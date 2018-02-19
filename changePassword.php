<?php
        include("functions.php");
	session_start();
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
	if(isset($_SESSION[RPFORMFILLED])){
		$password = $_POST[password];
		$confirmation = $_POST[confirmation];
		$username = $_SESSION[username];
		if($password != $confirmation)
			echo "Passwords must match.<br>";
		if(!validatePassword($password)){
			$ret = createPassword($username, $password);
			if($ret){
				echo "There was an internal error. Please contact the administrator.<br>";
				exit();
			}
			$_SESSION['loginFormMessage'] = "Password has been updated.<br>";
			logMessage("User $username has successfully changed password.");
			header('Location: ' . "login.php");
			exit();
		}
	}
?>

<?php
	if(!isset($_SESSION[RPFORMFILLED])){
		$username = $_GET[username];
		$code = $_GET[code];
		$username = stripInput($username);
		$code = stripInput($code);
		$ip = $_SERVER['REMOTE_ADDR'];
		if($username != "" && $code != ""){
			if(validateInput($username, $code)){
				logMessage("Invalid input for changePassword.php, username $username, nonce $code - IP: $ip");
				header('Location: ' . "login.php");
				exit();
			}
			$ret = checkPasswordResetExists($username, $code);
			if($ret == 2){
				echo "There was an internal error. Please contact the administrator.<br>";
				exit();
			}
			if($ret == 1){
				logMessage("Invalid input for changePassword.php, username $username, nonce $code - IP: $ip");
				header('Location: ' . "login.php");
				exit();
			}
			$_SESSION[RPFORMFILLED] = true;
			$_SESSION[username] = $username;
		}
		else{
			logMessage("Direct URL access for changePassword.php - IP: $ip");
			header('Location: ' . "login.php");
			exit();
		}
	}
?>

<html>
	<head>
		<title> Reset Password </title>
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
	<h1> Reset Password </h1>
	<hr>
	<body>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">	
			<div class="container">
			New password: <input type="password" name="password" /><br>
			Confirm: <input type="password" name="confirmation" /><br>
			<br>
			</div>
			<input type="submit" value="Change" />
		</form>
		<hr>
		<a href="login.php">User login</a><br>
	</body>
</html>



