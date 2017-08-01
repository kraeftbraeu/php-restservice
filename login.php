<?php
	require "ht/connect.php";
	require "vendor/autoload.php";
	use Lcobucci\JWT\Builder;
	use Lcobucci\JWT\Signer\Hmac\Sha256;
	
	// login via post form
	$loginUser = mysql_real_escape_string($_GET['user']);
	$loginPw = $_GET['pw'];
	
	$queryResult = mysqli_query($link, "SELECT * FROM user WHERE u_name = '".$loginUser."'");
	if (!$queryResult)
	{
		$error = mysqli_error($link);
		mysqli_close($link);
		http_response_code(400);
		die($error);
	}
	if (mysqli_num_rows($queryResult) != 1)
	{
		mysqli_close($link);
		http_response_code(401);
		die("no unique user found");
	}
	$userObject = mysqli_fetch_object($queryResult);
	$dbPwHash = $userObject->u_pw;
	if (!password_verify($loginPw, $dbPwHash))
	{
		mysqli_close($link);
		http_response_code(401);
		die("no unique user found");
	}
	$userId = $userObject->u_id;
	$userName = $userObject->u_name;
	if($userId < 0 || empty($userName))
	{
		mysqli_close($link);
		http_response_code(401);
		die("no unique user found");
	}
	// login was successful

	if(password_needs_rehash($dbPwHash, PASSWORD_DEFAULT))
	{
		$newPwHash = password_hash($loginPw, PASSWORD_DEFAULT);
		mysqli_query($link, "UPDATE user SET u_pw = '".$newPwHash."' WHERE u_id = '".$userId."'");
	}
	mysqli_query($link, "UPDATE user SET u_log = '".date('Y-m-d H:i:s')."' WHERE u_id = '".$userId."'");
	
	echo (new Builder())->setIssuer("https://".$_SERVER['SERVER_NAME'])
						->setIssuedAt(time())
						->setExpiration(time()+(60*60*24))
						->set('u_id', $userId)
						->set('u_name', $userName)
						->set('u_adm', $userObject->u_adm)
						->sign(new Sha256(), 'testit')
						->getToken();
	
	mysqli_close($link);
?>