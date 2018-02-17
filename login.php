<?php
        include("functions.php");
?>

<?php
	if($_REQUEST[done]){
		$username = $_REQUEST[username];
		$password = $_REQUEST[password];
		$username = stripInput($username);
		$password = stripInput($password);
		$ret = checkPassword($username, $password);
		$ip = $_SERVER['REMOTE_ADDR'];
		if($ret == 2){
			echo "There was an internal error. Please contact the administrator.<br>";
			exit();
		}
		elseif($ret == 1){
			logMessage("Failed user login attempt, with username $username - IP: $ip");
			echo "Username or password is incorrect.<br>";
		}
		else{
			$ret = checkActivationStatus($username);
			if($ret == 2){
				echo "There was an internal error. Please contact the administrator.<br>";
				exit();
			}
			elseif($ret == 1){
				logMessage("Unactivated user is attempting to login, with username $username - IP: $ip");
				echo "User account has not yet been activated.<br>";
			}
			else
				echo "Login successful.<br>";
		}
	}
?>

<html>
	<head>
		<title> Login </title>
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
	<h1> Login </h1>
	<hr>
	<body>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">	
			<div class="container">
			Username: <input type="text" name="username" /><br>
			Password: <input type="password" name="password" /><br>
			<input type="hidden" name="done" value="1" />
			<br>
			</div>
			<input type="submit" value="Login" />
		</form>
		<hr>
		<a href="adminLogin.php">Admin login</a><br>
	</body>
</html>



