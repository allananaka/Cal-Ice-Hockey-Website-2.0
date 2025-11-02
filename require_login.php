<?php
// Start session if not running
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If no user session, send to login page
if (empty($_SESSION['user']) || empty($_SESSION['user']['email'])) {
    header('Location: /login.php');
    exit;
  }
  