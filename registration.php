<?php
        include("functions.php");
?>

<?php
	function validateInput($username,$email,$password){
		if(!validateUsername($username)){
			if(!validateEmail($email)){
				if(!validatePassword($password))
					return 0;
			}
		}
		return 1;
	}
?>

<?php
	if($_REQUEST[done]){
		$username = $_REQUEST[username];
		$email = $_REQUEST[email];
		$password = $_REQUEST[password];
		if(!validateInput($username, $email, $password)){
			$username = stripInput($username);
			$email = stripInput($email);
			$password = stripInput($password);
		        $dbConn = dbConnect();
			if($dbConn){
				$query = "INSERT INTO blog_users (username, email, password, activated) VALUES ('$username','$email','$password',FALSE)";
				$query = pg_query($query);
				$code = md5(rand());
				$activationLink = "https://dev.ceveyp.com/blogs/activation.php?username=$username&code=$code";
				if(!$query){
					echo "There was an error processing the request. Please contact the administrator. <br>";
					exit();
				}
				else{
					pg_close($dbConn);
					logMessage("New user $username activation request, with email $email");
					echo "Account activation request made. Awaiting admin approval.<br>";
				}
			}
		}
	}
?>

<html>
	<head>
		<title> Registration </title>
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
	<h1> Register For a Blog </h1>
	<hr>
	<body>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">	
			<div class="container">
			Username: <input type="text" name="username" /><br>
			Email:    <input type="text" name="email" /><br>
			Password: <input type="password" name="password" /><br>
			<input type="hidden" name="done" value="1" />
			<br>
			</div>
			<input type="submit" value="Get Started" />
		</form>
		<br>
		Already have an account? <a href="login.php">Login</a>
		<hr>
	</body>
</html>



