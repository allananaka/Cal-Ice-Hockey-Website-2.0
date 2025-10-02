<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Access Denied</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      margin-top: 100px;
      background: #f8f9fa;
    }
    .box {
      display: inline-block;
      padding: 30px;
      border: 1px solid #ccc;
      border-radius: 10px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h1 {
      color: #c0392b;
    }
    a {
      color: #4285f4;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="box">
    <h1>Access Denied</h1>
    <p>Your Google account (<strong>
      <?php echo isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : "unknown"; ?>
    </strong>) is not authorized to access this site.</p>

    <p>If you think this is a mistake, please contact the site administrator.</p>

    <p><a href="login.php">Try logging in with a different account</a></p>
  </div>
</body>
</html>
