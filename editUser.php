<?php
        include("functions.php");
	session_start();
?>

<?php
	$ip = $_SERVER['REMOTE_ADDR'];
	if(!$_SESSION[ADMINAUTH]){
		logMessage("Unauthenticated user attempting to access user edit page - IP: $ip");
		$_SESSION['loginFormMessage'] = "Invalid login.<br>";
		header('Location: ' . "login.php");
		exit();
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
		if(!isset($_SESSION[editUser])){
			if(isset($_GET[user])){
				$_SESSION[editUser] = $_GET[user];
			}
			else{
				header('Location: ' . "adminPage.php");
				exit();
			}
		}
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
		if($_POST[logout]){
			session_destroy();
			header('Location: ' . "login.php");
			exit();
		}
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
		if(isset($_FILES[newAvatar])){
			$avatar = $_FILES[newAvatar];
			$username = $_SESSION[editUser];
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
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
		if(isset($_POST[newUsername])){
			$username = $_SESSION[editUser];
			$newUsername = $_POST[newUsername];
			$confirm = $_POST[confirmNewUsername];
			if($newUsername == $confirm){
				if(changeUsername($username, $newUsername))
					echo "There was an internal error. Please contact the administrator.<br>";
				else{
					$_SESSION[username] = $newUsername;
					logMessage("User $username has changed username to $newUsername.");
					echo "Username changed to $newUsername.<br>";
					$_SESSION[editUser] = $newUsername;
				}
			}
			else
				echo "Usernames must match.<br>";
		}
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
		if(isset($_POST[newEmail])){
			$username = $_SESSION[editUser];
			$newEmail = $_POST[newEmail];
			$confirm = $_POST[confirmNewEmail];
			if($newEmail == $confirm){
				if(changeEmail($username, $newEmail))
					echo "There was an internal error. Please contact the administrator.<br>";
				else{
					logMessage("Administrator has changed the email for user $username.");
					echo "Email was successfully changed.<br>";
				}
			}
			else
				echo "Emails must match.<br>";
		}
	}
?>

<?php
	if($_SESSION[ADMINAUTH]){
		if(isset($_POST[newPassword])){
			$username = $_SESSION[editUser];
			$newPassword = $_POST[newPassword];
			$confirmNewPassword = $_POST[confirmNewPassword];
			if($newPassword == $confirmNewPassword){
				if(!createPassword($username, $newPassword)){
					logMessage("Administrator has changed password for user $username.<br>");
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
		<title> Edit User Profile </title>
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
		$username = $_SESSION[editUser];
		echo "<h1> Details for User $username </h1>";	
	?>
	<hr>
	<body>
		<div class="grid-container">
		<div>
		<?php
			$avatar = getAvatar($_SESSION[editUser]);
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
		<a href="adminPage.php">Back to admin page</a>
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



