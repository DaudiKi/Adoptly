<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to the home page
header("Location: AdoptlyHome.php");
exit();
?>