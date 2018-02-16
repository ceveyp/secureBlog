<?php
        include("functions.php");
?>

<?php
        function validateInput($username, $code){
                if(preg_match("/[^a-zA-Z]+/", $username))
                        return 1;
		if(strlen($code) != 32)
			return 1;
		if(preg_match("/[^a-fA-F0-9]/", $code))
			return 1;
		return 0;
        }
?>

<?php
	$username = $_REQUEST[username];
	$code = $_REQUEST[code];
	$username = stripInput($username);
	$code = stripInput($code);
	if(validateInput($username, $code)){
		$ip = $_SERVER['REMOTE_ADDR'];
		logMessage("Unauthorized user in activation page, attempted username $username - IP: $ip");
		echo "Unauthorized access detected. IP address $ip is logged.<br>";
		exit();
	}
        $dbConn = dbConnect();
	if($dbConn){
		$query = "SELECT id FROM activations WHERE username='$username' AND code='$code'";
		$result = pg_query($query);
		if(!$result){
			echo "An internal error occurred. Please contact the administrator.<br>";
			exit();
		}
		if(pg_num_rows($result) == 0){
			logMessage("Unauthorized user in activation page, attempted username $username - IP: $ip");
			echo "Unauthorized access detected. IP address $ip is logged.<br>";
			exit();
		}
		else{
			$query = "DELETE FROM activations WHERE username = '$username'";
			$result = pg_query($query);
			if(!$result){
				echo "An internal error occurred. Please contact the administrator.<br>";
				exit();
			} 
			$query = "UPDATE blog_users SET activated=true WHERE username='$username'";
			$result = pg_query($query);
			if(!$result){
				echo "An internal error occurred. Please contact the administrator.<br>";
				exit();
			} 
			logMessage("User $username account activated.");
		}
	}
	pg_close($dbConn);
?>

<html>
	<head>
		<title> Activation </title>
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
	<body>
		Account successfully activated. <a href="login.php"> Login </a>
	</body>
</html>



