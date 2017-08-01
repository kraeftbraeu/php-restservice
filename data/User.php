<?php
	require "Data.php";

/*
$userFieldId = new Field("u_id", "INT", false);
$userFieldName = new Field("u_name", "VARCHAR(50)", false);
$userFieldPassword = new Field("u_pw", "VARCHAR(255)", false);
$userFieldLastLog = new Field("u_log", "DATETIME", true);
$userFieldAdmin = new Field("u_adm", "VARCHAR(1)", true);
*/
class User //extends Data
{
    public $id, $name, $admin;
    public function __construct($id, $name, $admin)
    {
        $this->id = $id;
        $this->name = $name;
        $this->admin = $admin;
    }

    public function isAdmin()
    {
         return $this->admin=== "Y" || $this->admin === "S";
    }
}

?>