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
    public $u_id, $u_name, $u_adm;
    public function __construct($id, $name, $admin)
    {
        $this->u_id = $id;
        $this->u_name = $name;
        $this->u_adm = $admin;
    }

    public function isAdmin()
    {
         return $this->u_adm=== "Y" || $this->u_adm === "S";
    }
}

?>