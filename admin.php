<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE);
define(LDAP_OPT_DIAGNOSTIC_MESSAGE, 0x0032);
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option( $ds, LDAP_OPT_REFERRALS, 0 );


if($_SESSION['level'] != "Admin")
{ 
    header("Location: index.php?dir=SecureStorage/".$_SESSION['username'] . "/"); //logs attempted access by non-administrators and redirects
    $d = strtotime("now");
    $log =  "[". date("Y-m-d h:i:sa", $d). "] Unauthorized access of admin page attempted by " . $_SESSION['username'] . ".";
    $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
    exit();
}

if(isset($_POST['signupbutton']))
{
  signup();
}
if(isset($_POST['deletebutton']))
{
  delete();
}
if(isset($_POST['modbutton']))
{
  modify();
}
if(isset($_POST['clearLogs']))
{
  unlink("C:/wamp64/SecureStorage/Admin-Logs/logs.txt"); //Empties logs and logs the reset
  $d = strtotime("now");
  $log =  "[". date("Y-m-d h:i:sa", $d). "] Logs cleared by " . $_SESSION['username'] . ".";
  $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
}

function signup() {
    $info = array();
    $pass = $_POST['adminPassword'];
    $info["cn"] = $_POST['usernameNew'];
    $info["sn"] = $_POST['usernameNew'];
    $info['userPassword'] = '{MD5}' . base64_encode(pack('H*',md5($_POST['passwordNew']))); //hashes password for authentification
    $info['objectclass'][0] = "top";
    $info['objectclass'][1] = "person";
    $info['objectclass'][2] = "organizationalPerson";
    $info['objectclass'][3] = "inetOrgPerson";

    $ds=ldap_connect("ldap://localhost:10389"); 

    if($ds)
    {
      $r=ldap_bind($ds,"cn=".$_SESSION['username'] . ",ou=users,dc=example,dc=com", $pass);     

      $r = ldap_add($ds, "cn=" . $info["cn"] . ",ou=Users,dc=example,dc=com", $info);

      $group_name = "cn=". $_POST['userType'] .",ou=Groups,dc=example,dc=com";
      $group_info['member'] = "cn=" . $info["cn"] . ",ou=Users,dc=example,dc=com"; 
      ldap_mod_add($ds,$group_name,$group_info); //adds user using defined inputs

      $d = strtotime("now"); //logs new addition specifying executor
      $log =  "[". date("Y-m-d h:i:sa", $d). "] Account: " . $_POST['userType'] . " - " . $info["cn"] . " added by admininstrator " . $_SESSION['username'] . ".";
      $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);

    if($r)
    {
        mkdir("C:/wamp64/SecureStorage/".$info["cn"], 0700); //creates storage directory for new user
    } 
  }
}

function delete() {
  $pass = $_POST['adminPassword'];

  $ds=ldap_connect("ldap://localhost:10389");  

  if($ds)
  {

    $r=ldap_bind($ds,"cn=".$_SESSION['username'] . ",ou=users,dc=example,dc=com", $pass); 

    $dn = "ou=Groups,dc=example,dc=com";
    $filter="(member=cn=". $_POST['userDelete'] .",ou=Users,dc=example,dc=com)";
    $justthese = array("ou", "sn");

    $sr=ldap_search($ds, $dn, $filter, $justthese);
    $info = ldap_get_entries($ds, $sr);

    $group = $info[0]['dn'];
    
    $group_info['member'] = 'cn='.$_POST['userDelete'].',ou=Users,dc=example,dc=com';
    
    ldap_mod_del($ds, $group, $group_info);  //deletes user from permission table
    $userdn = 'cn='.$_POST['userDelete'].',ou=Users,dc=example,dc=com';
    ldap_delete($ds,$userdn);  //removes users object

    $d = strtotime("now"); //logs deletion and executor
    $log =  "[". date("Y-m-d h:i:sa", $d). "] Account: " . $_POST['userDelete'] . " deleted by administrator " . $_SESSION['username'] . "."; 
    $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);

  if($r)
  {
    $direc = "C:/wamp64/SecureStorage/".$_POST['userDelete'];  //uses recursive iterators to delete user's folder and all sub-directories/files
    $iter = new RecursiveDirectoryIterator($direc, RecursiveDirectoryIterator::SKIP_DOTS);
    $folders = new RecursiveIteratorIterator($iter,RecursiveIteratorIterator::CHILD_FIRST);
    foreach($folders as $folder) {
        if ($folder->isDir()){
            rmdir($folder->getRealPath());
        } else {
            unlink($folder->getRealPath());
        }
    }
    rmdir($direc);
  } 
}
}

function modify(){
  $pass = $_POST['adminPassword'];
  
    $ds=ldap_connect("ldap://localhost:10389"); 
    {
      $r=ldap_bind($ds,"cn=".$_SESSION['username'] . ",ou=users,dc=example,dc=com", $pass);   
  
      $dn = "ou=Groups,dc=example,dc=com";
      $filter="(member=cn=". $_POST['userMod'] .",ou=Users,dc=example,dc=com)";
      $justthese = array("ou", "sn");
  
      $sr=ldap_search($ds, $dn, $filter, $justthese);
      $info = ldap_get_entries($ds, $sr);
  
      $group = $info[0]['dn'];
      
      $group_info['member'] = 'cn='.$_POST['userMod'].',ou=Users,dc=example,dc=com';
      ldap_mod_del($ds, $group, $group_info); //add delete user from permission table

      $group_name = "cn=". $_POST['newPerm'] .",ou=Groups,dc=example,dc=com";
      $group_info['member'] = "cn=" . $_POST['userMod'] . ",ou=Users,dc=example,dc=com"; 
      ldap_mod_add($ds,$group_name,$group_info); //add user to new permission table

  
      $d = strtotime("now");
      $log =  "[". date("Y-m-d h:i:sa", $d). "] Account: " . $_POST['userMod'] . " changed to " . $_POST['newPerm'] . " by administrator " . $_SESSION['username'] . "."; //logs perm change and executor
      $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
  }
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link href="css/style.css" rel="stylesheet">
    <title>Admin Page</title>
  </head>
<body>
<div class="row">
<div class="col-md-6">
      <h1>Sign Up</h1>
      <form method="post" action="admin.php">
        <div class="form-group">
          <label for="exampleInputEmail1">Username</label>
          <input type="text" class="form-control" name="usernameNew" placeholder="username (15 character max)" maxlength="15" required>
        </div>
        <div class="form-group">
          <label for="exampleInputPassword1">Password</label>
          <input type="password" class="form-control" name="passwordNew" placeholder="password (32 character max)" maxlength="32" required>
        </div>
        <div class="form-group">
          <label for="exampleInputType">User Type</label>
          <select name="userType">
            <option value="Admin">Administrator</option>
            <option value="Mod">Moderator</option>
            <option value="User">User</option>
        </select>
        <div class="form-group">
          <label for="exampleInputPassword2">Admin Password</label>
          <input type="password" class="form-control" name="adminPassword" placeholder="password (32 character max)" maxlength="32" required>
        </div>
        </div>
        <button type="submit" name="signupbutton" class="btn btn-default">Submit</button>
      </form>
      <h1>Delete</h1>
      <form method="post" action="admin.php">
        <div class="form-group">
          <label for="exampleInputEmail1">Username</label>
          <input type="text" class="form-control" name="userDelete" placeholder="username (15 character max)" maxlength="15" required>
        </div>
        <div class="form-group">
          <label for="exampleInputPassword2">Admin Password</label>
          <input type="password" class="form-control" name="adminPassword" placeholder="password (32 character max)" maxlength="32" required>
        </div>
        <button type="submit" name="deletebutton" class="btn btn-default">Submit</button>
      </form>
      <h1>Modify</h1>
      <form method="post" action="admin.php">
        <div class="form-group">
          <label for="exampleInputEmail1">Username</label>
          <input type="text" class="form-control" name="userMod" placeholder="username (15 character max)" maxlength="15" required>
        </div>
        <label for="exampleInputType">User Type</label>
          <select name="newPerm">
            <option value="Admin">Administrator</option>
            <option value="Mod">Moderator</option>
            <option value="User">User</option>
        </select>
        <div class="form-group">
          <label for="exampleInputPassword2">Admin Password</label>
          <input type="password" class="form-control" name="adminPassword" placeholder="password (32 character max)" maxlength="32" required>
        </div>
        <button type="submit" name="modbutton" class="btn btn-default">Submit</button>
      </form>
      <form method="post" action="admin.php">        
        <button type="submit" name="clearLogs" class="btn btn-default">Clear Logs</button>
      </form>
      <a href='index.php'>Return</a>
    </div>


</div>
</body>
</html>