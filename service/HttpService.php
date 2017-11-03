<?php
class HttpService
{
	public function getServerName()
	{
		return $this->getServerVariable("SERVER_NAME");
	}
	
	public function getServerVariable($variableName)
	{
		return $_SERVER[$variableName];
	}
}
?>