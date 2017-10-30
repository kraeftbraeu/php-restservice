<?php
require_once "service/LogService.php";

class SqlService
{
	public static $dateFormat = "Y-m-d H:i:s";

	private $link;
	private $logService;

	public function __construct($link, $logService)
	{
		$this->link = $link;
		$this->logService = $logService;
	}
	
	public function execute($sql)
	{
		$result = mysqli_query($this->link, $sql);
		$this->logService->logSql($sql);
		if (!$result)
		{
			$this->logService->logError(mysqli_error($this->link));
			mysqli_close($this->link);
			http_response_code(400);
			die("SQL error");
		}
		return $result;
	}

	public function selectUnique($sql)
	{
		$result = $this->execute($sql);
		if (mysqli_num_rows($result) != 1)
		{
			$this->logService->logError(mysqli_num_rows($result)." objects found for sql <".$sql.">");
			mysqli_close($this->link);
			http_response_code(401);
			die("no unique object found");
		}
		return mysqli_fetch_object($result);
	}
}
?>
