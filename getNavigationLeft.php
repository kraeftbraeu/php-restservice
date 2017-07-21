<?php
	include("isValid.php");
	$nav = array("start" => "Start");
	$nav = array_merge($nav, array("wishes" => "Meine Wünsche", "presents" => "Meine Geschenke"));
	if($isAdmin)
		$nav = array_merge($nav, array("admin" => "Admin"));
	echo json_encode($nav);
?>