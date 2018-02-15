<?php
	function stripInput($str){
	        $sqlKeyword = array("INSERT", "UPDATE", "DELETE", "SELECT", "GROUP BY", "ALTER", "JOIN", "UNION", "DROP");
                foreach($sqlKeyword as $i)
                        $str = preg_replace("/$i/i", "", $str);
                $str = strip_tags($str);
                $str = preg_replace("/#+/", "", $str);
                $str = preg_replace("/--+/", "", $str);
                $str = trim($str);
                return $str;
        }
?>

<?php
        function dbConnect(){
                $dbConf = file("/etc/db.conf");
                $dbUser = $dbConf[0];
                $dbName = $dbConf[1];
                $dbHost = $dbConf[2];
                $dbPass = $dbConf[3];
                $dbConn = pg_connect("host=$dbHost dbname=$dbName user=$dbUser password=$dbPass");
                if(!$dbConn){
                        echo "There was an error processing the request. Please contact the administrator. <br>";
                        return false;
                }
                return $dbConn;
        }
?>

<?php
	if($_REQUEST[done]){
		$username = $_REQUEST[username];
		$email = $_REQUEST[email];
		$password = $_REQUEST[password];
		$username = stripInput($username);
		$email = stripInput($email);
		$password = stripInput($password);
		$dbConn = dbConnect();
		if($dbConn){
			$query = "INSERT INTO blog_users (username, email, password) VALUES ('$username','$email','$password')";
			$query = pg_query($query);
			if(!$query)
				echo "There was an error processing the request. Please contact the administrator. <br>";		
			else{
				pg_close($dbConn);
			}
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
		<form action="<?php users/$_PHP_SELF ?>" method="POST">	
			<div class="container">
			Username: <input type="text" name="username" /><br>
			Password: <input type="password" name="password" /><br>
			<input type="hidden" name="done" value="1" />
			<br>
			</div>		
			<input type="submit" value="Login" />
		</form>
		<br>
		<hr>
	</body>
</html>



