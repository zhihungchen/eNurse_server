<?php
/**
 * create_user.php
 * 
 * Internal tool for developer/admin to create new user accounts for the eNurse system.
 * 
 * Usage:
 * 1. Admin must authenticate using MySQL root credentials (same as phpMyAdmin).
 * 2. After successful login, a form appears to create a new user.
 * 3. Form supports role assignment (e.g. 'admin', 'user', 'viewer').
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
    $input_user = htmlspecialchars($_POST["db_user"]);
    $input_pass = htmlspecialchars($_POST["db_pass"]);

    // Load DB config to verify database name & host
    $configFile = __DIR__ . "/config.json";
    $config = json_decode(file_get_contents($configFile), true);
    // $db = $config["database"];

    // Try connecting with provided credentials
    $conn = @new mysqli($config["host"], $input_user, $input_pass, $config["database"]);


    if ($conn->connect_error) {
        header("Location: create_user.php?error=1");
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
    $errorMsg = '';
    if (isset($_GET['error']) && $_GET['error'] == 1) {
        $errorMsg = '<p class="error">Invalid MySQL credentials. Please try again.</p>';
    }

    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
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

            .form-container {
                background-color: #ffffff;
                padding: 20px 30px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 400px;
                text-align: center;
            }

            h1 {
                margin-bottom: 20px;
                color: #333333;
            }

            label {
                display: block;
                text-align: left;
                margin-bottom: 5px;
                font-weight: bold;
                color: #555555;
            }

            input {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #cccccc;
                border-radius: 4px;
                font-size: 14px;
            }

            button {
                width: 100%;
                padding: 10px;
                background-color: #007bff;
                color: #ffffff;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
            }

            button:hover {
                background-color: #0056b3;
            }

            p {
                margin-top: 15px;
                font-size: 14px;
                color: #555555;
            }

            p.error {
                color: #ff4d4d;
                font-weight: bold;
            }
        </style>
    </head>

    <body>
        <div class="form-container">
            <h1>Admin Login</h1>
            <form method="POST">
                <label for="db_user">MySQL Username:</label>
                <input type="text" id="db_user" name="db_user" placeholder="Enter MySQL username" required>

                <label for="db_pass">MySQL Password:</label>
                <input type="password" id="db_pass" name="db_pass" placeholder="Enter MySQL password" required>

                <button type="submit">Verify</button>
            </form>

            $errorMsg
        </div>
    </body>

    </html>
HTML;
}


function show_user_creation_form() {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create User</title>
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

            .form-container {
                background-color: #ffffff;
                padding: 20px 30px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 400px;
                text-align: center;
            }

            h1 {
                margin-bottom: 20px;
                color: #333333;
            }

            label {
                display: block;
                text-align: left;
                margin-bottom: 5px;
                font-weight: bold;
                color: #555555;
            }

            input, select {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #cccccc;
                border-radius: 4px;
                font-size: 14px;
            }

            button {
                width: 100%;
                padding: 10px;
                background-color: #28a745;
                color: #ffffff;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
            }

            button:hover {
                background-color: #218838;
            }

            p {
                margin-top: 15px;
                font-size: 14px;
                color: #555555;
            }

            p.error {
                color: #ff4d4d;
                font-weight: bold;
            }

            a {
                color: #007bff;
                text-decoration: none;
            }

            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>

    <body>
        <div class="form-container">
            <h1>Create User</h1>
            <form action="create_user_submit.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Enter a username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter a password" required>

                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                    <option value="viewer">Viewer</option>
                </select>

                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter full name" required>

                <button type="submit">Create User</button>
            </form>

            <p><a href="login.html">Back to Login</a></p>
        </div>
    </body>

    </html>
HTML;
}
?>
