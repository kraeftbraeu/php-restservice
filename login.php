<?php
	include("include/connect.inc");
	include("ht/jwt_helper.php");
	
	// login via post form
	$loginUser = mysql_real_escape_string($_POST['user']);
	$loginPw = $_POST['pw'];
	
	$sql = "select * from `user` WHERE u_name = '".$loginUser."'";
	$result = mysqli_query($link, $sql);
	if (!$result)
	{
		http_response_code(400);
		echo "HTTP Error 400: Bad Request";
		die(mysqli_error($link));
		exit;
	}
	if (mysqli_num_rows($result) != 1)
	{
		http_response_code(401);
		echo "HTTP Error 401: Unauthorized";
		exit;
	}
	else
	{
		$userObject = mysqli_fetch_object($result);
		/*$userObject = (object) array("u_id" => 7,
									 "u_name" => "Manuel",
									 "u_pw" => password_hash("m", PASSWORD_DEFAULT),
									 "u_log" => (new DateTime())->format('d.m.Y H:i'),
									 "u_adm" => "Y");*/
	
		$dbPwHash = $userObject->u_pw;
		if (!password_verify($loginPw, $dbPwHash))
		{
			http_response_code(401);
			echo "HTTP Error 401: Unauthorized";
			exit;
		}
		else
		{
			if(password_needs_rehash($dbPwHash, PASSWORD_DEFAULT))
			{
				$newPwHash = password_hash($loginPw, PASSWORD_DEFAULT);
				$sql = @mysql_query("UPDATE user SET u_pw = '".$newPwHash."' WHERE u_id = '".$userId."'");
			}
			
			$userId = $userObject->u_id;
			$userName = $userObject->u_name;
			$isLoggedIn = $userId >= 0 && !empty($userName);
			if($isLoggedIn)
			{
				$sql = @mysql_query("UPDATE user SET u_log = '".(new DateTime())->format('d.m.Y H:i')."' WHERE u_id = '".$userId."'");
				
				$token = array( "iss" => "https://" . $_SERVER['SERVER_NAME'],
								"iat" => time(),
								"userId" => $userId,
								"user" => $userName,
								"admin" => $userObject->u_adm
								);
				echo JWT::encode($token, $jwtKey);
			}
		}
	}
	
	mysqli_close($link);
?>