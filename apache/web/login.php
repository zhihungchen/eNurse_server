<?php
session_start();
header("Content-Type: text/html; charset=UTF-8");

// Load DB config
$configFile = __DIR__ . "/config.json";
if (!file_exists($configFile)) {
    die("Missing config.json");
}

$config = json_decode(file_get_contents($configFile), true);
if (!$config || !isset($config["database"])) {
    die("Invalid config.json format");
}

// Connect to MySQL using config user credentials
// $db = $config["database"];
$conn = @new mysqli($config["host"], $config["user"], $config["password"], $config["database"]);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle POST data
$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";
$role     = $_POST["role"] ?? "";

if (!$username || !$password || !$role) {
    header("Location: login.html?error=1");
    exit;
}

// Query user with matching role
$stmt = $conn->prepare("SELECT id, username, password_hash, role FROM table_enurse_users WHERE username = ? AND role = ?");
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$stmt->store_result();

// Check if user exists and verify password
if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $uname, $hashed_password, $user_role);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        // Set session variables
        $_SESSION["user_id"] = $id;
        $_SESSION["username"] = $uname;
        $_SESSION["role"] = $user_role;

        // Redirect to role-specific dashboard
        header("Location: dashboard.php");
        exit;
    }
}

header("Location: login.html?error=" . urlencode("Invalid username, password, or role. Please try again."));
exit;
?>
