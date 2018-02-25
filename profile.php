<?php
        include("functions.php");
	session_start();
?>

<?php
	$ip = $_SERVER['REMOTE_ADDR'];
	if(!$_SESSION[AUTH]){
		logMessage("Unauthenticated user attempting to access profile page - IP: $ip");
		$_SESSION['loginFormMessage'] = "Invalid login.<br>";
		header('Location: ' . "login.php");
		exit();
	}
?>

<?php
	if($_POST[logout]){
		session_destroy();
		header('Location: ' . "login.php");
		exit();
	}
?>

<?php
	if(isset($_FILES[newAvatar])){
		$avatar = $_FILES[newAvatar];
		$username = $_SESSION[username];
		if(!verifyImage($avatar)){
			if(storeImage($username, $avatar))
				echo "There was an internal error. Please contact the administrator.<br>";
			else{
				echo "Image uploaded.<br>";
				logMessage("User $username uploaded a new avatar.");
			}
		}
		else{
			echo "Invalid file type.<br>";
			logMessage("User $username attempting to upload an invalid file type.<br>");
		}
		unset($_FILES[newAvatar]);
	}
?>

<?php
	if(isset($_POST[newUsername])){
		$username = $_SESSION[username];
		$newUsername = $_POST[newUsername];
		$confirm = $_POST[confirmNewUsername];
		if($newUsername == $confirm){
			if(changeUsername($username, $newUsername))
				echo "There was an internal error. Please contact the administrator.<br>";
			else{
				$_SESSION[username] = $newUsername;
				logMessage("User $username has changed username to $newUsername.");
			}
		}
		else
			echo "Usernames must match.<br>";
	}
?>

<?php
	if(isset($_POST[newEmail])){
		$username = $_SESSION[username];
		$newEmail = $_POST[newEmail];
		$confirm = $_POST[confirmNewEmail];
		if($newEmail == $confirm){
			if(changeEmail($username, $newEmail))
				echo "There was an internal error. Please contact the administrator.<br>";
			else{
				logMessage("User $username has changed email.");
				echo "Email was successfully changed.<br>";
			}
		}
		else
			echo "Emails must match.<br>";
	}
?>

<?php
	if(isset($_POST[newPassword])){
		$username = $_SESSION[username];
		$password = $_POST[password];
		$newPassword = $_POST[newPassword];
		$confirmNewPassword = $_POST[confirmNewPassword];
		$ret = checkPassword($username, $password);
		if($ret == 2)
			echo "There was an internal error. Please contact the administrator.<br>";
		elseif($ret == 1){
			echo "Invalid password.<br>";
			logMessage("User $username failed reset password attempt.<br>");
		}
		else{
			if($newPassword == $confirmNewPassword){
				if(!createPassword($username, $newPassword)){
					logMessage("User $username has successfully changed password.<br>");
					echo "Password successfully changed.<br>";
				}
				else
					echo "There was an internal error. Please contact the administrator.<br>";
			}
			else
				echo "Passwords must match.<br>";
		}
	}
?>

<html>
	<head>
		<title> My Profile </title>
		<style type="text/css">
		.grid-container {
			display: grid;
		        width: 200px;
			grid-template-columns: auto auto auto auto;
			grid-column-gap: 2em;
		}
		.grid-container > div{
			padding: 1em;
		}
    		</style>
	</head>
	<?php
		$username = $_SESSION[username];
		echo "<h1> Hello $username </h1>";	
	?>
	<hr>
	<body>
		<div class="grid-container">
		<div>
		<?php
			$avatar = getAvatar($_SESSION[username]);
			if($avatar){
				echo "<img src='$avatar' height=100 width=100 />";
			}
		?>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST" enctype="multipart/form-data">
			<label id="avatarInput">Select an avatar to upload:</label>
			<input id="avatarInput" name="newAvatar" type="file" /><br>
			<input type="submit" value="Upload" />
		</form>
		<br>
		<a href="createPost.php">Create a new blog post</a>
		</div>
		<div>
		<fieldset>
		<legend>Change Username</legend>
		<br>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">
			New username: <input type="text" name="newUsername" /><br>
			Confirm: <input type="text" name="confirmNewUsername" /><br><br>
			<input type="submit" value="Submit" />
		</form></div>
		</fieldset>
		<div>
		<fieldset>
		<legend>Change Email</legend>
		<br>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">
			New email: <input type="email" name="newEmail" /><br>
			Confirmation: <input type="email" name="confirmNewEmail" /><br><br>
			<input type="submit" value="Submit" />
		</form></div>
		</fieldset>
		<div>
		<fieldset>
		<legend>Change Password</legend>
		<br>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">
			Current password: <input type="password" name="password" /><br>
			New password: <input type="password" name="newPassword" /><br>
			Confirm: <input type="password" name="confirmNewPassword" /><br><br>
			<input type="submit" value="Submit" />
		</form>
		</fieldset>
		</div>
		</div>
		<hr>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">
			<input type="submit" value="Logout" />
			<input type="hidden" name="logout" value="1" />
		</form>
	</body>
</html>



