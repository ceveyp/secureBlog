<?php
        include("functions.php");
	session_start();
?>

<?php
        if(isset($_POST[email])){
                $email = $_POST[email];
                $email = stripInput($email);
                $ret = checkForEmail($email);
                $ip = $_SERVER['REMOTE_ADDR'];
                if($ret == 2){
                        echo "There was an internal error. Please contact the administrator.<br>";
                        exit();
                }
                elseif($ret == 1){
                        logMessage("Unregistered user attempting to change password, with email $email - IP: $ip");
                        echo "Email does not exist.<br>";
                }
		else{	
			$username = getUsername($email);
			$code = createNonce();
			$resetLink = "https://dev.ceveyp.com/blogs/changePassword.php?username=$username&code=$code";
			if(createReset($email, $code)){
				echo "There was an internal error. Please contact the administrator.<br>";
				exit();
			}
			logMessage("Change password request made for user $username, reset link: $resetLink");
			$_SESSION['loginFormMessage'] = "Password reset link sent.<br>";
			header('Location: ' . "login.php");
			exit();
		}
        }
?>

<html>
	<head>
		<title> Password Reset </title>
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
	<h1> Password Reset </h1>
	<hr>
	<body>
		<h2> Please Enter your Email: </h2>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">	
			<div class="container">
			<input type="text" name="email" />
			<br><br>
			</div>
			<input type="submit" value="Submit" />
		</form>
		<hr>
		<a href="login.php">User login</a><br>
	</body>
</html>



