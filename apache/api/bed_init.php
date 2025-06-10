<?php
// bed_init.php
header('Content-Type: application/json; charset=utf-8');

// 1. 載入 init.php，裡面應該定義了：
//    - $conn           : mysqli DB 連線物件
//    - TABLE_ENURSE_ALL_BEDS : 全部床位表名
//    - KEY_BED_ID, KEY_FLOOR, KEY_ROOM_NUMBER, KEY_BED_NAME : 欄位常數
require_once __DIR__ . '/db_config.php';

// 2. 床位 ID 列表
$bedList = [
    "5a01之0","5a02之0","5a03之0","5a05之0",
    "5a06之1","5a06之2","5a06之3","5a06之5",
    "5a07之1","5a07之2","5a07之3","5a07之5",
    "5a08之1","5a08之2","5a08之3","5a08之5",
    "5a09之1","5a09之2","5a09之3","5a09之5",
    "5a10之1","5a10之2","5a10之3","5a10之5",
    "5a11之1","5a11之2",
    "5a12之1","5a12之2",
    "5a13之0","5a15之0","5a16之0","5a17之0",
    "5a護理站",

    "5b01之0","5b02之0","5b03之0","5b05之0",
    "5b06之1","5b06之2","5b06之3","5b06之5",
    "5b07之1","5b07之2","5b07之3","5b07之5",
    "5b08之1","5b08之2","5b08之3","5b08之5",
    "5b09之1","5b09之2","5b09之3","5b09之5",
    "5b10之0","5b11之0",
    "5b12之1","5b12之2",
    "5b13之1","5b13之2",
    "5b15之0","5b16之0","5b17之0","5b18之0",
    "5b護理站",

    "7b01之0","7b02之0","7b03之0","7b05之0",
    "7b06之1","7b06之2","7b06之3","7b06之5",
    "7b07之1","7b07之2","7b07之3","7b07之5",
    "7b08之1","7b08之2","7b08之3","7b08之5",
    "7b09之1","7b09之2","7b09之3","7b09之5",
    "7b10之1","7b10之2","7b10之3","7b10之5",
    "7b11之1","7b11之2","7b12之1","7b12之2",
    "7b13之0","7b15之0","7b16之0","7b17之0",
    "7b護理站"
];

// 3. 準備 INSERT 語句（如果 bed_id 已存在則 IGNORE）
// 用 PDO 把床位一次次写入
$sql = "
  INSERT IGNORE INTO " . TABLE_ENURSE_ALL_BEDS . " (
    " . KEY_BED_ID . ",
    " . KEY_FLOOR . ",
    " . KEY_ROOM_NUMBER . ",
    " . KEY_BED_NAME . "
  ) VALUES (?, ?, ?, ?)
";
$stmt = $pdo->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error'=>'Prepare failed: '.$conn->error]);
    exit;
}

// 4. 迭代每個 bed ID，解析欄位並執行插入
foreach ($bedList as $bedId) {
    // 匹配 floor (2 chars) + room_number (2 digits) + 之 + ordinal
    if (preg_match('/^([0-9a-zA-Z]{2})(\d{2})之(\d+)$/u', $bedId, $m)) {
        $floor      = $m[1];                  // e.g. "5a"
        $roomNumber = $m[2];                  // e.g. "01"
        $ordinal    = intval($m[3]);          // e.g. 0,1,2,...
        $bedName    = $roomNumber . '-' . $ordinal; // e.g. "01-0"
        $bedId      = $floor . $roomNumber . '-' . $ordinal; // e.g. "5a01-0"

    } else {
        // 無法解析（例如 "5a護理站"），直接把 bedName 設成 bedId 自身
        $floor      = substr($bedId, 0, 2);
        $roomNumber = substr($bedId, 2);
        $bedName    = $bedId;
    }

    // 傳入參數並執行
    try {
        $stmt->execute([$bedId, $floor, $roomNumber, $bedName]);
    } catch (PDOException $e) {
        error_log("Insert failed for {$bedId}: " . $e->getMessage());
    }
}

// 5. 完成回應
echo json_encode(['success'=>true]);
exit;

