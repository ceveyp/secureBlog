<html>
	<head>
		<title> Admin Login </title>
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
	<h1> Admin Login </h1>
	<hr>
	<body>
		<form action="adminPage.php" method="POST">	
			<div class="container">
			Username: <input type="text" name="username" /><br>
			Password: <input type="password" name="password" /><br>
			<input type="hidden" name="done" value="1" />
			<br>
			</div>		
			<input type="submit" value="Login" />
		</form>
		<hr>
		<a href="login.php">User login</a><br>
	</body>
</html>



