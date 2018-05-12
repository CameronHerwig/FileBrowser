<?php
session_start();
if(isset($_SESSION['uploaddir']) && isset($_POST["submit"]))
{
    mkdir("C:/wamp64/".$_SESSION['uploaddir'] . "/" . $_POST["folderNew"], 0700); //creates directory
    $d = strtotime("now");
    $log =  "[". date("Y-m-d h:i:sa", $d). "] Folder: " . "C:/wamp64/". $_SESSION['uploaddir'] . $_POST["folderNew"] . " added by " . $_SESSION['username'] . "."; //logs creation
    $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
    header("Location: index.php?dir=".$_SESSION['uploaddir']);
    exit();
}


?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link href="css/style.css" rel="stylesheet">
    <title>Folder Creator</title>
  </head>
<body>
<div class="body">
<form method="post" action="folder.php">
<div class="form-group">
  <label for="exampleInputEmail1">New Folder Name</label>
  <input type="text" class="form-control" name="folderNew" placeholder="" maxlength="15" required>
</div>
<button type="submit" name="submit" class="btn btn-default">Submit</button>
</form>
<br>
<a href='index.php'>Return</a>
</div>
</body>
</html>
