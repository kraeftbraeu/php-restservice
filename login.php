<?php
	require "ht/connect.php";
	require "vendor/autoload.php";
	use Lcobucci\JWT\Builder;
	use Lcobucci\JWT\Signer\Hmac\Sha256;
	
	// allow cross-origin request
	header('Access-Control-Allow-Origin: *'); 

	$method = $_SERVER['REQUEST_METHOD'];
	if($method === 'OPTIONS')
	{
		header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS'); 
		header('Access-Control-Allow-Headers: Accept, Accept-CH, Accept-Charset, Accept-Datetime, Accept-Encoding, Accept-Ext, Accept-Features, Accept-Language, Accept-Params, Accept-Ranges, Access-Control-Allow-Credentials, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Access-Control-Allow-Origin, Access-Control-Expose-Headers, Access-Control-Max-Age, Access-Control-Request-Headers, Access-Control-Request-Method, Age, Allow, Alternates, Authentication-Info, Authorization, C-Ext, C-Man, C-Opt, C-PEP, C-PEP-Info, CONNECT, Cache-Control, Compliance, Connection, Content-Base, Content-Disposition, Content-Encoding, Content-ID, Content-Language, Content-Length, Content-Location, Content-MD5, Content-Range, Content-Script-Type, Content-Security-Policy, Content-Style-Type, Content-Transfer-Encoding, Content-Type, Content-Version, Cookie, Cost, DAV, DELETE, DNT, DPR, Date, Default-Style, Delta-Base, Depth, Derived-From, Destination, Differential-ID, Digest, ETag, Expect, Expires, Ext, From, GET, GetProfile, HEAD, HTTP-date, Host, IM, If, If-Match, If-Modified-Since, If-None-Match, If-Range, If-Unmodified-Since, Keep-Alive, Label, Last-Event-ID, Last-Modified, Link, Location, Lock-Token, MIME-Version, Man, Max-Forwards, Media-Range, Message-ID, Meter, Negotiate, Non-Compliance, OPTION, OPTIONS, OWS, Opt, Optional, Ordering-Type, Origin, Overwrite, P3P, PEP, PICS-Label, POST, PUT, Pep-Info, Permanent, Position, Pragma, ProfileObject, Protocol, Protocol-Query, Protocol-Request, Proxy-Authenticate, Proxy-Authentication-Info, Proxy-Authorization, Proxy-Features, Proxy-Instruction, Public, RWS, Range, Referer, Refresh, Resolution-Hint, Resolver-Location, Retry-After, Safe, Sec-Websocket-Extensions, Sec-Websocket-Key, Sec-Websocket-Origin, Sec-Websocket-Protocol, Sec-Websocket-Version, Security-Scheme, Server, Set-Cookie, Set-Cookie2, SetProfile, SoapAction, Status, Status-URI, Strict-Transport-Security, SubOK, Subst, Surrogate-Capability, Surrogate-Control, TCN, TE, TRACE, Timeout, Title, Trailer, Transfer-Encoding, UA-Color, UA-Media, UA-Pixels, UA-Resolution, UA-Windowpixels, URI, Upgrade, User-Agent, Variant-Vary, Vary, Version, Via, Viewport-Width, WWW-Authenticate, Want-Digest, Warning, Width, X-Content-Duration, X-Content-Security-Policy, X-Content-Type-Options, X-CustomHeader, X-DNSPrefetch-Control, X-Forwarded-For, X-Forwarded-Port, X-Forwarded-Proto, X-Frame-Options, X-Modified, X-OTHER, X-PING, X-PINGOTHER, X-Powered-By, X-Requested-With'); 
		exit;
	}
	else if($method !== 'POST')
		exit;
	
	// login via post form
	if(isset($_POST['user']) && isset($_POST['pw']))
	{
		$loginUser = mysql_real_escape_string($_POST['user']);
		$loginPw = $_POST['pw'];
	}
	else
	{
		$jsonData = json_decode(file_get_contents('php://input'), true);
		$loginUser = $jsonData['user'];
		$loginPw = $jsonData['pw'];
	}
	
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
	
	echo json_encode(array(
		 'token' =>
		 (new Builder())->setIssuer("https://".$_SERVER['SERVER_NAME'])
						->setIssuedAt(time())
						->setExpiration(time()+(60*60*24))
						->set('u_id', $userId)
						->set('u_name', $userName)
						->set('u_adm', $userObject->u_adm)
						->sign(new Sha256(), 'testit')
						->getToken()
						->__toString()
	));
	
	mysqli_close($link);
?>