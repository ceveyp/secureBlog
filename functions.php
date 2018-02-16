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
                        echo "Too short";
                }
                if(!preg_match('/[a-z]+/', $password)){
                        $validPassword = 0;
                        echo "No lowercase";
                }
                if(!preg_match('/[A-Z]+/', $password)){
                        $validPassword = 0;
                        echo "No uppercase";
                }
                if(!preg_match('/[0-9]+/', $password)){
                        $validPassword = 0;
                        echo "No numbers";
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


