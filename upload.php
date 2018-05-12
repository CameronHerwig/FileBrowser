<?php
session_start();
if(isset($_SESSION['uploaddir']) && isset($_POST["submit"]))
{
    $dir = "C:/wamp64/" . $_SESSION['uploaddir'];
    $target_file = $dir . "/" . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
    {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";

        $d = strtotime("now");
        $log =  "[". date("Y-m-d h:i:sa", $d). "] File: " . $_SESSION['uploaddir'] . $_FILES["fileToUpload"]["name"] .  " - " ."uploaded by " . $_SESSION['username'] . "."; //logs upload and redirects
        $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);

        header("Location: index.php?dir=". $_SESSION['uploaddir']);
        exit();

    } 
    else 
    {
        echo "Sorry, there was an error uploading your file.";
    }

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
    <title>File Uploader</title>
  </head>
<body>
<div class="body">
<form action=upload.php method="post" enctype="multipart/form-data">
    <h4>Select file to upload:</h4>
    <div class="form-group">
    <input type="file" name="fileToUpload" id="fileToUpload">
    </div
    <br>
    <input type="submit" value="Upload" name="submit">
</form>
<br>
<a href='index.php'>Return</a>
</div>
</body>
</html>

