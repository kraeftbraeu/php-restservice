<?php
	require "ht/jwt.php";
	require "vendor/autoload.php";
	require "data/User.php";
	use Lcobucci\JWT\Parser;
	use Lcobucci\JWT\ValidationData;
	use Lcobucci\JWT\Signer\Hmac\Sha512;

	class JwtService
	{
		public function getUserFromJwt()
		{
			$isValid = false;
			try
			{
				$jwtParam = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
				//$jwtParam = $_SERVER["HTTP_AUTHORIZATION"];
				/*$jwtParam = null;
				foreach (apache_request_headers() as $header => $value)
					if ($header === "Authorization")
					{
						$jwtParam = $value;
						break;
					}
				//*/
				if($jwtParam === null)
					sendForbidden("jwt header not found")
				else
				{
					$jwt = (new Parser())->parse($jwtParam);
					// echo json_encode($jwt);
					
					$data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
					$data->setIssuer("https://".$_SERVER['SERVER_NAME']);

					$isValid = $jwt !== null;
					$isValid = $isValid && $jwt->validate($data);
					$isValid = $isValid && $jwt->verify(new Sha512(), Jwtpw::$jwtpw);
					
					$user = new User($jwt->getClaim('u_id'), $jwt->getClaim('u_name'), $jwt->getClaim('u_adm'));
					//$isAdmin = $user->admin === "Y";

					$isValid = $isValid && isset($user->id);
					$isValid = $isValid && isset($user->name);
					//$isValid = $isValid && isset($user->admin);
				}
			}
			catch (Exception $e)
			{
				$this->sendForbidden($e);
			}
			if($isValid !== true)
				$this->sendForbidden("jwt is not valid");
			else
				return $user;
		}

		private function sendForbidden($errorMessage)
		{
			http_response_code(403);
			die($errorMessage);
		}
	}
?>