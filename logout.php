<?php
session_start();      // start session so we can clear it
session_unset();      // remove all session variables
session_destroy();    // destroy the session itself

// Redirect to homepage
header("Location: index.html");
exit;