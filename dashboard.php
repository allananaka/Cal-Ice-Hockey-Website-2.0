<?php
require __DIR__ . '/../config.php';
session_start();

// Check if user is signed in
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}

// Optionally, grab their info
$email = $_SESSION['user']['email'];
$name  = $_SESSION['user']['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Dashboard</title>
    <style>
        body {
            background: #f8f9fa;
            text-align: center;
            font-family: Arial, sans-serif;
            margin-top: 100px;
        }
        img {
            max-width: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .logout {
            margin-top: 20px;
        }
        .logout a {
            display: inline-block;
            padding: 10px 20px;
            background: #c0392b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout a:hover {
            background: #a93226;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
    <p>You’re signed in as <strong><?php echo htmlspecialchars($email); ?></strong></p>

    <!-- Display your team image -->
    <img src="images/heinous 2.JPG" alt="Team Photo">

    <div class="logout">
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
