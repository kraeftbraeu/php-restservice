<?php
class LogService
{
	public function logSql($log)
	{
		file_put_contents("log/sql.log", "\r\n".($this->timeStamp())." ".$log, FILE_APPEND | LOCK_EX);
	}

	public function logError($log)
	{
		file_put_contents("log/error.log", "\r\n".($this->timeStamp())." ".$log, FILE_APPEND | LOCK_EX);
	}

	private function timeStamp()
	{
		return date('Y-m-d H:i:s');
	}
}	
?>