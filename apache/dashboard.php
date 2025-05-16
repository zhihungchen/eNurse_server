<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 15px 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        h1 {
            color: #333333;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }

        .button:hover {
            background-color: #218838;
        }

        .logout {
            background-color: #dc3545;
        }

        .logout:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Welcome to the Dashboard</h1>
    </div>
    <div class="content">
        <h2>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Your role: <?php echo htmlspecialchars($_SESSION['role']); ?></p>

        <a href="manage_tasks.php" class="button">Manage Tasks</a>
        <a href="logout.php" class="button logout">Logout</a>
    </div>
</body>

</html>
