<?php
/**
 * create_user_submit.php
 * 
 * Handles the submission of the new user form from create_user.php.
 * 
 * Responsibilities:
 * - Receives username, password, name, and role via POST.
 * - Hashes the password using password_hash().
 * - Inserts the new user into the 'table_enurse_users' table using the admin's MySQL credentials stored in session.
 * 
 * Requirements:
 * - This script should only be accessed after admin authentication (via create_user.php).
 * 
 */
session_start();

// Check if admin is verified
if (!isset($_SESSION["admin_verified"]) || $_SESSION["admin_verified"] !== true) {
    die("❌ Unauthorized access. Please log in as an admin.");
}

// Load database configuration
$configFile = __DIR__ . "/config.json";
$config = json_decode(file_get_contents($configFile), true);
// $db = $config["database"];

// Connect to the database using session credentials
$admin_user = $_SESSION["admin_user"];
$admin_pass = $_SESSION["admin_pass"];
$conn = @new mysqli($config["host"], $admin_user, $admin_pass, $config["database"]);
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// Get form data
$username = $_POST["username"] ?? null;
$password = $_POST["password"] ?? null;
$role     = $_POST["role"] ?? null;
$name     = $_POST["name"] ?? null;

// Validate form data
if (!$username || !$password || !$role || !$name) {
    die("❌ Missing required fields. Please fill out all fields.");
}

// Validate role
$allowed_roles = ["admin", "user", "viewer"];
if (!in_array($role, $allowed_roles)) {
    die("❌ Invalid role. Allowed roles are: " . implode(", ", $allowed_roles));
}

// Check if username already exists
$stmt = $conn->prepare("SELECT COUNT(*) FROM table_enurse_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    die("❌ Username '$username' already exists. Please choose a different username.");
}

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert the new user into the database
$stmt = $conn->prepare("INSERT INTO table_enurse_users (username, password_hash, role, name) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $password_hash, $role, $name);

if ($stmt->execute()) {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Created</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f9;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }

            .message-container {
                background-color: #ffffff;
                padding: 20px 30px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                text-align: center;
                max-width: 400px;
                width: 100%;
            }

            h1 {
                color: #28a745;
                margin-bottom: 20px;
            }

            p {
                color: #555555;
                margin-bottom: 20px;
            }

            a {
                color: #007bff;
                text-decoration: none;
                font-weight: bold;
            }

            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="message-container">
            <h1>✅ User Created</h1>
            <p>User '<strong>$username</strong>' has been created successfully.</p>
            <p><a href="create_user.php">Create another user</a> or <a href="login.html">Go to Login</a>.</p>
        </div>
    </body>
    </html>
HTML;
} else {
    echo "❌ Failed to create user: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
