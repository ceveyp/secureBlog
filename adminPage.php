<?php
	include("functions.php");
?>

<?php
	if(isset($_POST[selectedUsers])){
		$usersToActivate = $_POST[selectedUsers];
		$n = count($usersToActivate);
		$dbConn = dbConnect();
		if($dbConn){
			for($i = 0; $i < $n; ++$i){
				$username = $usersToActivate[$i];
				$code = md5(rand());
				$activationLink = "https://dev.ceveyp.com/blogs/activation.php?username=$username&code=$code";
				$insert = "INSERT INTO activations (username, code) VALUES ('$username','$code')";
        	                $result = pg_query($insert);
                	        if(!$result)
                        		echo "There was an error processing the request. Please contact the administrator. <br>";
	                        else
                        	        logMessage("Account activation link for user $username: $activationLink");
			}
			echo "Account activation links sent.<br>";
		}
              	pg_close($dbConn);
	}
?>

<?php
	$username = $_REQUEST[username];
        $password = $_REQUEST[password];
        $username = stripInput($username);
        $password = stripInput($password);
	$dbConn = dbConnect();
        if($dbConn){
        	$query = "SELECT password, salt FROM admins WHERE username='$username'";
                $result = pg_query($query);
                if(!$result){
	                echo "There was an error processing the request. Please contact the administrator.<br>";
                        exit();
                }
                else{
                        $ip = $_SERVER['REMOTE_ADDR'];
                       	if(pg_num_rows($result) == 0){
                        	logMessage("Failed admin login attempt, with username $username - IP: $ip");
                                echo "Failed login attempt. IP address $ip has been logged.<br>";
                                exit();
                        }
			$row = pg_fetch_row($result);
			$key = $row[0];
			$salt = $row[1];
			$hash = hash_pbkdf2("sha256", $password, $salt, 5, 20);
			if($hash != $key){
                        	logMessage("Failed admin login attempt, with username $username - IP: $ip");
                                echo "Failed login attempt. IP address $ip has been logged.<br>";
                                exit();
			}
                       	logMessage("Successful admin login, with username $username");
			$query = "SELECT id, username, email FROM blog_users WHERE activated = false";
			$result = pg_query($query);
			if(!$result){
	 		        echo "There was an internal error. Please contact the administrator.<br>";
        	      		exit();
			}
                        pg_close($dbConn);
                }
        }
	else{
 	       echo "There was an internal error. Please contact the administrator.<br>";
               exit();
        }
?>

<html>
	<head>
		<title> Admin Page </title>
 		<style type="text/css">
                        .container {
                        width: 505px;
                        clear: both;
                }
                .container input {
                        width: 100%;
                        clear: both;
                }
                </style>
	</head>
	<h1> Admin Page </h1>
	<hr>
	<body>
		<h2> Users Requiring Activation </h2>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">
			<table border=1>
			<tr>
				<th>ID</th>
				<th>Username</th>
				<th>Email</th>
				<th>Approval</th>
			</tr>
			<?php
				$rows = pg_num_rows($result);
				for($i = 0; $i < $rows; ++$i){
					$row = pg_fetch_row($result);
					echo "<tr>";
					echo "<td>" . "$row[0]" . "</td>";
					echo "<td>" . "$row[1]" . "</td>";
					echo "<td>" . "$row[2]" . "</td>";
					echo "<td>" . "<input type='checkbox' name='selectedUsers[]' value='$row[1]' />" . "</td>";
					echo "<tr>";
				}
			?>
				<input type="hidden" name="username" value="<?php echo $username ?>" />
				<input type="hidden" name="password" value="<?php echo $password ?>" />
			</table>
			<br>
			<input type="submit" value="Submit" />
		</form>
		<hr>
		<a href="login.php">User login</a><br>
	</body>
</html> 

