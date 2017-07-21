<?php
include("isValid.php");
include("include/connect.inc");

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
 
// create SQL based on HTTP method
$idField = isset($field) ? $field : substr($table, 0, 1)."_id";
switch ($method)
{
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
 
// die if SQL statement failed
if (!$result)
{
	http_response_code(400);
	die(mysqli_error($link));
}
 
// print results, insert id or affected row count
if ($method == 'GET')
{
	$countRows = mysqli_num_rows($result);
	//if ($countRows > 1) 
	echo '[';
	for ($i = 0; $i < $countRows; $i++)
		echo ($i > 0 ? ',' : '').json_encode(mysqli_fetch_object($result));
	//if ($countRows > 1) 
	echo ']';
}
elseif ($method == 'POST')
	echo mysqli_insert_id($link);
else
	echo mysqli_affected_rows($link);

mysqli_close($link);