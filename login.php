<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link href="css/style.css" rel="stylesheet">
    <title>Login Page</title>
  </head>
  <body>

  <div class="row">

    <div class="col-md-6" id="login">
      <h1>Log In</h1>
      <form method="post" action="login.php">
        <div class="form-group">
          <label for="exampleInputEmail1">Username</label>
          <input type="text" class="form-control" name="username" placeholder="username" maxlength="15" required>
        </div>
        <div class="form-group">
          <label for="exampleInputPassword1">Password</label>
          <input type="password" class="form-control" name="password" placeholder="Password" maxlength="32" required>
        </div>
        <button type="submit" name="loginbutton" class="btn btn-default">Submit</button>
      </form>
    </div>

  </div>
</div>
</body>
</html>

<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE);
define(LDAP_OPT_DIAGNOSTIC_MESSAGE, 0x0032);
ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);


if(isset($_POST['loginbutton']))
{
   login();
} 
if($_SESSION['username'] != '') { //if already logged in get redirected
  header("Location: index.php?dir=SecureStorage/".$_SESSION['username'] . "/");
  exit();
}

function login(){

  $username = $_POST['username'];
  $password = $_POST['password'];
  session_start();

  echo "<h3>LDAP query results</h3>";
  echo "Connecting ...";
  $ds=ldap_connect("ldap://localhost:10389");  //replace with valid ldap address
  //$ds=ldap_connect("ldaps://localhost:10636");  //for encryption enabled servers
  ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3); 
  ldap_set_option( $ds, LDAP_OPT_REFERRALS, 0 );
  echo "connect result is " . $ds . "<br />";
  if($ds) { 
      echo "Binding ..."; 
      ldap_start_tls($ds);
      $r=ldap_bind($ds,"cn=$username,ou=Users,dc=example,dc=com", "$password");   //binds to authenticate user
      var_dump($r);
      echo "ldap_error: " . ldap_error($ds);
      echo "<br>";
      ldap_get_option($ds, LDAP_OPT_DIAGNOSTIC_MESSAGE, $err); //in case of failure
      echo "ldap_get_option: $err";
    if($r)
    {
      $_SESSION['username'] = $username;
      echo "login success\n";

      $dn = "ou=Groups,dc=example,dc=com";
      $filter="(member=cn=". $username .",ou=Users,dc=example,dc=com)"; //acquires permission level using filters
      $justthese = array("ou", "sn");

      $sr=ldap_search($ds, $dn, $filter, $justthese);
      $info = ldap_get_entries($ds, $sr);

      switch ($info[0]['dn']) {
        case "cn=Admin,ou=Groups,dc=example,dc=com":
            $_SESSION['level'] = "Admin";
            break;
        case "cn=Mod,ou=Groups,dc=example,dc=com":
            $_SESSION['level'] = "Mod";
            break;
        case "cn=User,ou=Groups,dc=example,dc=com":
            $_SESSION['level'] = "User";
            break;
      }

      $d = strtotime("now"); //logs login information
      $log =  "[". date("Y-m-d h:i:sa", $d). "] Login by " . $_SESSION['level'] . ": " . $_SESSION['username'] . ".";
      $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);

      if($_SESSION['username'] != '') {
        header("Location: index.php?dir=SecureStorage/".$_SESSION['username'] . "/");
        exit();
      }
    }
    else
    {
      $d = strtotime("now"); //logs login failure
      $log =  "[". date("Y-m-d h:i:sa", $d). "] Login Failed for account " . $username . ".";
      $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
    }
      ldap_close($ds);
  
  } else {
      echo "<h4>Unable to connect to LDAP server</h4>";
  }
}

?>
