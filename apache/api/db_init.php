<?php
/**
 * db_init.php
 *
 * 仅在环境变量 DB_INIT=true 时才会执行，
 * 使用 PDO + 异常处理 安全地创建表（IF NOT EXISTS）。
 */

// 1. 加载配置（返回 $pdo 实例 和 创建语句常量）
$pdo = require __DIR__ . '/db_config.php';

// // 2. 检查环境变量开关，默认不执行
// if (getenv('DB_INIT') !== 'true') {
//     http_response_code(403);
//     exit('Database initialization is disabled.');
// }

// 3. 要执行的建表语句，常量在 config.php 中定义
$tables = [
    'table_enurse_users'    => CREATE_TABLE_ENURSE_USERS,
    'table_enurse_all_beds' => CREATE_TABLE_ENURSE_ALL_BEDS,
    'table_enurse_beds'     => CREATE_TABLE_ENURSE_BEDS,
    'table_enurse_tasks'    => CREATE_TABLE_ENURSE_TASKS,
    'table_enurse_records'  => CREATE_TABLE_ENURSE_RECORDS,
];

// 4. 逐表执行
$results = [];
foreach ($tables as $name => $sql) {
    // 检查表是否已存在
    // 用 quote() 安全地给模式加上引号、转义
    $pattern = $pdo->quote($name); 
    $stmt = $pdo->query("SHOW TABLES LIKE $pattern");
    if ($stmt && $stmt->rowCount() > 0) {
         // 已存在
        $results[] = htmlspecialchars("{$name}: already exists", ENT_QUOTES, 'UTF-8');
        continue;
    }
    // 不存在，执行建表
    try {
        // exec() 用于执行无返回结果的 SQL（如 DDL）
        $pdo->exec($sql);
        $results[] = htmlspecialchars("{$name}: creation successful", ENT_QUOTES, 'UTF-8');
    } catch (PDOException $e) {
        // 错误写入日志，不直接输出详尽错误给客户端
        error_log("DB Init Error [{$name}]: " . $e->getMessage());
        $results[] = htmlspecialchars("{$name}: creation failed (see server logs)", ENT_QUOTES, 'UTF-8');
    }
}

// 5. 输出结果（简洁友好）
header('Content-Type: text/html; charset=utf-8');
echo '<h3>Database Initialization Results:</h3><ul>';
foreach ($results as $msg) {
    echo "<li>{$msg}</li>";
}
echo '</ul>';

