<?php
session_start();
if(explode("/",$_GET['dl'])[1] != $_SESSION['username'] && $_SESSION['level'] == "User") //verifies its the current user's files if not mod+
{
    header("Location: index.php?dir=SecureStorage/".$_SESSION['username'] . "/");
    $d = strtotime("now");
    $log =  "[". date("Y-m-d h:i:sa", $d). "] Unauthorized download of " . $_GET['dl'] .  " attempted by " . $_SESSION['username'] . "."; //logs unauthorized downloads and redirects
    $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
    exit();
}

if(isset($_GET['dl'])) 
{
    $dir = "C:/wamp64/" . $_GET['dl'];    //grabs from url
    $size = strlen($dir);
}


$d = strtotime("now");
$log =  "[". date("Y-m-d h:i:sa", $d). "] File: " . $_GET['dl'] . " - " ."downloaded by " . $_SESSION['username'] . "."; //logs download and redirects
$myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);

if (file_exists($dir)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($dir).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($dir));
    readfile($dir);
    exit;
}

?>