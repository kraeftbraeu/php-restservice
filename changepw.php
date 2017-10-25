<?php
	require_once "ht/connect.php";
	require_once $pathToVendor."vendor/autoload.php";
	require_once "service/JwtService.php";
	require_once "service/LogService.php";
	require_once "service/SqlService.php";
	
	$logService = new LogService();
	$sqlService = new SqlService($link, $logService);
	$jwtService = new JwtService($logService);
	
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
	{
		$logService->logError("attack on changepw");
		exit;
	}
	
	$user = $jwtService->getUserFromJwt();

	// login via post form
	if(isset($_POST['newpw']))
		$newPw = $_POST['newpw'];
	else
	{
		$jsonData = json_decode(file_get_contents('php://input'), true);
		$newPw = $jsonData['pw'];
	}
	if(!isset($newPw) || $newPw === null)
	{
		$logService->logError("no new password found");
		mysqli_close($link);
		http_response_code(400);
		die("no new password found");
	}
	$newPwHash = password_hash($newPw, PASSWORD_DEFAULT);
	$sqlService->execute("UPDATE user SET u_pw = '".$newPwHash."' WHERE u_id = '".$user->u_id."'");
	
	echo json_encode(array(
		'token' => $jwtService->getToken($user)
	));
	
	mysqli_close($link);
?>