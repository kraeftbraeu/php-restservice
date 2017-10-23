<?php
class AdminService
{
	private $link;
	public $userFieldId, $userFieldName, $userFieldPassword, $userFieldLastLog, $userFieldAdmin;
	public $filterFieldId, $filterFieldGiver, $filterFieldWisher;
	public $wishFieldId, $wishFieldUser, $wishFieldDescr, $wishFieldLink;
	public $presentFieldId, $presentFieldWisher, $presentFieldGiver, $presentFieldWish, $presentFieldDescr, $presentFieldLink;
	public $userTable, $filterTable, $wishTable, $presentTable;

	public function __construct($link)
	{
		$this->link = $link;

		$this->userFieldId = new Field("u_id", "INT", false);
		$this->userFieldName = new Field("u_name", "VARCHAR(50)", false);
		$this->userFieldPassword = new Field("u_pw", "VARCHAR(255)", false);
		$this->userFieldLastLog = new Field("u_log", "DATETIME", true);
		$this->userFieldAdmin = new Field("u_adm", "VARCHAR(1)", true);
		
		$this->filterFieldId = new Field("f_id", "INT", false);
		$this->filterFieldGiver = new Field("f_giver", "INT", false);
		$this->filterFieldWisher = new Field("f_wisher", "INT", false);

		$this->wishFieldId = new Field("w_id", "INT", false);
		$this->wishFieldUser = new Field("w_user", "INT", false);
		$this->wishFieldDescr = new Field("w_descr", "VARCHAR(200)", false);
		$this->wishFieldLink = new Field("w_link", "VARCHAR(200)", true);

		$this->presentFieldId = new Field("p_id", "INT", false);
		$this->presentFieldWisher = new Field("p_wisher", "INT", false);
		$this->presentFieldGiver = new Field("p_giver", "INT", false);
		$this->presentFieldWish = new Field("p_wish", "INT", true);
		$this->presentFieldDescr = new Field("p_pdescr", "VARCHAR(200)", true);
		$this->presentFieldLink = new Field("p_plink", "VARCHAR(200)", true);

		$this->userTable = new Table("user", array($this->userFieldId, $this->userFieldName, $this->userFieldPassword, $this->userFieldLastLog, $this->userFieldAdmin));
		$this->filterTable = new Table("filter", array($this->filterFieldId, $this->filterFieldGiver, $this->filterFieldWisher));
		$this->wishTable = new Table("wish", array($this->wishFieldId, $this->wishFieldUser, $this->wishFieldDescr, $this->wishFieldLink));
		$this->presentTable = new Table("present", array($this->presentFieldId, $this->presentFieldWisher, $this->presentFieldGiver, $this->presentFieldWish, $this->presentFieldDescr, $this->presentFieldLink));
	}

	function sql($query)
	{
  		if($GLOBALS["logDb"] == true)
  			echo "<p>SQL: ".$query."</p>";
		@mysqli_query($this->link, $query);
		if (mysqli_errno($this->link) != 0)
			return mysqli_error($this->link);
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
				$value = "'".mysqli_real_escape_string($this->link, $value)."'";
			$query .= $value;
			if($i < $count-1)
				$query .= ", ";
			else $query .= ")";
		}
		return $this->sql($query);
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
			$query .= mysqli_real_escape_string($this->link, $key)." = ";
			if($value == null)
				$query .= "NULL";
			else
				$query .= "'".mysqli_real_escape_string($this->link, $value)."'";
			if($i < $count - 1)
				$query .= ", ";
		}
		$query .= " WHERE ".$table->idField->name." = ".mysqli_real_escape_string($this->link, $id);
		return $this->sql($query);
	}
	
	function sqlDelete($table, $id)
	{
		$query = "DELETE FROM ".$table->name." WHERE ".$table->idField->name." = ".mysqli_real_escape_string($this->link, $id);
		return $this->sql($query);
	}
	
	function sqlSelect($query)
	{
  		if($GLOBALS["logDb"] === true)
  			echo "<p>SQL: ".$query."</p>";
		$fetch = @mysqli_query($this->link, $query);
		if (mysqli_errno($this->link) != 0)
		{
			echo "<p>".mysqli_error($this->link)."</p>";
			return null;
		}
		else if ($fetch === false ||mysqli_num_rows($fetch) === 0)
			return null;
		else if($fetch === null)
			return null;
		else
		{
			$result = array();
			$i = 0;
			while ($row = mysqli_fetch_row($fetch))
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
		$query .= mysqli_real_escape_string($this->link, $fieldsArray[$i]);
		if($i < $count-1)
			$query .= ", ";
		}
		$query .= " FROM ".mysqli_real_escape_string($this->link, $table->name);
		if(isset($where) && $where!=null)
			$query .= " WHERE ".$where;
		
		if(isset($orderBy) && $orderBy != null)
			$query .= " ORDER BY ".mysqli_real_escape_string($this->link, $orderBy);
		
		if($GLOBALS["logDb"] == true)
  			echo "<p>SQL: ".$query."</p>";
		$fetch = @mysqli_query($this->link, $query);
		if (mysqli_errno($this->link) != 0)
		{
 			echo "<p>".mysqli_error($this->link)."</p>";
			return null;
		}
		else if (mysqli_num_rows($this->link, $fetch) == 0)
		{
			return null;
		}
		else if ($unique && mysqli_num_rows($this->link, $fetch) > 1)
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
			return mysqli_fetch_row($this->link, $fetch);
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
			while ($row = mysqli_fetch_row($this->link, $fetch))
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
			while ($row = mysqli_fetch_row($this->link, $fetch))
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

}

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
?>