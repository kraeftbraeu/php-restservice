<?php
	require "vendor/autoload.php";
	require "data/User.php";
	use Lcobucci\JWT\Parser;
	use Lcobucci\JWT\ValidationData;
	use Lcobucci\JWT\Signer\Hmac\Sha256;

	class JwtService
	{
		public function getUserFromJwt()
		{
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
				$jwtParam = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvbG9jYWxob3N0IiwiaWF0IjoxNTAxNDkwNDk3LCJleHAiOjE1MDE1NzY4OTcsInVfaWQiOiIxIiwidV9uYW1lIjoiTWFudWVsIiwidV9hZG0iOiJZIn0.XVS6oAKdvS1bgr28kHCFENVFepAJwN0T0P64xBCBxkM";
				if($jwtParam === null)
					$this->sendForbidden("jwt is not valid");
				else
				{
					$jwt = (new Parser())->parse($jwtParam);
					// echo json_encode($jwt);
					
					$data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
					$data->setIssuer("https://".$_SERVER['SERVER_NAME']);

					$isValid = $jwt !== null;
					$isValid = $isValid && $jwt->validate($data);
					$isValid = $isValid && $jwt->verify(new Sha256(), 'testit');
					
					$user = new User($jwt->getClaim('u_id'), $jwt->getClaim('u_name'), $jwt->getClaim('u_adm'));
					//$isAdmin = $user->admin === "Y";

					$isValid = $isValid && isset($user->id);
					$isValid = $isValid && isset($user->name);
					//$isValid = $isValid && isset($user->admin);
				}
			}
			catch (Exception $e)
			{
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