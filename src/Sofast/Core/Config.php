<?php
namespace Sofast\Core;

class config
{
	private static $sfConfig = array();
	
	public function __construct(){}
	
	public static function init($dir = '')
	{
		$dir = $dir ? APPPATH. $dir . '/' : APPPATH.'config/';
		include_dir($dir);
	}

	public static function load()
	{
		$agrs = func_get_args();
		for($i=0,$n=count($agrs);$i<$n;$i++)
			include_dir($agrs[$i]);
	}
	
	public static function set()
	{
		$agrs = func_get_args();
		if(is_array($agrs[0]))
			self::$sfConfig = array_merge(self::$sfConfig,$agrs[0]);
		elseif($agrs[1]){
			$keys = array_reverse(explode(".",$agrs[0]));
			$val = $agrs[1];
			foreach($keys as $key){
				$result = array();
				$result[$key] = $val;
				$val = $result;
			}
			self::$sfConfig = array_merge(self::$sfConfig,$result);
		}
	}
	
	public static function get($key='',$val=NULL)
	{
		if(!$key) return self::$sfConfig;
		$keys = explode(".",$key);
		$result = self::$sfConfig;
		foreach($keys as $key){
			$result = $result[$key];
		}
		if($result) return $result;
		else return $val;
	}
}
