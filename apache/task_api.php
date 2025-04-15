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

// Delete all beds (POST request)
if ($action === "delete_all_beds") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["username"], $data["password"]) ||
        $data["username"] !== $username || $data["password"] !== $password) {
        echo json_encode(["error" => "Unauthorized"]);
        exit();
    }

    $sql = "DELETE FROM beds";
    if ($conn->query($sql)) {
        echo json_encode(["message" => "All beds deleted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to delete beds"]);
    }
    exit();
}


// Fetch all beds (GET request)
if ($action === "get_beds") {
    $result = $conn->query("SELECT id, bed_name, room_number, floor FROM beds");
    echo json_encode(["beds" => $result->fetch_all(MYSQLI_ASSOC)]);
    exit();
}

// Update tasks for a specific bed (POST request)

function check_user_permission($username, $password) {
    // TODO: replace with actual user table check if needed
    return $username === "root" && $password === "root";
}

if ($action === "update_tasks") {
    $input = json_decode(file_get_contents("php://input"), true);

    $username     = $input["username"] ?? '';
    $password     = $input["password"] ?? '';
    $bed_name     = $input["bed_name"] ?? null;
    $room_number  = $input["room_number"] ?? null;
    $floor        = $input["floor"] ?? null;
    $tasks        = $input["tasks"] ?? null;

    if (!check_user_permission($username, $password)) {
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }

    if (!$bed_name || !$room_number || !$floor || !$tasks) {
        echo json_encode(["error" => "Missing parameters"]);
        exit;
    }

    $tasks_json = json_encode($tasks);

    $stmt = $conn->prepare("
        UPDATE beds
        SET tasks = ?
        WHERE bed_name = ? AND room_number = ? AND floor = ?
    ");
    $stmt->bind_param("ssss", $tasks_json, $bed_name, $room_number, $floor);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Update failed", "details" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// delete all tasks 
if ($action === "delete_all_tasks") {
    $input = json_decode(file_get_contents("php://input"), true);

    $username = $input["username"] ?? '';
    $password = $input["password"] ?? '';

    if (!check_user_permission($username, $password, $conn)) {
        echo json_encode(["error" => "Unauthorized"]);
        exit();
    }

    $sql = "UPDATE beds SET tasks = NULL";

    if ($conn->query($sql)) {
        echo json_encode(["message" => "All bed tasks cleared"]);
    } else {
        echo json_encode(["error" => "Failed to clear tasks", "details" => $conn->error]);
    }

    $conn->close();
    exit();
}




// Invalid action
echo json_encode(["error" => "Invalid action"]);
$conn->close();
?>
