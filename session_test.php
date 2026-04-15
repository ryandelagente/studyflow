<?php
session_start();
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = "Session is Working!";
    echo "Session initialized. <a href='session_test.php'>Click here to refresh and verify.</a>";
} else {
    echo "Success: " . $_SESSION['test'];
}
?>