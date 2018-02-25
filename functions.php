<?php
	define("IMG_SRC", "/img/");
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
                        echo "There was an error processing the request. Please contact the administrator.<br>";
                        return false;
                }
                return $dbConn;
        }
?>

<?php
	function doSQL($stmnt){
		$dbConn = dbConnect();
		if($dbConn){
			$result = pg_query($stmnt);
			if(!$result)
				return false;
			return $result;
		}
		else
			return false;
	}
?>

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
        function logMessage($msg){
                $logFile = "/var/log/blogapp/userActions.log";
                $msg = date('Y-m-d H:i:s') . " - " . $msg . "\n";
                file_put_contents($logFile, $msg, FILE_APPEND | LOCK_EX);
        }
?>

<?php
        function validateEmail($email){
                $atPos = strpos($email, "@");
                if(!$atPos){
                        echo "Not a valid email.<br>";
                        return 1;
                }
                $uname = substr($email, 0, $atPos);
                $tld = strrchr($email, ".");
                $tldLen = strlen($tld);
                $tldLen++;
                $unameLen = strlen($uname);
                $emailLen = strlen($email);
                $domainLen = $emailLen - $unameLen;
                $domainLen = $domainLen - $tldLen;
                $domain = substr($email, $atPos+1, $domainLen);
                if(preg_match("/[^a-zA-Z0-9\.-]+/", $uname)){
                        echo "Not a valid email.<br>";
                        return 1;
                }
                if(preg_match("/[^a-zA-Z0-9-]+/", $domain)){
                        echo "Not a valid email.<br>";
                        return 1;
                }
                if(preg_match("/[^a-zA-Z\.]+/", $tld)){
                        echo "Not a valid email.<br>";
                        return 1;
                }
                return 0;
        }
?>

<?php
        function validateUsername($username){
                if(preg_match("/[^a-zA-Z]+/", $username)){
                        echo "Username must contain only letters.<br>";
                        return 1;
                }
                $dbConn = dbConnect();
                if($dbConn){
                        $query = "SELECT id FROM blog_users WHERE USERNAME='$username'";
                        $result = pg_query($query);
                        if(!$result){
                                echo "There was an error processing the request. Please contact the administrator.<br>";
                                return 1;
                        }
                        if(pg_num_rows($result) > 0){
                                echo "Username must be unique. <br>";
                                return 1;
                        }
                        return 0;
                }
        }
?>

<?php
        function validatePassword($password){
                $validPassword = 1;
                if(strlen($password) < 8){
                        $validPassword = 0;
                }
                if(!preg_match('/[a-z]+/', $password)){
                        $validPassword = 0;
                }
                if(!preg_match('/[A-Z]+/', $password)){
                        $validPassword = 0;
                }
                if(!preg_match('/[0-9]+/', $password)){
                        $validPassword = 0;
                }
                if(!$validPassword){
                        echo "Password must be 8 and 26 characters. <br>";
                        echo "Password must contain at least one lowercase letter. <br>";
                        echo "Password must contain at least one uppercase letter. <br>";
                        echo "Password must contain at least one number. <br>";
                        return 1;
                }
                return 0;
        }
?>

<?php
	function getKey($password){
		$hash = hash_pbkdf2("sha256", $password, md5(rand()), 5, 20);
		return $hash;
	}
?>

<?php
	function createPassword($username, $password){
		$salt = md5(rand());
		$key = hash_pbkdf2("sha256", $password, $salt, 5, 20);
		$dbConn = dbConnect();
		if($dbConn){
			$update = "UPDATE blog_users SET password='$key', salt='$salt' WHERE username = '$username'";
			$result = pg_query($update);
			if(!$result)
				return 1;
		}
		else
			return 1;
		return 0;
	}
?>

<?php
	function checkPassword($username, $password){
		$dbConn = dbConnect();
		if($dbConn){
			$query = "SELECT password, salt FROM blog_users WHERE username='$username'";
			$result = pg_query($query);
			if($result){
				if(pg_num_rows($result) == 0)
					return 1;
				else{
					$row = pg_fetch_row($result);
					$key = $row[0];
					$salt = $row[1];
					$hash = hash_pbkdf2("sha256", $password, $salt, 5, 20);
					if($key != $hash)
						return 1;
				}
			}
			else
				return 2;
		}
		else
			return 2;
		return 0;
	}
?>

<?php 
	function checkActivationStatus($username){
		$dbConn = dbConnect();
		if($dbConn){
			$query = "SELECT * FROM blog_users WHERE username='$username' AND activated=true";
			$result = pg_query($query);
			if(!$result)
				return 2;
			if(pg_num_rows($result) == 0)
				return 1;
		}
		else
			return 2;
		return 0;
	}
?>

<?php
	function checkForEmail($email){
		$dbConn = dbConnect();
		if($dbConn){
			$query = "SELECT * FROM blog_users WHERE email='$email'";
			$result = pg_query($query);
			if(!$result)
				return 2;
			if(pg_num_rows($result) == 0)
				return 1;
		}
		else
			return 2;
		return 0;
		
	}
?>

<?php
	function createNonce(){
		return md5(rand());
	}
?>

<?php
	function createReset($email, $code){
		$dbConn = dbConnect();
		if($dbConn){
			$query = "SELECT username FROM blog_users WHERE email='$email'";
			$result = pg_query($query);
			if(!$result)
				return 1;
			$row = pg_fetch_row($result);
			$username = $row[0];
			$insert = "INSERT INTO resets (username, code) VALUES ('$username', '$code')";
			$result = pg_query($insert);
			if(!$result)
				return 1;
		}
		else
			return 1;
		return 0;
	}
?>

<?php
	function getUsername($email){
		$dbConn = dbConnect();
		if($dbConn){
			$query = "SELECT username FROM blog_users WHERE email='$email'";
			$result = pg_query($query);
			if(!$result)
				return false;
			$row = pg_fetch_row($result);
			$username = $row[0];
			return $username;
		}
		else
			return false;
	}
?>

<?php
	function validateNonce($code){
		if(strlen($code) != 32)
                        return 1;
                if(preg_match("/[^a-fA-F0-9]/", $code))
                        return 1;
		return 0;
	}
?>

<?php
	function checkPasswordResetExists($username, $code){
		$dbConn = dbConnect();
		if($dbConn){
			$query = "SELECT * FROM resets WHERE username='$username' AND code='$code'";
			$result = pg_query($query);
			if(!$result)
				return 2;
			if(pg_num_rows($result) == 0)
				return 1;
			$update = "DELETE FROM resets WHERE username='$username' AND code='$code'";
			$result = pg_query($update);
			if(!$result)
				return 2;
			return 0;
		}
		else
			return 2;
	}
?>

<?php
	function verifyImage($img){
		$type = $img['type'];
		if(($type == "image/gif") ||
			($type == "image/jpeg") ||
			($type == "image/jpg") ||
			($type == "image/pjpeg") ||
			($type == "image/x-png") ||
			($type == "image/png")){
			return 0;
		}
		return 1;
	}
?>

<?php
	function clearMetadata($img){
		$imagick = new Imagick($img);
		$imagick->stripImage();
		$imagick->writeImage($img);
	}
?>

<?php
	function storeImage($username, $img){
		if(verifyImage($img))
			return 1;
//		clearMetadata($img);
		$path = $img['tmp_name'];
		$ext = pathinfo($img['name'], PATHINFO_EXTENSION);
		$nonce = createNonce();
		$imgPath = IMG_SRC;
		$newPath = "/var/www/html/blogs" . "$imgPath" . "$nonce" . "." . "$ext";
		$oldImg = null;
		$dbConn = dbConnect();
		if($dbConn){
			$query = "SELECT avatar, ext FROM avatars WHERE username='$username'";
			$result = pg_query($query);
			if(!$result){
				echo "Spot 1<br>";
				return 1;
			}
			if(pg_num_rows($result) == 1){
				$row = pg_fetch_row($result);
				//$oldImg = "/var/www/html/blogs" . "$imgPath" . "$row[0]" . "." . "$row[1]";
				//echo "$oldImg<br>";
				//echo file_exists("$oldImg") ? "true" : "false";
/*				if(!unlink("$oldImg")){
					echo "Spot 2<br>";
					return 1;
				}*/
				$update = "UPDATE avatars SET avatar='$nonce', ext='$ext' WHERE username='$username'";
				$result = pg_query($update);
				if(!$result){
					echo "Spot 3<br>";
					return 1;
				}
			}
			else{
				$insert = "INSERT INTO avatars (username, avatar, ext) VALUES ('$username', '$nonce', '$ext')";
				$result = pg_query($insert);
				if(!$result){
					echo "Spot 4<br>";
					return 1;
				}
			}
		}
		else
			return 1;
		if(!rename("$path", "$newPath")){
			echo "Spot 5<br>";
			return 1;
		}
		return 0;
	}
?>

<?php
	function getAvatar($username){
		$dbConn = dbConnect();
		if($dbConn){
			$query = "SELECT avatar, ext FROM avatars WHERE username='$username'";
			$result = pg_query($query);
			if(!$result)
				return false;
			$row = pg_fetch_row($result);
			return "/blogs" . IMG_SRC . $row[0] . "." . $row[1];
		}
		else
			return false;
	}
?>

<?php
	function changeUsername($username, $newUsername){
		if(!validateUsername($newUsername)){
			$dbConn = dbConnect();
			if($dbConn){
				$update = "UPDATE blog_users SET username='$newUsername' WHERE username='$username'";
				$result = pg_query($update);
				if(!$result)
					return 1;
				$update = "UPDATE avatars SET username='$newUsername' WHERE username='$username'";
				$result = pg_query($update);
				if(!$result)
					return 1;
				return 0;
			}
			return 1;
		}
		return 1;
	}
?>

<?php
	function changeEmail($username, $newEmail){
		if(!validateEmail($newEmail)){
			$dbConn = dbConnect();
			if($dbConn){
				$update = "UPDATE blog_users SET email='$newEmail' WHERE username='$username'";
				$result = pg_query($update);
				if(!$result)
					return 1;
				return 0;
			}
		}
		return 1;
	}
?>

<?php
	function incInvalidLogin($username){
		$dbConn = dbConnect();
		if($dbConn){
			$update = "UPDATE blog_users SET log_inv=log_inv+1 WHERE username='$username'";
			$result = pg_query($update);
			if(!$result)
				return 1;
			return 0;
		}
		return 1;
	}
?>

<?php
	function createAccountUnlock($username){
		$query = "SELECT id FROM unlocks WHERE username='$username'";
		$result = doSQL($query);
		if(!$result)
			return false;
		if(pg_num_rows($result) == 0){
			$nonce = createNonce();
			$url = "https://dev.ceveyp.com/blogs/unlock.php?username=$username&code=$nonce";
			$insert = "INSERT INTO unlocks (username, code) VALUES('$username', '$nonce')";
			if(doSQL($insert)){
				logMessage("Account unlock link for user $username: $url");
				return true;
			}
			else
				return false;
		}
		else
			return true;
	}
?>

<?php
	function checkAccountLockStatus($username){
		$query = "SELECT log_inv FROM blog_users WHERE username='$username'";
		$result = doSQL($query);
		if($result){
			$row = pg_fetch_row($result);
			$inv_n = $row[0];
			if($inv_n >= 5){
				if(createAccountUnlock($username) === false)
					return 2;
				return 1;
			}
			return 0;
		}
		return 2;
	}
?>
<?php
	function validateURL($url) {
		$domain = "|^[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,})/?$|";
		if (preg_match($domain, $url)) {
			return 0;
		}
		$regex = '#^([a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))$#';
		if (preg_match($regex, $url, $matches)) {
        		$parts = parse_url($url);
	        	if (!in_array($parts['scheme'], array( 'http', 'https' )))
        	        	return 1;
			if (!preg_match($domain, $parts['host']))
				return 1;
			return 0;
    		}
		return 1;
	}
?>

