<?php
	require "vendor/autoload.php";
	use Lcobucci\JWT\Parser;
	use Lcobucci\JWT\ValidationData;
	use Lcobucci\JWT\Signer\Hmac\Sha256;

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
			$jwt = (new Parser())->parse($jwtParam);
			// echo json_encode($jwt);
			
			$data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
			$data->setIssuer("https://".$_SERVER['SERVER_NAME']);

			$isValid = $jwt !== null;
			$isValid = $isValid && $jwt->validate($data);
			$isValid = $isValid && $jwt->verify(new Sha256(), 'testit');
			
			$userId = $jwt->getClaim('userId');
			$user = $jwt->getClaim('user');
			$admin = $jwt->getClaim('admin');
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