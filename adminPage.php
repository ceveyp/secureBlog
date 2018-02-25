<?php
	include("functions.php");
	session_start();
	unset($_SESSION[editUser]);
?>

<?php
	if(!$_SESSION[ADMINAUTH]){
		$username = $_POST[username];
        	$password = $_POST[password];
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
				$_SESSION[ADMINAUTH]=true;
        	                pg_close($dbConn);
                	}
	        }
		else{
			echo "There was an internal error. Please contact the administrator.<br>";
	               exit();
        	}
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
		if(isset($_POST[newUsername])){
			$newUser = $_POST[newUsername];
			$newPass = $_POST[newPassword];
			$newEmail = $_POST[newEmail];
			$newUser = stripInput($newUser);
			$newPass = stripInput($newPass);
			$newEmail = stripInput($newEmail);
			$insert = "INSERT INTO blog_users (username, email, activated, log_inv) VALUES ('$newUser', '$newEmail', true, 0)"; 
			if(!validateUsername($newUser)){
				if(!validatePassword($newPass)){
					if(!validateEmail($newEmail)){
						$result = doSQL($insert);
						if(!$result)
							echo "There was an internal error. Please contact the administrator.<br>";
						else{
							if(createPassword($newUser, $newPass))
								echo "There was an internal error. Please contact the administrator.<br>";
							else{
								logMessage("User $newUser has been created by administrator.");
								echo "User $newUser has been created.<br>";
							}
						}
					}
				}
			}
		}
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
		if(isset($_POST[deletedUsers])){
			$usersToDelete = $_POST[deletedUsers];
			$n = count($usersToDelete);
			for($i = 0; $i < $n; ++$i){
				$username = $usersToDelete[$i];
				$update = "DELETE FROM blog_users WHERE username='$username'";
				$result = doSQL($update);
				if(!$result)
					echo "There was an internal error. Please contact the administrator.<br>";
				else{
					logMessage("User $username has been deleted by administrator.");
					echo "Selected users have been deleted.<br>";
				}
			}
		}
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
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
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
	        if(isset($_POST[logout])){
			echo "true";
        	        session_destroy();
                	header('Location: ' . "login.php");
	                exit();
        	}
	}
?>

<?php
	function getAllUsers(){
		$query = "SELECT * FROM blog_users";
		$users = doSQL($query);
		return $users;
	}
?>

<?php
	function getUnactivatedUsers(){
		$query = "SELECT * FROM blog_users WHERE activated=false";
		$result = doSQL($query);
		if(!$result)
			return false;
		return $result;
	}
?>

<html>
	<head>
		<title> Admin Page </title>
 		<style type="text/css">
                .container {
                        display: grid;
			width: 200px;
			grid-template-columns: auto auto auto auto;
			grid-column-gap: 2em;
                }
		.container > div{
			padding: 1em;
		}
                </style>
	</head>
	<h1> Admin Page </h1>
	<hr>
	<body>
		<div class="container">
		<div>
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
				$result = getUnactivatedUsers();
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
		</div>
		<div>
			<h2>Create User</h2>
			<form action="adminPage.php" method="POST">
				Username: <input type="text" name="newUsername" /><br><br>
				Email: <input type="email" name="newEmail" /><br><br>
				Password: <input type="password" name="newPassword" />
				<br><br>
				<input type="submit" value="Submit" />
			</form>
		</div>
		<div>
			<h2>Delete Users</h2>
			<form action="adminPage.php" method="POST">
				<table border=1>
					<tr>
						<th>ID</th>
						<th>Username</th>
						<th>Email</th>
						<th>Delete</th>
					</tr>
					<?php
						$users = getAllUsers();
						$numRows = pg_num_rows($users);
						for($i = 0; $i < $numRows; $i++){
							$row = pg_fetch_row($users);
							echo "<tr>";
							echo "<td>".$row[0]."</td>";
							echo "<td>".$row[1]."</td>";
							echo "<td>".$row[2]."</td>";
							echo "<td>"."<input type='checkbox' name='deletedUsers[]' value='$row[1]' />"."</td>";
							echo "</tr>";
						}
					?>
				</table>
				<br>
				<input type="submit" value="Submit" />
			</form>
		</div>
		<div>
			<h2>Edit Details</h2>
			<table border=1>
				<tr>
					<th>ID</th>
					<th>Username</th>
					<th>Email</th>
					<th>Delete</th>
				</tr>
				<?php
					$users = getAllUsers();
					$numRows = pg_num_rows($users);
					for($i = 0; $i < $numRows; $i++){
						$row = pg_fetch_row($users);
						echo "<tr>";
						echo "<td>".$row[0]."</td>";
						echo "<td>".$row[1]."</td>";
						echo "<td>".$row[2]."</td>";
						echo "<td>"."<a href='editUser.php?user=$row[1]'>Edit</a>"."</td>";
						echo "</tr>";
					}
				?>
			</table>
		</div>
		</div>
		<form action="adminPage.php" method="POST">
			<input type="hidden" name="logout" value="1" />
			<input type="submit" value="Logout" />
		</form>
		<hr>
		<a href="login.php">User login</a><br>
	</body>
</html> 

