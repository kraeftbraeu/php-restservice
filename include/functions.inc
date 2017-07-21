<?php
	/***************/
	/*  DB helper  */
	/***************/
	
	class Field
	{
		public $name, $type, $mayBeNull;
		public function __construct($name, $type, $mayBeNull) { $this -> name = $name; $this -> type = $type; $this -> mayBeNull = $mayBeNull; }
	}
	
	class Table
	{
		public $name, $idField, $fields;
		public function __construct($name, $fields) { $this -> name = $name; $this -> fields = $fields; $this -> idField = $fields[0]; }
	}
	
	$userFieldId = new Field("u_id", "INT", false);
	$userFieldName = new Field("u_name", "VARCHAR(50)", false);
	$userFieldPassword = new Field("u_pw", "VARCHAR(255)", false);
	$userFieldLastLog = new Field("u_log", "DATETIME", true);
	$userFieldAdmin = new Field("u_adm", "VARCHAR(1)", true);
	
	$filterFieldId = new Field("f_id", "INT", false);
	$filterFieldGiver = new Field("f_giver", "INT", false);
	$filterFieldWisher = new Field("f_wisher", "INT", false);

	$wishFieldId = new Field("w_id", "INT", false);
	$wishFieldUser = new Field("w_user", "INT", false);
	$wishFieldDescr = new Field("w_descr", "VARCHAR(200)", false);
	$wishFieldLink = new Field("w_link", "VARCHAR(200)", true);

	$presentFieldId = new Field("p_id", "INT", false);
	$presentFieldWisher = new Field("p_wisher", "INT", false);
	$presentFieldGiver = new Field("p_giver", "INT", false);
	$presentFieldWish = new Field("p_wish", "INT", true);
	$presentFieldDescr = new Field("p_pdescr", "VARCHAR(200)", true);
	$presentFieldLink = new Field("p_plink", "VARCHAR(200)", true);

	$userTable = new Table("user", array($userFieldId, $userFieldName, $userFieldPassword, $userFieldLastLog, $userFieldAdmin));
	$filterTable = new Table("filter", array($filterFieldId, $filterFieldGiver, $filterFieldWisher));
	$wishTable = new Table("wish", array($wishFieldId, $wishFieldUser, $wishFieldDescr, $wishFieldLink));
	$presentTable = new Table("present", array($presentFieldId, $presentFieldWisher, $presentFieldGiver, $presentFieldWish, $presentFieldDescr, $presentFieldLink));
	
	function sql($query)
	{
  		if($GLOBALS["logDb"] == true)
  			echo "<p>SQL: ".$query."</p>";
		@mysql_query($query);
		if (mysql_errno() != 0)
			return mysql_error();
		else
			return null;
	}

	function sqlInsert($table, $fieldsArray)
	{
		$keys = array_keys($fieldsArray);
		$query = "INSERT INTO ".$table->name." VALUES (";
		$count = count($keys);
		for ($i = 0; $i < $count; $i++)
		{
			$key = $keys[$i];
			$value = $fieldsArray[$key];
			if($value == null)
				$value = "NULL";
			else if(is_string($value))
				$value = "'".mysql_real_escape_string($value)."'";
			$query .= $value;
			if($i < $count-1)
				$query .= ", ";
			else $query .= ")";
		}
		return sql($query);
	}

	function sqlUpdate($table, $fieldsArray, $id)
	{
		$keys = array_keys($fieldsArray);
		$query = "UPDATE ".$table->name." SET ";
		$count = count($keys);
		for ($i = 0; $i < $count; $i++)
		{
			$key = $keys[$i];
			$value = $fieldsArray[$key];
			$query .= mysql_real_escape_string($key)." = ";
			if($value == null)
				$query .= "NULL";
			else
				$query .= "'".mysql_real_escape_string($value)."'";
			if($i < $count - 1)
				$query .= ", ";
		}
		$query .= " WHERE ".$table->idField->name." = ".mysql_real_escape_string($id);
		return sql($query);
	}
	
	function sqlDelete($table, $id)
	{
		$query = "DELETE FROM ".$table->name." WHERE ".$table->idField->name." = ".mysql_real_escape_string($id);
		return sql($query);
	}
	
	function sqlSelect($query)
	{
  		if($GLOBALS["logDb"] == true)
  			echo "<p>SQL: ".$query."</p>";
		$fetch = @mysql_query($query);
		if (mysql_errno() != 0)
		{
			echo "<p>".mysql_error()."</p>";
			return null;
		}
		else if (mysql_num_rows($fetch) == 0)
			return null;
		else if($fetch == null)
			return null;
		else
		{
			$result = array();
			$i = 0;
			while ($row = mysql_fetch_row($fetch))
				$result[$i++] = $row;
			return $result;
		}
	}

	function getFetchWithWhere($table, $fieldsArray, $where, $unique, $orderBy)
	{
		$query = "SELECT ";
		$count = count($fieldsArray);
		for ($i = 0; $i < $count; $i++)
		{
		$query .= mysql_real_escape_string($fieldsArray[$i]);
		if($i < $count-1)
			$query .= ", ";
		}
		$query .= " FROM ".mysql_real_escape_string($table->name);
		if(isset($where) && $where!=null)
			$query .= " WHERE ".$where;
		
		if(isset($orderBy) && $orderBy != null)
			$query .= " ORDER BY ".mysql_real_escape_string($orderBy);
		
		if($GLOBALS["logDb"] == true)
  			echo "<p>SQL: ".$query."</p>";
		$fetch = @mysql_query($query);
		if (mysql_errno() != 0)
		{
 			echo "<p>".mysql_error()."</p>";
			return null;
		}
		else if (mysql_num_rows($fetch) == 0)
		{
			return null;
		}
		else if ($unique && mysql_num_rows($fetch) > 1)
		{
			return null;
		}
		else
			return $fetch;
	}

	function getFieldsUnique($table, $fieldsArray, $field, $value)
	{
		$fetch = getFetchWithWhere($table, $fieldsArray, $field." = '".$value."'", true, null);
		if($fetch == null)
			return null;
		else
			return mysql_fetch_row($fetch);
	}
	
	function getFieldsForWhere($table, $fieldsArray, $where, $orderBy = null)
	{
		$fetch = getFetchWithWhere($table, $fieldsArray, $where, false, $orderBy);
		if($fetch == null)
			return null;
		else
		{
			$result = array();
			$i = 0;
			while ($row = mysql_fetch_row($fetch))
				$result[$i++] = $row;
			return $result;
		}
	}
	
	function getFieldsForField($table, $fieldsArray, $compareField, $compareValue, $orderBy = null)
	{
		$fetch = getFetchWithWhere($table, $fieldsArray, $compareField." = '".$compareValue."'", false, $orderBy);
		if($fetch == null)
			return null;
		else
		{
			$result = array();
			$i = 0;
			while ($row = mysql_fetch_row($fetch))
				$result[$i++] = $row;
			return $result;
		}
	}

	/*******************/
	/*  encode helper  */
	/*******************/
	
	function encode4url($text)
	{
		$code = str_replace("/", "%2F", $text);
		$code = str_replace("+", "%2B", $code);
		//echo "<p>".$code."</p>";
		return $code;
	}
	
	function decodeUrl($code)
	{
		$code = str_replace("%2F", "/", $code);
		$text = str_replace("%2B", "+", $code);
		return $text;
	}

	/******************/
	/* string helper  */
	/******************/
	
	function startsWith($haystack, $needle)
	{
		return $needle === "" || strpos($haystack,$needle) === 0;
	}
	
	function contains($haystack, $needle)
	{
		return strpos($haystack, $needle) !== false;
	}
	
	function equals($haystack, $needle)
	{
		return $haystack === $needle;
	}
	
	function removeQuotes($str)
	{
		if(empty($str))
			return "";
		$str = trim($str);
		$last = strlen($str);
		if($last < 1)
			return "";
		if(equals($str[0], "\"") && equals($str[$last-1], "\""))
			return substr($str, 1, $last-2);
		return $str;
	}
	
	function closeSession()
	{
		// Löschen aller Session-Variablen.
		$_SESSION = array();
		
		// Falls die Session gelöscht werden soll, löschen Sie auch das
		// Session-Cookie.
		// Achtung: Damit wird die Session gelöscht, nicht nur die Session-Daten!
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), 
						'', 
						time() - 42000, 
						$params["path"],
						$params["domain"], 
						$params["secure"], 
						$params["httponly"]
					);
		}
		
		// Zum Schluß, löschen der Session.
		session_destroy();
	}
	
	function echoAlert($message, $isSuccess)
	{
		if($isSuccess)
		{
			$panelClass = "success";
			$glyphClass = "ok";
		}
		else
		{
			$panelClass = "danger";
			$glyphClass = "flash";
		}
		echo "<div class=\"panel panel-".$panelClass."\"><div class=\"panel-body\">\n<span class=\"glyphicon glyphicon-".$glyphClass."\" aria-hidden=\"true\"></span> "
				.$message
		."\n</div></div>";
	}
?>