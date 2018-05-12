<?php
session_start();
var_dump($_SESSION['delete'][1]);
if(explode("/",$_GET['dir'])[1] != $_SESSION['username'] && $_SESSION['level'] == "User") //verifies file is user's or permissions are elevated
{
    header("Location: index.php?dir=SecureStorage/".$_SESSION['username'] . "/");
    $d = strtotime("now");
    $log =  "[". date("Y-m-d h:i:sa", $d). "] Unauthorized deletion of " . $_GET['dir'] .  " attempted by " . $_SESSION['username'] . "."; //logs unauthorized deletion and redirects
    $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
    exit();
}

if(isset($_GET['dir'])) 
{
    $dir = "C:/wamp64/" . $_GET['dir'];    //grabs from url
    $size = strlen($dir);
}

var_dump($_GET['type']);

if($_GET['type'] == "file")
{
    echo $dir;
    unlink($dir);
    $d = strtotime("now");
    $log =  "[". date("Y-m-d h:i:sa", $d). "] File: " . $_GET['dir'] . " - " ."deleted by " . $_SESSION['username'] . "."; //for normal files simply unlinks and logs
    $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
    header("Location: index.php?dir=SecureStorage/".$_SESSION['username'] . "/");
    exit;
}
else if($_GET['type'] == "folder")
{
    echo $dir;
    $iter = new RecursiveDirectoryIterator($direc, RecursiveDirectoryIterator::SKIP_DOTS); //recursively delete all subdirectories and files
    $folders = new RecursiveIteratorIterator($iter,RecursiveIteratorIterator::CHILD_FIRST);
    foreach($folders as $folder) {
        if ($folder->isDir()){
            rmdir($folder->getRealPath());
        } else {
            unlink($folder->getRealPath());
        }
    }
    rmdir($dir);
    $d = strtotime("now");
    $log =  "[". date("Y-m-d h:i:sa", $d). "] Folder: " .$_GET['dir'] . " - " ."deleted by " . $_SESSION['username'] . "."; //logs deletion and redirects
    $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
    header("Location: index.php?dir=SecureStorage/".$_SESSION['username'] . "/");
    exit;
}


//$d = strtotime("now");
//$log =  "[". date("Y-m-d h:i:sa", $d). "] File: " . $_GET['dl'] . " - " ."downloaded by " . $_SESSION['username'] . ".";
//$myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
/*
if (file_exists($dir)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($dir).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($dir));
    readfile($dir);
    
}
*/

?>