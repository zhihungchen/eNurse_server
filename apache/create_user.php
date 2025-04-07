<?php
/**
 * create_user.php
 * 
 * Internal tool for developer/admin to create new user accounts for the eNurse system.
 * 
 * Usage:
 * 1. Admin must authenticate using MySQL root credentials (same as phpMyAdmin).
 * 2. After successful login, a form appears to create a new user.
 * 3. Form supports role assignment (e.g. 'nurse_lead', 'developer').
 * 
 * Notes:
 * - This file is for internal use only. Do NOT expose it to public access.
 * - Intended for use in a Docker/localhost or private hospital network.
 * - MySQL credentials are used only for session validation and DB insertion.
 */
session_start();

// If admin is already verified, show user creation form
if (isset($_SESSION["admin_verified"]) && $_SESSION["admin_verified"] === true) {
    show_user_creation_form();
    exit;
}

// Admin login form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["db_user"])) {
    $input_user = $_POST["db_user"];
    $input_pass = $_POST["db_pass"];

    // Load DB config to verify database name & host
    $configFile = __DIR__ . "/config.json";
    $config = json_decode(file_get_contents($configFile), true);
    $db = $config["database"];

    // Try connecting with provided credentials
    $conn = @new mysqli($db["host"], $input_user, $input_pass, $db["dbname"]);

    if ($conn->connect_error) {
        echo "<p style='color:red;'>❌ Access denied: Invalid MySQL credentials.</p>";
        show_admin_login_form();
        exit;
    }

    // Save verified session
    $_SESSION["admin_verified"] = true;
    $_SESSION["admin_user"] = $input_user;
    $_SESSION["admin_pass"] = $input_pass;

    // Redirect to refresh page and hide password from form data
    header("Location: create_user.php");
    exit;
}

// Default: show login
show_admin_login_form();
exit;

// ---------------------- FORM SECTIONS ----------------------

function show_admin_login_form() {
    echo <<<HTML
    <h2>Admin Login (MySQL credentials)</h2>
    <form method="POST">
        <label>MySQL Username:</label>
        <input type="text" name="db_user" required><br>

        <label>MySQL Password:</label>
        <input type="password" name="db_pass" required><br><br>

        <button type="submit">Verify</button>
    </form>
HTML;
}

function show_user_creation_form() {
    echo <<<HTML
    <h2>Create New User</h2>
    <form method="POST" action="create_user_submit.php">
        <label>Username:</label>
        <input type="text" name="username" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <label>Full Name:</label>
        <input type="text" name="name" required><br>

        <label>Role:</label>
        <select name="role">
            <option value="nurse_lead">Nurse Lead</option>
            <option value="developer">Developer</option>
        </select><br><br>

        <button type="submit">Create User</button>
    </form>
HTML;
}
?>
