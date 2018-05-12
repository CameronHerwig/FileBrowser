<?php
    session_start();
    $self = $_SERVER['PHP_SELF'];
    echo "Current User: " . $_SESSION['username'] . "<br>";
    if($_SESSION['username'] == '') //if not signed in puts to login
    {
        header("Location: login.php");
        exit();
    }
    //Finds current position
    if(isset($_GET['dir'])) //if current dir isnt set defaults to user root
    {
        $currentdir = $_GET['dir'];
        $dir = "C:/wamp64/" . $_GET['dir'];    //grabs from url
        $size = strlen($dir);
        while ($dir[$size - 1] == '/') {    //removes any existing backslash
            $dir = substr($dir, 0, $size - 1);
            $size = strlen($dir);
        }
    }
    else 
    {
        header("Location: index.php?dir=SecureStorage/".$_SESSION['username'] . "/");
        exit();
    }

    if(explode("/",$currentdir)[1] != $_SESSION['username'] && $_SESSION['level'] == "User") //verifies users stay in their directories
    {
        header("Location: index.php?dir=SecureStorage/".$_SESSION['username'] . "/");
        $d = strtotime("now");
        $log =  "[". date("Y-m-d h:i:sa", $d). "] Unauthorized access of " . $currentdir .  " attempted by " . $_SESSION['username'] . "."; //logs unauthorized access
        $myfile = file_put_contents('C:/wamp64/SecureStorage/Admin-Logs/logs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);
        exit();
    }

    echo '<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link href="css/style.css" rel="stylesheet">
    <title>File Browser</title>
    </head> <div class="body">';
    //Row Grabber
    if(is_dir($dir)) 
    {
        if($handle = opendir($dir))
        {
            $pos = strrpos($dir, "/"); //get last directory split
            $topdir = substr($dir, 0, $pos + 1); 
            $topdir = str_replace("C:/wamp64/","",$topdir);
            //echo $topdir;
			$i = 0;
            while(false !== ($file = readdir($handle))) 
            {
                if ($file != "." && $file != "..") //if not a root directory
                {
					$rows[$i]['data'] = $file;
					$rows[$i]['dir'] = is_dir($dir . "/" . $file); //boolean to determine file vs directory
					$i++;
				}
			}
    		closedir($handle);
        }
        //Seperate directories 
        $temp = array();
        if(isset($rows))
        {
        for($i=0;$i<sizeof($rows);$i++)
        {
            if($rows[$i]['dir'])
            {
                array_push($temp,$rows[$i]);
            }
        }
        for($i=0;$i<sizeof($rows);$i++)
        {
            if(!$rows[$i]['dir'])
            {
                array_push($temp,$rows[$i]);
            }
        }
        $rows = $temp;
        echo "<table>";
        if($topdir !="")
        {
            if($_SESSION['level'] == "User" && ($currentdir != "SecureStorage/" . $_SESSION['username'] . "/"))
            {
                echo "<tr>  <a href='", $self, "?dir=", $topdir, "'>Previous Directory</a>\n  </tr>";
            }
            else if($_SESSION['level'] == "Mod" || $_SESSION['level'] == "Admin")
            {
                echo "<tr>  <a href='", $self, "?dir=", $topdir, "'>Previous Directory</a>\n  </tr>";
            }
		    
        }
        echo "<tr>    <th>    [UP]    </th>";
        echo "<th>File Name</th>";
        echo "<th>  size (bytes)    </th>";
        echo "<th>  </th>";
        echo "<th>  </th>";
		echo "</tr>";
		foreach($rows as $row) {
            $file = str_replace("C:/wamp64/","",$dir) . '/' . $row['data'];
			echo "<tr>";
			echo "<td>";
            if ($row['dir']) 
            {
				echo "[DIRECTORY]";
				$file_type = "dir";
            } 
            else 
            {
				echo "[FILE]";
				$file_type = "file";
			}
            echo "</td>";
            if($row['dir'])
            {
                echo "<td> <a href='", $self, "?dir=", $file, "'>", $row['data'], "</a> </td>\n";
            }
            else
            {
                echo  "<td>" . $row['data'] . "</td>";
            }
            
            if(filesize("C:/wamp64/".$file)>1000000)
            {
                echo "<td>" . round(filesize("C:/wamp64/".$file)/1000000,2) . " Mb</td>";
            }
            else
            {
                echo "<td>" . round(filesize("C:/wamp64/".$file)/1000,2) . " Kb</td>";
            }
            
            if (!$row['dir']) 
            {
				echo "<td>  <a href='download.php?dl=", $file, "'>Download ", $file_type, "</a>\n    </td>";
            } 
            if($row['dir']) 
            {
				echo "<td>  <a href='delete.php?type=folder&dir=$file'>Delete ", $file_type, "</a>\n    </td>";
            }
            else
            {
                echo "<td>  <a href='delete.php?type=file&dir=$file'>Delete ", $file_type, "</a>\n    </td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        }
        else{
            echo "<table>";
            if($topdir !="")
            {
                if($_SESSION['level'] == "User" && ($currentdir != "SecureStorage/" . $_SESSION['username'] . "/"))
                {
                    echo "<tr>  <a href='", $self, "?dir=", $topdir, "'>Previous Directory</a>\n  </tr>";
                }
                else if($_SESSION['level'] == "Mod" || $_SESSION['level'] == "Admin")
                {
                    echo "<tr>  <a href='", $self, "?dir=", $topdir, "'>Previous Directory</a>\n  </tr>";
                }
                
            }
            echo "<tr>    <th>    [UP]    </th>";
            echo "<th>File Name</th>";
            echo "<th>  size (bytes)    </th>";
            echo "<th>  </th>";
            echo "<th>  </th>";
            echo "</tr>";
            echo "</table>";
        }
    }

    echo "<a href='logout.php'>Logout</a>" . "&emsp;";
    if($topdir !="")
    {
    $_SESSION['uploaddir'] = $currentdir;
    echo "<a href='upload.php'>Upload a File</a>" . "&emsp;";
    }
    if($topdir !="")
    {
        $_SESSION['uploaddir'] = $currentdir;
    echo "<a href='folder.php'>Add new folder</a>" . "&emsp;";
    } 
    if($_SESSION['level']=="Admin")
    {
    echo "<a href='admin.php'>Administator Tools</a>";
    }
    echo "</div>";

?>