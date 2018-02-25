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

<script type="text/javascript">
	function newHyperLink(){
		var url=prompt("Please enter a URL: ","")
		var text=prompt("Please enter some text: ","")
		if (url!=null && text!=""){
			if (Age != null && Age != ""){
				document.write("Hello " + name + "! Your age is "+ Age);
			}
		}
	}
</script>

<html>
	<head>
		<title> Create Post </title>
		<style type="text/css">
		.grid-container {
			display: grid;
			grid-template-columns: 70% 30%;
			grid-column-gap: 1em;
		}
		.grid-container > div{
			padding: 1em;
			background: #eee;
		}
    		</style>
	</head>
	<?php
		$username = $_SESSION[username];
		echo "<h1> Hello $username - Create a new blog post</h1>";
	?>
	<hr>
	<body>
		<div class="grid-container">
			<div>
				<h3>Enter blog text:<h3>
				<p><button onclick="newHyperLink()">Add link</button><p>
				<form action="createPost.php" method="POST" enctype="multipart/form-data">
					<textarea name="post" rows="20" cols="120" ></textarea>
					<p><input type="submit" value="Submit" /><p>
			</div>
			<div>
					<label id="avatarInput">Select an image to upload:</label>
					<input id="avatarInput" name="newBlogImg" type="file" /><br>
					<input type="submit" value="Upload" />
				</form>
			</div>
		</div>
		<hr>
		<form action="<?php blogs/$_PHP_SELF ?>" method="POST">
			<input type="submit" value="Logout" />
			<input type="hidden" name="logout" value="1" />
		</form>
	</body>
</html>



