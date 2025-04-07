<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

$username = $_SESSION["username"];
$role = $_SESSION["role"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p>Your role: <strong><?php echo htmlspecialchars($role); ?></strong></p>

    <?php if ($role === 'nurse_lead'): ?>
        <h3>📝 Nurse Lead Task Panel (Demo)</h3>
        <ul>
            <li><button>Assign New Task</button></li>
            <li><button>View Task History</button></li>
            <li><button>Robot Status</button></li>
        </ul>
    <?php elseif ($role === 'developer'): ?>
        <h3>🛠️ Developer Debug Panel (Demo)</h3>
        <ul>
            <li><button>System Logs</button></li>
            <li><button>Test API Endpoint</button></li>
            <li><button>Robot Simulator</button></li>
        </ul>
    <?php else: ?>
        <p>⚠️ Unknown role. Limited access.</p>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
