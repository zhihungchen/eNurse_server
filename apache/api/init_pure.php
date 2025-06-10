<?php
/**
 * init.php
 * 
 * 项目初始化脚本：加载配置、启动会话、设置统一 JSON 响应头。
 * 已移除 CORS 设置。若需跨域请在服务器层面配置。
 */

// 启动会话
session_start();

// 加载配置（返回 PDO 实例和常量定义）
$pdo = require __DIR__ . '/config.php';

// 设置 JSON 默认响应头
header('Content-Type: application/json; charset=utf-8');

// 可在此添加更多全局初始化逻辑


