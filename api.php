<?php
	require "ht/connect.php";
	require "service/JwtService.php";
	
	$user = (new JwtService())->getUserFromJwt();

	// get the HTTP method, path and body of the request
	$method = $_SERVER['REQUEST_METHOD'];
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
	
	// allow cross-origin request
	header('Access-Control-Allow-Origin: *'); 

	// create SQL based on HTTP method
	$idField = isset($field) ? $field : substr($table, 0, 1)."_id";
	switch ($method)
	{
		case 'OPTIONS':
		{
			//header("Allow: GET,PUT,POST,DELETE,OPTIONS");
			header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS'); 
			header('Access-Control-Allow-Headers: Accept, Accept-CH, Accept-Charset, Accept-Datetime, Accept-Encoding, Accept-Ext, Accept-Features, Accept-Language, Accept-Params, Accept-Ranges, Access-Control-Allow-Credentials, Access-Control-Allow-Headers, Access-Control-Allow-Methods, Access-Control-Allow-Origin, Access-Control-Expose-Headers, Access-Control-Max-Age, Access-Control-Request-Headers, Access-Control-Request-Method, Age, Allow, Alternates, Authentication-Info, Authorization, C-Ext, C-Man, C-Opt, C-PEP, C-PEP-Info, CONNECT, Cache-Control, Compliance, Connection, Content-Base, Content-Disposition, Content-Encoding, Content-ID, Content-Language, Content-Length, Content-Location, Content-MD5, Content-Range, Content-Script-Type, Content-Security-Policy, Content-Style-Type, Content-Transfer-Encoding, Content-Type, Content-Version, Cookie, Cost, DAV, DELETE, DNT, DPR, Date, Default-Style, Delta-Base, Depth, Derived-From, Destination, Differential-ID, Digest, ETag, Expect, Expires, Ext, From, GET, GetProfile, HEAD, HTTP-date, Host, IM, If, If-Match, If-Modified-Since, If-None-Match, If-Range, If-Unmodified-Since, Keep-Alive, Label, Last-Event-ID, Last-Modified, Link, Location, Lock-Token, MIME-Version, Man, Max-Forwards, Media-Range, Message-ID, Meter, Negotiate, Non-Compliance, OPTION, OPTIONS, OWS, Opt, Optional, Ordering-Type, Origin, Overwrite, P3P, PEP, PICS-Label, POST, PUT, Pep-Info, Permanent, Position, Pragma, ProfileObject, Protocol, Protocol-Query, Protocol-Request, Proxy-Authenticate, Proxy-Authentication-Info, Proxy-Authorization, Proxy-Features, Proxy-Instruction, Public, RWS, Range, Referer, Refresh, Resolution-Hint, Resolver-Location, Retry-After, Safe, Sec-Websocket-Extensions, Sec-Websocket-Key, Sec-Websocket-Origin, Sec-Websocket-Protocol, Sec-Websocket-Version, Security-Scheme, Server, Set-Cookie, Set-Cookie2, SetProfile, SoapAction, Status, Status-URI, Strict-Transport-Security, SubOK, Subst, Surrogate-Capability, Surrogate-Control, TCN, TE, TRACE, Timeout, Title, Trailer, Transfer-Encoding, UA-Color, UA-Media, UA-Pixels, UA-Resolution, UA-Windowpixels, URI, Upgrade, User-Agent, Variant-Vary, Vary, Version, Via, Viewport-Width, WWW-Authenticate, Want-Digest, Warning, Width, X-Content-Duration, X-Content-Security-Policy, X-Content-Type-Options, X-CustomHeader, X-DNSPrefetch-Control, X-Forwarded-For, X-Forwarded-Port, X-Forwarded-Proto, X-Frame-Options, X-Modified, X-OTHER, X-PING, X-PINGOTHER, X-Powered-By, X-Requested-With'); 
			exit;
			break;
		}
		case 'GET':
			$sql = "select * from `$table`".($key ? " WHERE $idField=$key" : ''); break;
		case 'PUT':
			$sql = "update `$table` set $set where $idField=$key"; break;
		case 'POST':
			$sql = "insert into `$table` set $set"; break;
		case 'DELETE':
			$sql = "delete from `$table` where $idField=$key"; break;
	}

	// excecute SQL statement
	$result = mysqli_query($link, $sql);
	//echo "<p>".$sql."</p>\r\n<p>".$result."</p>\r\n";
	
	$dt = new DateTime();
	$dt = $dt->format('Y-m-d H:i:s');
	file_put_contents("log/rest.log", "\r\n".$dt." ".$sql, FILE_APPEND | LOCK_EX);
	
	// die if SQL statement failed
	if (!$result)
	{
		$error = mysqli_error($link);
		mysqli_close($link);
		http_response_code(400);
		die($error);
	}

	// print results, insert id or affected row count
	if ($method == 'GET')
	{
		$rows = array();
		while($r = mysqli_fetch_assoc($result))
			$rows[] = $r;
		echo json_encode($rows);
	}
	elseif ($method == 'POST')
		echo mysqli_insert_id($link);
	else
		echo mysqli_affected_rows($link);

	mysqli_close($link);
?>