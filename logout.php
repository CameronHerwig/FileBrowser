<?php
session_start();
$d = strtotime("now"); //logs impending logout by user
$log =  "[". date("Y-m-d h:i:sa", $d). "] Logout by " . $_SESSION['level'] . ": " . $_SESSION['username'] . ".";
$myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
unset($_SESSION);  //unsets and destroys session
session_destroy();
session_write_close();
header("Location: login.php"); //then redirects for login
exit();
?>