<?php

// require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/init_cors.php';
require_once __DIR__ . '/utils.php';

// 下面就可以用 $pdo 去操作数据库了


// 初始化并读取 POST
$_POST = initPostData();

/**
 * 入口：支援 POST
 * 前端可傳 JSON body：
 * {
 *   "bed_id":  123,
 *   "tasks": [
 *     {"name":"換藥", "detail":"即將到期", ...},
 *     {"name":"量血壓", "detail":"每小時一次", ...}
 *   ]
 * }
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $bedId = intval($input['bed_id'] ?? 0);
    $tasks = $input['tasks'] ?? [];

    if ($bedId <= 0 || ! is_array($tasks)) {
        http_response_code(400);
        echo json_encode(['error'=>'Invalid bed_id or tasks']);
        exit;
    }

    // 1. 存床位下的 tasks JSON
    saveBedTasks($bedId, $tasks);

    // 2. 插入每一筆任務到 tasks 表
    insertTasks($bedId, $tasks);

    echo json_encode(['success'=>true]);
    exit;
}

// 也可以再擴充對 GET/PUT/DELETE 等 HTTP method 的支援，分別對應查詢／更新／刪除任務等操作。

/**
 * 1. 儲存整筆 JSON tasks 到 ENURSE_BEDS.KEY_TASKS
 */
function saveBedTasks(int $bedId, array $tasks): bool {
    global $conn;
    $tasksJson = json_encode($tasks, JSON_UNESCAPED_UNICODE);
    $sql = "
      UPDATE " . TABLE_ENURSE_BEDS . "
      SET " . KEY_TASKS . " = ?
      WHERE " . KEY_BED_ID . " = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $tasksJson, $bedId);
    return $stmt->execute();
}

/**
 * 2. 逐筆插入任務到 ENURSE_TASKS
 */
function insertTasks(int $bedId, array $tasks): bool {
    global $conn;
    $sql = "
      INSERT INTO " . TABLE_ENURSE_TASKS . " (
        " . KEY_BED_ID . ",
        " . KEY_TASKS . ",      -- 這裡存 task 名稱或型別
        " . KEY_DETAILS . "     -- 這裡存整筆 task 資料（JSON）
      ) VALUES (?, ?, ?)
    ";
    $stmt = $conn->prepare($sql);

    foreach ($tasks as $task) {
        // 假設 JSON array 每筆有 name 欄位
        $taskName = $task['name'] ?? '';
        $detailJson = json_encode($task, JSON_UNESCAPED_UNICODE);
        $stmt->bind_param('iss', $bedId, $taskName, $detailJson);
        if (! $stmt->execute()) {
            return false;
        }
    }
    return true;
}

/**
 * 3. 更新既有任務（示範可更新狀態、排序、level、或其他 detail）
 */
function updateTask(int $taskId, array $data): bool {
    global $conn;
    $sets = [];
    $types = '';
    $vals  = [];

    if (isset($data['status'])) {
        $sets[]  = KEY_STATUS . ' = ?';
        $types  .= 's';
        $vals[]  = $data['status'];
    }
    if (isset($data['sort_order'])) {
        $sets[]  = KEY_SORT_ORDER . ' = ?';
        $types  .= 'd';
        $vals[]  = $data['sort_order'];
    }
    if (isset($data['level'])) {
        $sets[]  = KEY_LEVEL . ' = ?';
        $types  .= 'd';
        $vals[]  = $data['level'];
    }
    if (isset($data['details'])) {
        $sets[]   = KEY_DETAILS . ' = ?';
        $types   .= 's';
        $vals[]   = json_encode($data['details'], JSON_UNESCAPED_UNICODE);
    }

    if (empty($sets)) {
        return false;
    }
    // 最後綁定 WHERE task_id
    $types .= 'i';
    $vals[]  = $taskId;

    $sql = "
      UPDATE " . TABLE_ENURSE_TASKS . "
      SET " . implode(', ', $sets) . "
      WHERE " . KEY_ID . " = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$vals);
    return $stmt->execute();
}