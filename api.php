<?php
	require_once "ht/connect.php";
	require_once "service/JwtService.php";
	require_once "service/LogService.php";
	require_once "service/SqlService.php";

	// allow cross-origin request
	header('Access-Control-Allow-Origin: *'); 
	
	$method = $_SERVER['REQUEST_METHOD'];
	if($method === 'OPTIONS')
	{
		header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS'); 
		header('Access-Control-Allow-Headers: Accept, Accept-CH, Accept-Charset, Accept-Datetime, Accept-Encoding, Accept-Ext, Accept-Features, Accept-Language, Accept-Params, Accept-Ranges, Access-Control-Allow-Credentials, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Access-Control-Allow-Origin, Access-Control-Expose-Headers, Access-Control-Max-Age, Access-Control-Request-Headers, Access-Control-Request-Method, Age, Allow, Alternates, Authentication-Info, Authorization, C-Ext, C-Man, C-Opt, C-PEP, C-PEP-Info, CONNECT, Cache-Control, Compliance, Connection, Content-Base, Content-Disposition, Content-Encoding, Content-ID, Content-Language, Content-Length, Content-Location, Content-MD5, Content-Range, Content-Script-Type, Content-Security-Policy, Content-Style-Type, Content-Transfer-Encoding, Content-Type, Content-Version, Cookie, Cost, DAV, DELETE, DNT, DPR, Date, Default-Style, Delta-Base, Depth, Derived-From, Destination, Differential-ID, Digest, ETag, Expect, Expires, Ext, From, GET, GetProfile, HEAD, HTTP-date, Host, IM, If, If-Match, If-Modified-Since, If-None-Match, If-Range, If-Unmodified-Since, Keep-Alive, Label, Last-Event-ID, Last-Modified, Link, Location, Lock-Token, MIME-Version, Man, Max-Forwards, Media-Range, Message-ID, Meter, Negotiate, Non-Compliance, OPTION, OPTIONS, OWS, Opt, Optional, Ordering-Type, Origin, Overwrite, P3P, PEP, PICS-Label, POST, PUT, Pep-Info, Permanent, Position, Pragma, ProfileObject, Protocol, Protocol-Query, Protocol-Request, Proxy-Authenticate, Proxy-Authentication-Info, Proxy-Authorization, Proxy-Features, Proxy-Instruction, Public, RWS, Range, Referer, Refresh, Resolution-Hint, Resolver-Location, Retry-After, Safe, Sec-Websocket-Extensions, Sec-Websocket-Key, Sec-Websocket-Origin, Sec-Websocket-Protocol, Sec-Websocket-Version, Security-Scheme, Server, Set-Cookie, Set-Cookie2, SetProfile, SoapAction, Status, Status-URI, Strict-Transport-Security, SubOK, Subst, Surrogate-Capability, Surrogate-Control, TCN, TE, TRACE, Timeout, Title, Trailer, Transfer-Encoding, UA-Color, UA-Media, UA-Pixels, UA-Resolution, UA-Windowpixels, URI, Upgrade, User-Agent, Variant-Vary, Vary, Version, Via, Viewport-Width, WWW-Authenticate, Want-Digest, Warning, Width, X-Content-Duration, X-Content-Security-Policy, X-Content-Type-Options, X-CustomHeader, X-DNSPrefetch-Control, X-Forwarded-For, X-Forwarded-Port, X-Forwarded-Proto, X-Frame-Options, X-Modified, X-OTHER, X-PING, X-PINGOTHER, X-Powered-By, X-Requested-With'); 
		exit;
	}

	$logService = new LogService();
	$sqlService = new SqlService($link, $logService);
	$jwtService = new JwtService($logService);
	$user = $jwtService->getUserFromJwt();

	// get the HTTP method, path and body of the request
	$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
	$input = json_decode(file_get_contents('php://input'), true);

	$param1 = array_shift($request);
	$param2 = array_shift($request);
	$param3 = array_shift($request);
	// retrieve the table and key from the path
	$table = preg_replace('/[^a-z0-9_]+/i', '', $param1);
	if(!isset($param3))
		$key = $param2;
	else
	{
		$field = preg_replace('/[^a-z0-9_]+/i', '', $param2);
		$key = $param3;
	}
	if(isset($key))
		if(is_numeric($key))
			$key = $key + 0;
		else
			$key = "'$key'";

	// escape the columns and values from the input object
	if(!empty($input))
	{
		$columns = preg_replace('/[^a-z0-9_]+/i', '', array_keys($input));
		$values = array_map(function ($value) use ($link)
		{
			if ($value === null)
				return null;
			return mysqli_real_escape_string($link, (string) $value);
		},array_values($input));
		
		// build the SET part of the SQL command
		$set = '';
		for ($i = 0; $i < count($columns); $i++)
		{
			$set.=($i > 0 ? ',' : '').'`'.$columns[$i].'`=';
			$set.=($values[$i] === null ? 'NULL' : '"'.$values[$i].'"');
		}
	}
	
	// create SQL based on HTTP method
	$idField = isset($field) ? $field : substr($table, 0, 1)."_id";
	switch ($method)
	{
		case 'GET': {
			// TODO: Felder generisch sammeln
			if($table === "user")
				$fields = "u_id, u_name, u_adm";
			else
				$fields = "*";
			$sqlResult = $sqlService->execute("SELECT $fields FROM $table".($key ? " WHERE $idField=$key" : ''));
			$result = array();
			while($r = mysqli_fetch_assoc($sqlResult))
				$result[] = $r;
			break;
		}
		case 'PUT': {
			$sqlService->execute("UPDATE $table SET $set WHERE $idField=$key");
			$result = $key;
			break;
		}
		case 'POST': {
			$sqlService->execute("INSERT INTO $table SET $set");
			$result = mysqli_insert_id($link);
			break;
		}
		case 'DELETE': {
			$sqlService->execute("DELETE FROM $table WHERE $idField=$key");
			$result = mysqli_affected_rows($link);
			break;
		}
	}

	// print results, insert id or affected row count
	echo json_encode(array(
		'token' => $jwtService->getToken($user),
		'content' => $result
	));

	mysqli_close($link);
?>