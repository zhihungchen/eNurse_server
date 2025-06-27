<?php
/**
 * config.noenv.php
 * 
 * 最简版配置：直接使用环境变量（若有），否则回退到硬编码默认值。
 * 仅供测试使用，不推荐在生产环境使用。
 */

// 数据库连接参数：环境变量或默认值
// $dbHost    = getenv('DB_HOST')    ?: 'mysql';
$dbHost    = getenv('DB_HOST')    ?: 'localhost';
$dbPort    = getenv('DB_PORT')    ?: '3306';
$dbName    = getenv('DB_NAME')    ?: 'enurse';
$dbUser    = getenv('DB_USER')    ?: 'robot';
$dbPass    = getenv('DB_PASS')    ?: 'buzhidaowsmbeigongji';
$dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';

// 构造 PDO DSN
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $dbHost, $dbPort, $dbName, $dbCharset
);

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log("DB Connection Error: {$e->getMessage()}");
    // 测试时直接输出错误并退出
    exit('DB Connection Error: ' . $e->getMessage());
}

// 一些示例常量（根据需要自行添加）
// 常量定义：基础 URL
define('RDS_BASE_URL', getenv('BASE_URL') ?: 'http://localhost:80/api/');

// ---------- 操作类型 ----------
define('KEY_DATA_TYPE',    'data_type');
define('KEY_ACTION',       'action');
define('KEY_VALUES',       'values');

define('ACTION_INSERT',    'insert');
define('ACTION_UPDATE',    'update');
define('ACTION_GET',       'get');
define('ACTION_GET_ALL',   'get_all');
define('ACTION_DELETE',    'delete');

// ---------- 表名定义 ----------
define('TABLE_ENURSE_USERS',     'table_enurse_users');
define('TABLE_ENURSE_ALL_BEDS',  'table_enurse_all_beds');
define('TABLE_ENURSE_BEDS',      'table_enurse_beds');
define('TABLE_ENURSE_TASKS',     'table_enurse_tasks');
define('TABLE_ENURSE_RECORDS',   'table_enurse_records');

// ---------- 公共字段 ----------
define('KEY_ID',             'id');
define('KEY_FLOOR',          'floor');
define('KEY_ROOM_NUMBER',    'room_number');
define('KEY_BED_NAME',       'bed_name');
define('KEY_BED_ID',         'bed_id');
define('KEY_TASKS',          'tasks');
define('KEY_TASK_TYPE',      'task_type');
define('KEY_TASK_NAME',      'task_name');
define('KEY_STATUS',         'status');
define('KEY_SORT_ORDER',     'sort_order');
define('KEY_LEVEL',          'level');
define('KEY_CREATED_AT',     'created_at');
define('KEY_START_TIME',     'start_time');
define('KEY_FINISH_TIME',    'finish_time');
define('KEY_DETAILS',        'details');
define('KEY_FINAL_STATUS',   'final_status');
define('KEY_IS_SYNCED',      'is_synced');

// ---------- SQL 创建语句 ----------
define('CREATE_TABLE_ENURSE_USERS',
    "CREATE TABLE IF NOT EXISTS " . TABLE_ENURSE_USERS . " (
        " . KEY_ID . " SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(20) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'nurse',
        name VARCHAR(30) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

define('CREATE_TABLE_ENURSE_ALL_BEDS',
    "CREATE TABLE IF NOT EXISTS " . TABLE_ENURSE_ALL_BEDS . " (
        " . KEY_ID . " SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        " . KEY_FLOOR . " VARCHAR(20) NOT NULL,
        " . KEY_ROOM_NUMBER . " VARCHAR(20) NOT NULL,
        " . KEY_BED_NAME . " VARCHAR(20) NOT NULL,
        " . KEY_BED_ID . " VARCHAR(20) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

define('CREATE_TABLE_ENURSE_BEDS',
    "CREATE TABLE IF NOT EXISTS " . TABLE_ENURSE_BEDS . " (
        " . KEY_ID . " SMALLINT UNSIGNED AUTO_INCREMENT,
        " . KEY_FLOOR . " VARCHAR(20) NOT NULL,
        " . KEY_ROOM_NUMBER . " VARCHAR(20) NOT NULL,
        " . KEY_BED_NAME . " VARCHAR(20) NOT NULL,
        " . KEY_BED_ID . " VARCHAR(20) NOT NULL,
        " . KEY_TASKS . " MEDIUMTEXT NOT NULL,
        " . KEY_STATUS . " ENUM('pending','ongoing','complete','failed') NOT NULL DEFAULT 'pending',
        " . KEY_DETAILS . " MEDIUMTEXT NULL,
        " . KEY_SORT_ORDER . " DECIMAL(10,2) NOT NULL DEFAULT 0,
        " . KEY_LEVEL . " DECIMAL(10,2) NOT NULL DEFAULT 0,
        PRIMARY KEY (" . KEY_ID . "),
        UNIQUE KEY uq_bed_status_order (" . KEY_BED_ID . ", " . KEY_STATUS . ", " . KEY_SORT_ORDER . "),
        FOREIGN KEY (" . KEY_BED_ID . ") REFERENCES " . TABLE_ENURSE_ALL_BEDS . "(" . KEY_BED_ID . ") ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

define('CREATE_TABLE_ENURSE_TASKS',
    "CREATE TABLE IF NOT EXISTS " . TABLE_ENURSE_TASKS . " (
        " . KEY_ID . " SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        " . KEY_BED_ID . " VARCHAR(20) NOT NULL,
        " . KEY_TASK_TYPE . " VARCHAR(20) NOT NULL DEFAULT '',
        " . KEY_TASK_NAME . " VARCHAR(20) NOT NULL,
        " . KEY_DETAILS . " MEDIUMTEXT NULL,
        " . KEY_STATUS . " ENUM('pending','ongoing','complete','failed') NOT NULL DEFAULT 'pending',
        " . KEY_SORT_ORDER . " DECIMAL(10,2) NOT NULL DEFAULT 0,
        " . KEY_LEVEL . " DECIMAL(10,2) NOT NULL DEFAULT 0,
        " . KEY_CREATED_AT . " TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        " . KEY_START_TIME . " TIMESTAMP NULL,
        " . KEY_FINISH_TIME . " TIMESTAMP NULL,
        UNIQUE KEY uq_bed_task_time (" . KEY_BED_ID . ", " . KEY_TASK_NAME . ", " . KEY_CREATED_AT . "),
        FOREIGN KEY (" . KEY_BED_ID . ") REFERENCES " . TABLE_ENURSE_ALL_BEDS . "(" . KEY_BED_ID . ") ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

define('CREATE_TABLE_ENURSE_RECORDS',
    "CREATE TABLE IF NOT EXISTS " . TABLE_ENURSE_RECORDS . " (
        " . KEY_ID . " SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        " . KEY_BED_ID . " VARCHAR(20) NOT NULL,
        " . KEY_TASK_TYPE . " VARCHAR(20) NOT NULL DEFAULT '',
        " . KEY_TASK_NAME . " VARCHAR(20) NOT NULL,
        " . KEY_DETAILS . " MEDIUMTEXT NULL,
        " . KEY_FINAL_STATUS . " ENUM('complete','failed','canceled') NOT NULL DEFAULT 'complete',
        " . KEY_IS_SYNCED . " TINYINT(1) NOT NULL DEFAULT 1,
        " . KEY_CREATED_AT . " TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        " . KEY_START_TIME . " TIMESTAMP NULL,
        " . KEY_FINISH_TIME . " TIMESTAMP NULL,
        UNIQUE KEY uq_bed_task_time (" . KEY_BED_ID . ", " . KEY_TASK_NAME . ", " . KEY_CREATED_AT . "),
        FOREIGN KEY (" . KEY_BED_ID . ") REFERENCES " . TABLE_ENURSE_ALL_BEDS . "(" . KEY_BED_ID . ") ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);


return $pdo;


