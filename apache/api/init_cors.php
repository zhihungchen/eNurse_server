<?php
/**
 * init.php
 * 
 * 负责处理 CORS、会话启动、加载配置及数据库连接，
 * 并设置统一的 JSON 响应头。
 * 建议放置于项目入口脚本之前统一引入。
 */

// ------ 记录请求方法与来源 ------
error_log('PHP CORS check: method=' . $_SERVER['REQUEST_METHOD']);
error_log('PHP CORS check: origin=' . ($_SERVER['HTTP_ORIGIN'] ?? 'N/A'));

// ------ CORS 配置 ------
$allowed_origins = [
    'https://ntuairobo.net',
    'http://enurse-app.local:8080',
    'https://localhost',
    'https://172.16.63.134', // enurse-web
    'https://172.18.96.84', #temi
    'https://e-nurse.ntuh.gov.tw'
    // '*' // 通配符，可根据需求移除
];

if (! empty($_SERVER['HTTP_ORIGIN'])) {
    $origin   = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowAny = in_array('*', $allowed_origins, true);
    $allowOrigin = $allowAny
        ? '*'  // 若包含通配符则允许所有
        : (in_array($origin, $allowed_origins, true) ? $origin : '');

    if ($allowOrigin !== '') {
        header("Access-Control-Allow-Origin: $allowOrigin");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');
    } else {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        error_log('Access denied: origin not allowed: ' . $origin);
        exit(json_encode(['error' => 'Access denied: origin not allowed']));
    }
} else {
    // // 没有 Origin 头，可能是同源请求或本地请求
    // header('Access-Control-Allow-Origin: http://localhost:8080');
    // 非浏览器请求（Android 客户端、Postman、curl 等），直接允许
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

// 预检请求直接返回 204
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// ------ 启动 Session ------
session_start();

// ------ 加载配置 (返回 PDO 实例和常量) ------
$pdo = require __DIR__ . '/db_config.php';

// ------ 设置 JSON 响应头 ------
header('Content-Type: application/json; charset=utf-8');

// ------ 可在此处继续添加通用初始化逻辑 ------
?>


