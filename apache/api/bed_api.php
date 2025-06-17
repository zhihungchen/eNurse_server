<?php
/**
 * bed_api.php
 * 
 * 提供床位数据的增删改查接口，
 * 依赖 pure_init.php, utils.php 和 config.php (返回 PDO 实例及常量定义)
 */

// require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/init_cors.php';
require_once __DIR__ . '/utils.php';
// require_once './task_api.php';

// 下面就可以用 $pdo 去操作数据库了


// 初始化并读取 POST
$_POST = initPostData();

$dataType = $_POST[KEY_DATA_TYPE] ?? '';
if ($dataType !== TABLE_ENURSE_BEDS) {
    echo json_encode(['error' => 'Invalid data type']);
    exit;
}

$action = $_POST[KEY_ACTION] ?? '';
$params = $_POST[KEY_VALUES] ?? [];
$force = $_POST['force'] ?? false;
$results = [];
$errors = [];

define('DUPLICATE_UPDATE', true);

switch ($action) {
    case ACTION_INSERT:
    case ACTION_UPDATE:
    case ACTION_GET:
    case ACTION_DELETE:
        foreach ($params as $item) {
            $bedId = $item[KEY_BED_ID] ?? null;
            // bed_id 必须存在
            if (!$bedId) {
                $errors[] = 'Missing bed_id';
                continue;
            }

            switch ($action) {
                case ACTION_INSERT:
                    if (DUPLICATE_UPDATE) {
                        // 判断是否已存在
                        $exists = recordExistsPending($pdo, TABLE_ENURSE_BEDS, KEY_BED_ID, $bedId);
                        list($ok, $rowOrErr) = upsertRecord($pdo, TABLE_ENURSE_BEDS, KEY_BED_ID, $bedId, $item, $exists);
                    } else {
                        // 插入新记录
                        list($ok, $rowOrErr) = upsertRecord($pdo, TABLE_ENURSE_BEDS, KEY_BED_ID, $bedId, $item, false);
                    }

                    break;

                case ACTION_UPDATE:
                    error_log($item[KEY_STATUS]);
                    $id = -1;
                    if ($item[KEY_STATUS] === 'ongoing' || $item[KEY_STATUS] === 'ONGOING') {
                        $id = recordExistsStatus($pdo, TABLE_ENURSE_BEDS, KEY_BED_ID, $bedId, KEY_STATUS, 'pending');
                        if ($id === -1) {
                            $id = recordExistsStatus($pdo, TABLE_ENURSE_BEDS, KEY_BED_ID, $bedId, KEY_STATUS, 'ongoing');
                            if ($id === -1) {
                                error_log("没有找到符合条件的记录。");
                            } else {
                                error_log("找到符合条件的记录，ID 为：{$id}");
                            }
                        } else {
                            error_log("找到符合条件的记录，ID 为：{$id}");
                        }
                    }
                    else if ($item[KEY_STATUS] === 'complete' || $item[KEY_STATUS] === 'COMPLETE' || $item[KEY_STATUS] === 'failed' || $item[KEY_STATUS] === 'FAILED') {
                        $id = recordExistsStatus($pdo, TABLE_ENURSE_BEDS, KEY_BED_ID, $bedId, KEY_STATUS, 'ongoing');
                        if ($id === -1) {
                            error_log("没有找到符合条件的记录。");
                        } else {
                            error_log("找到符合条件的记录，ID 为：{$id}");
                        }
                        
                        $maxPerBed = getMaxSortOrderPerGroup($pdo, TABLE_ENURSE_BEDS, KEY_BED_ID);
                        if (empty($maxPerBed)) {
                            error_log("沒有任何 bed_id 底下的列滿足 status = 'complete'。");
                        } else {
                            foreach ($maxPerBed as $bedIdKey => $maxOrder) {
                                if ($bedIdKey === $item[KEY_BED_ID]) {
                                    error_log("床位 {$bedIdKey} 底下所有 status=complete 的列中，最大 sort_order 為：{$maxOrder}");
                                }
                            }
                        }
                        if (isset($maxPerBed[$item[KEY_BED_ID]])) {
                            $item[KEY_SORT_ORDER] = $maxPerBed[$item[KEY_BED_ID]] + 1.0;
                        }
                    }
                    list($ok, $rowOrErr) = upsertRecord($pdo, TABLE_ENURSE_BEDS, KEY_ID, $id, $item, true);
                    break;

                case ACTION_GET:
                    $rowOrErr = fetchBedById($pdo, TABLE_ENURSE_BEDS, KEY_BED_ID, $bedId);
                    $ok = true;
                    break;

                case ACTION_DELETE:
                    try {
                        $stmt = $pdo->prepare("DELETE FROM " . TABLE_ENURSE_BEDS . " WHERE " . KEY_BED_ID . " = :bed_id");
                        $stmt->execute([':bed_id' => $bedId]);
                        $ok = true;
                        $rowOrErr = [KEY_BED_ID => $bedId, KEY_STATUS => 'canceled'];
                    } catch (PDOException $e) {
                        $ok = false;
                        $rowOrErr = $e->getMessage();
                    }
                    break;
            }

            if ($ok) {
                $results[] = $rowOrErr;
            } else {
                $errors[] = $rowOrErr;
            }
        }
        break;

    case ACTION_GET_ALL:
        $floor  = $_POST[KEY_FLOOR]  ?? null;
        $status = $_POST[KEY_STATUS] ?? null;

        $where = [];
        $paramsBinding = [];
        if ($floor !== null) {
            $where[] = KEY_FLOOR . ' = :floor';
            $paramsBinding[':floor'] = $floor;
        }
        if ($status !== null) {
            $where[] = KEY_STATUS . ' = :status';
            $paramsBinding[':status'] = $status;
        } 
        if ($force !== true) {
            $where[] = KEY_STATUS . ' != :complete';
            $paramsBinding[':complete'] = 'complete';
        }

        $sql = 'SELECT * FROM ' . TABLE_ENURSE_BEDS
             . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
        $stmt = $pdo->prepare($sql);
        $stmt->execute($paramsBinding);
        $results = $stmt->fetchAll();
        break;

    default:
        $errors[] = 'Unknown action';
        break;
}

if (!empty($errors)) {
    // 如果 $errors 不是空陣列，就把所有錯誤訊息拼接後丟到 error_log
    error_log("Error: " . implode(", ", $errors));
} else {
    // 否則把 $results（通常是個陣列）印出來
    // error_log("Success: " . print_r($results, true));
    error_log("Success");
}


// 输出结果
$returnData = [
    KEY_DATA_TYPE => TABLE_ENURSE_BEDS,
    KEY_ACTION    => $action,
    KEY_VALUES    => $errors ? $errors : $results,
];

echo json_encode($returnData, JSON_UNESCAPED_UNICODE);

// ---------- 辅助函数 ----------

/**
 * 检查记录是否存在
 */
function recordExists(PDO $pdo, string $table, string $keyField, string $keyValue): bool {
    $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE {$keyField} = :val LIMIT 1");
    $stmt->execute([':val' => $keyValue]);
    return (bool) $stmt->fetchColumn();
}

/**
 * 检查指定表中，给定 keyField = keyValue 且 status = 'pending' 的记录是否存在
 *
 * @param PDO    $pdo       已初始化的 PDO 实例
 * @param string $table     要查询的表名（请确保来自白名单或受信任）
 * @param string $keyField  主键字段名
 * @param string $keyValue  主键字段的值
 * @param string $statusField  状态字段名，默认 'status'
 * @return bool  存在且状态为 'pending' 返回 true，否则 false
 */
function recordExistsPending(
    PDO $pdo,
    string $table,
    string $keyField,
    string $keyValue,
    string $statusField = 'status'
): bool {
    // 注意：$table 和 $keyField 必须来自受信任的来源，避免 SQL 注入
    $sql = "SELECT 1
              FROM {$table}
             WHERE {$keyField} = :val
               AND {$statusField} = :pending
             LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':val'     => $keyValue,
        ':pending' => 'pending',
    ]);
    return (bool) $stmt->fetchColumn();
}

function recordExistsStatus(
    PDO $pdo,
    string $table,
    string $keyField,
    string $keyValue,
    string $statusField = 'status',
    string $statusValue = 'pending'
): int {
    // 注意：$table 和 $keyField 必须来自受信任的来源，避免 SQL 注入
    $sql = "SELECT *
              FROM {$table}
             WHERE {$keyField} = :val
               AND {$statusField} = :pending
             LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':val'     => $keyValue,
        ':pending' => $statusValue,
    ]);
    // return (bool) $stmt->fetchColumn();

    // $result = $stmt->fetchColumn(); // fetchColumn() 会拿到第一列（也就是 id）
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        // 没有匹配的记录
        return -1;
    } else {
        // $row 就是一個關聯陣列，包含了該列所有欄位和值
        // 例如：$row['id'], $row['status'], $row['bed_no'] 等等
        // var_dump($row); //會打印出來返回到app
        error_log($row[KEY_ID]);
        error_log($row[KEY_SORT_ORDER]);
        return $row[KEY_ID];
    }
}

/**
 * 針對每一組（以 $groupField 為分組欄位），找出所有 status = 'complete' 的列中，
 * 該群組的最大 sort_order。
 *
 * 回傳格式範例：
 * [
 *   'bed001' => 5,
 *   'bed002' => 3,
 *   'bed005' => 12,
 *    …
 * ]
 *
 * @param PDO    $pdo          PDO 連線物件
 * @param string $table        資料表名稱，請確保為可信來源
 * @param string $groupField   要分組的欄位名稱（例如 'bed_id'）
 * @param string $statusField  狀態欄位名稱，預設為 "status"
 * @param string $statusValue  要比對的狀態值，預設為 "complete"
 * @param string $sortField    要取最大值的欄位，預設為 "sort_order"
 * @return array               回傳一個關聯陣列，格式為 [ groupValue => maxSortOrder, … ]；
 *                             若該群組沒有任何 status=complete，則不會出現在結果裡
 */
function getMaxSortOrderPerGroup(
    PDO    $pdo,
    string $table,
    string $groupField,
    string $statusField  = 'status',
    string $statusValue  = 'complete',
    string $sortField    = 'sort_order'
): array {
    // 確保這些欄位名稱都是可信的字串，否則可能有 SQL 注入風險
    $sql = "SELECT {$groupField}, MAX({$sortField}) AS max_order
              FROM {$table}
             WHERE {$statusField} = :status
             GROUP BY {$groupField}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':status' => $statusValue,
    ]);

    // 用 FETCH_KEY_PAIR 把結果直接取成 [ groupValue => max_order ] 的形式
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    // 注意：PDO::FETCH_KEY_PAIR 會把第一欄當成 key，第二欄當成 value
    // 如果 fetchAll 不支援 KEY_PAIR，你也可以改為 FETCH_ASSOC，再迴圈提取：
    // $result = [];
    // foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    //     $result[$row[$groupField]] = (int)$row['max_order'];
    // }
    // return $result;

    // 將所有值強制轉成整數
    foreach ($rows as $key => $val) {
        $rows[$key] = (int)$val;
    }
    return $rows;
}


/**
 * 插入或更新记录
 * @return array [bool 成功, array|string 记录或错误信息]
 */
function upsertRecord(PDO $pdo, string $table, string $keyField, string $keyValue, array $data, bool $isUpdateOnly): array {
    try {
        if (!$isUpdateOnly) {
            // 插入
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ":{$c}", $cols);
            foreach ($data as $k => $v) {
                if ($k === 'id') continue;
                if ($k === 'tasks') {
                    // 1. 逐筆把 tasks 陣列寫進 table_enurse_tasks
                    // foreach ($v as $task) {
                    //     if (! isset($task['task_name'])) {
                    //         continue;
                    //     }
                    //     // JSON 化整筆 task 物件存到 details
                    //     $bedId      = $data['bed_id'] ?? '';
                    //     $task_name  = $task['task_name']  ?? '';
                    //     $status     = $task['status']     ?? 'pending';
                    //     $createdAt  = $task['created_at'] ?? date('c');

                    //     // 執行插入
                    //     $ok = $taskStmt->execute([
                    //         $bedId,
                    //         $task_name,
                    //         $status,
                    //         $createdAt
                    //     ]);

                    //     if (! $ok) {
                    //         $err = $taskStmt->errorInfo();
                    //         error_log("Insert task failed for bed {$bedId}, task {$task['task_name']}: " . $err[2]);
                    //     }
                    // }

                    // 2. 最後再把整個 $v 陣列編碼回 JSON，存回 $data
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                }
                $data[$k] = $v;
            }
            $sql = "INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_combine($placeholders, $data));
        } else {
            // 更新
            $sets = [];
            $bindings = [];
            foreach ($data as $k => $v) {
                if ($k === 'id') continue;
                if ($k === 'tasks') {
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                }
                $sets[] = "{$k} = :{$k}";
                $bindings[":{$k}"] = $v;
            }
            $bindings[':keyVal'] = $keyValue;
            $sql = "UPDATE {$table} SET " . implode(',', $sets) . " WHERE {$keyField} = :keyVal";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($bindings);
        }

        // 返回最新数据行
        return [true, fetchBedById($pdo, $table, $keyField, $keyValue)];
    } catch (PDOException $e) {
        return [false, $e->getMessage()];
    }
}

/**
 * 根据 keyField/keyValue 获取单条记录
 */
function fetchBedById(PDO $pdo, string $table, string $keyField, string $keyValue): array {
    $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE {$keyField} = :val");
    $stmt->execute([':val' => $keyValue]);
    return $stmt->fetch() ?: [];
}

