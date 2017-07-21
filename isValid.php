<?php
	include("ht/jwt_helper.php");

	$isValid = false;
	try
	{
		$jwtParam = null;
		$headers = apache_request_headers();
		foreach ($headers as $header => $value)
			if ($header === "jwt")
			{
				$jwtParam = $value;
				break;
			}
		if($jwtParam === null)
			sendForbidden();
		else
		{
			$jwt = JWT::decode($jwtParam, $jwtKey);
			// echo json_encode($jwt);
			
			$isValid = $jwt !== null;
			$isValid = $isValid && $jwt->iss === "https://" . $_SERVER['SERVER_NAME'];
			$isValid = $isValid && time() - $jwt->iat < 60*60*24; // Token ist 1 Tag lang gültig
			
			$userId = $jwt->userId;
			$user = $jwt->user;
			$admin = $jwt->admin;
			$isAdmin = $admin === "Y";

			$isValid = $isValid && isset($userId);
			$isValid = $isValid && isset($user);
			//$isValid = $isValid && isset($admin);
		}
	}
	catch (Exception $e)
	{
	}
	if($isValid !== true)
		sendForbidden();

function sendForbidden()
{
	http_response_code(403);
	echo "HTTP Error 403: Forbidden";
	exit;
}
?>