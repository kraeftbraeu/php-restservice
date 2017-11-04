<?php
class HttpService
{
	public function getServerVariable($variableName)
	{
		return filter_input(INPUT_SERVER, $variableName, FILTER_SANITIZE_STRING);
	}
}
?>