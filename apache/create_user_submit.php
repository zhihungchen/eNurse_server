<?php
/**
 * create_user_submit.php
 * 
 * Handles the submission of the new user form from create_user.php.
 * 
 * Responsibilities:
 * - Receives username, password, name, and role via POST.
 * - Hashes the password using password_hash().
 * - Inserts the new user into the 'users' table using the admin's MySQL credentials stored in session.
 * 
 * Requirements:
 * - This script should only be accessed after admin authentication (via create_user.php).
 * 
 */
session_start();

// Check admin session
if (!isset($_SESSION["admin_verified"]) || $_SESSION["admin_verified"] !== true) {
    die("❌ Unauthorized access.");
}

// Load config
$configFile = __DIR__ . "/config.json";
$config = json_decode(file_get_contents($configFile), true);
$db = $config["database"];

// Get admin login from session
$admin_user = $_SESSION["admin_user"];
$admin_pass = $_SESSION["admin_pass"];

// Connect to DB
$conn = new mysqli($db["host"], $admin_user, $admin_pass, $db["dbname"]);
if ($conn->connect_error) {
    die("❌ DB connection failed.");
}

// Get new user data
$username = $_POST["username"] ?? null;
$password = $_POST["password"] ?? null;
$role     = $_POST["role"] ?? null;
$name     = $_POST["name"] ?? null;

if (!$username || !$password || !$role || !$name) {
    die("❌ Missing fields.");
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, password_hash, role, name) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $password_hash, $role, $name);

if ($stmt->execute()) {
    echo "✅ User '$username' created successfully.";
} else {
    echo "❌ Failed to create user: " . $stmt->error;
}
?>
