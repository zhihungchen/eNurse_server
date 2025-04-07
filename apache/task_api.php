<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

// Load Database Credentials from config.json
$configFile = "/var/www/html/config.json";
if (!file_exists($configFile)) {
    die(json_encode(["error" => "Configuration file missing"]));
}

$config = json_decode(file_get_contents($configFile), true);
if (!$config || !isset($config["database"])) {
    die(json_encode(["error" => "Invalid configuration file"]));
}

// MySQL Database Connection
$dbConfig = $config["database"];
$servername = $dbConfig["host"];
$username = $dbConfig["username"];
$password = $dbConfig["password"];
$dbname = $dbConfig["dbname"];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Handle GET and POST requests
$method = $_SERVER["REQUEST_METHOD"];
$data = json_decode(file_get_contents("php://input"), true) ?? [];
$action = $_GET["action"] ?? $data["action"] ?? null;

$response = [];

// Create a new bed (POST request)
if ($action === "create_bed") {
    if (!isset($data["bed_name"], $data["room_number"], $data["floor"])) {
        echo json_encode(["error" => "Missing parameters for bed creation"]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO beds (bed_name, room_number, floor) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data["bed_name"], $data["room_number"], $data["floor"]);

    echo json_encode($stmt->execute() ? 
        ["message" => "Bed created successfully", "bed_id" => $stmt->insert_id] : 
        ["error" => "Failed to create bed"]);
    exit();
}

// Fetch all beds (GET request)
if ($action === "get_beds") {
    $result = $conn->query("SELECT id, bed_name, room_number, floor FROM beds");
    echo json_encode(["beds" => $result->fetch_all(MYSQLI_ASSOC)]);
    exit();
}

// Fetch all tasks
if ($action === "get_tasks") {
    $result = $conn->query("SELECT id, bed_id, task_name, task_type, timestamp, details, status FROM tasks ORDER BY timestamp DESC");
    echo json_encode(["tasks" => $result->fetch_all(MYSQLI_ASSOC)]);
    exit();
}

// Create a new task (POST request)
if ($action === "create_task") {
    if (!isset($data["bed_id"], $data["task_name"], $data["task_type"], $data["task_details"])) {
        echo json_encode(["error" => "Missing parameters for task creation"]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO tasks (bed_id, task_name, task_type, timestamp, details, status) VALUES (?, ?, ?, NOW(), ?, 'pending')");
    $stmt->bind_param("isss", $data["bed_id"], $data["task_name"], $data["task_type"], $data["task_details"]);

    echo json_encode($stmt->execute() ? 
        ["message" => "Task created successfully", "task_id" => $stmt->insert_id] : 
        ["error" => "Failed to create task"]);
    exit();
}

// Update task status (POST request)
if ($action === "update_task") {
    if (!isset($data["task_id"], $data["new_status"])) {
        echo json_encode(["error" => "Missing parameters for task update"]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $data["new_status"], $data["task_id"]);

    echo json_encode($stmt->execute() ? 
        ["message" => "Task updated successfully"] : 
        ["error" => "Failed to update task"]);
    exit();
}

// Delete a task (POST request)
if ($action === "delete_task") {
    if (!isset($data["task_id"])) {
        echo json_encode(["error" => "Missing task_id for deletion"]);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $data["task_id"]);

    echo json_encode($stmt->execute() ? 
        ["message" => "Task deleted successfully"] : 
        ["error" => "Failed to delete task"]);
    exit();
}

// Invalid action
echo json_encode(["error" => "Invalid action"]);
$conn->close();
?>
