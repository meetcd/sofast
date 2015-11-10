<?php
namespace System\Core;
class Exception extends \Exception
{
    public function __toString() {
        return $this->halt();
    }
	
	public function show()
	{
		$title = lang::get("System notes:");
		$msg = $this->getMessage();
		$more = $this->getFile().":".$this->getLine();
		$trace = nl2br($this->getTraceAsString());
		ob_start();
		if(is_file(APPPATH."error/error.php")) include_once(APPPATH."error/error.php");
		else include_once(SYSTEMPATH."error/error.php");
		ob_end_flush();
	}
	
	public function halt()
	{
		$title = lang::get("System notes:");
		$msg = $this->getMessage();
		$more = $this->getFile().":".$this->getLine();
		$trace = nl2br($this->getTraceAsString());
		ob_start();
		if(is_file(APPPATH."error/error.php")) include_once(APPPATH."error/error.php");
		else include_once(SYSTEMPATH."error/error.php");
		ob_end_flush();
		exit;
	}
}

?>