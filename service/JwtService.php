<?php
require_once "ht/jwt.php";
require_once $pathToVendor."vendor/autoload.php";
require_once "data/User.php";
require_once "service/LogService.php";
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha512;

class JwtService
{
	private $signer;
	private $logService;

	public function __construct($logService)
	{
		$this->signer = new Sha512();
		$this->logService = $logService;
	}

	public function getToken($user)
	{
		return (new Builder())->setIssuer("https://".$_SERVER['SERVER_NAME'])
							  ->setIssuedAt(time())
							  ->setExpiration(time()+(60*60)) // 60min
							  ->set('u_id', $user->u_id)
							  ->set('u_name', $user->u_name)
							  ->set('u_adm', $user->u_adm)
							  ->sign($this->signer, Jwtpw::$jwtpw)
							  ->getToken()
							  ->__toString();
	}

	public function getUserFromJwt()
	{
		$isValid = false;
		try
		{
			//$jwtParam = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
			$jwtParam = $_SERVER["HTTP_AUTHORIZATION"];
			/*$jwtParam = null;
			foreach (apache_request_headers() as $header => $value)
				if ($header === "Authorization")
				{
					$jwtParam = $value;
					break;
				}
			//*/
			if($jwtParam === null)
				sendForbidden("jwt header not found");
			else
			{
				$jwt = (new Parser())->parse($jwtParam);
				// echo json_encode($jwt);
				
				$data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
				$data->setIssuer("https://".$_SERVER['SERVER_NAME']);

				$isValid = $jwt !== null;
				$isValid = $isValid && $jwt->validate($data);
				$isValid = $isValid && $jwt->verify($this->signer, Jwtpw::$jwtpw);
				
				$user = new User($jwt->getClaim('u_id'), $jwt->getClaim('u_name'), $jwt->getClaim('u_adm'));

				$isValid = $isValid && isset($user->u_id);
				$isValid = $isValid && isset($user->u_name);
				//$isValid = $isValid && isset($user->u_adm);
			}
		}
		catch (Exception $e)
		{
			$this->sendForbidden("jwt is not valid", $e);
		}
		if($isValid !== true)
			$this->sendForbidden("jwt is not valid");
		else
			return $user;
	}

	private function sendForbidden($errorMessage, $exception = NULL)
	{
		http_response_code(403);
		$exceptionMessage = is_null($exception) ? $errorMessage : $exception;
		$this->logService->logError($exceptionMessage);
		die($errorMessage);
	}
}
?>