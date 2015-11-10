<?php
namespace Sofast\Support;
use Sofast\Core\Config;
use Sofast\Core\Sf;
use Sofast\Core\Lang;

class View
{
	private static $viewData = array();
	private static $viewTpl = array();
	private static $content = '';

	public static function set($key,$val='')
	{
		if(is_array($key)) self::$viewData = array_merge_recursive(self::$viewData,$key);
		else self::$viewData[$key] = $val;
	}
	
	public static function apply($name,$tpl)
	{
		$tpl && self::$viewTpl[$name] = $tpl;
	}
	
	public static function getContent($tpl,$key='')
	{
		$key = empty($key) ? $tpl : $key;
		extract(self::$viewData);
		ob_start();
		if(is_file(config::get("view_dir").$tpl.".php")){
			include_once(config::get("view_dir").$tpl.".php");
		}
		
		self::$viewData[$key] = ob_get_contents();
		ob_end_clean();
		return self::$viewData[$key];
	}
	
	public static function parse($tpl)
	{
		foreach(self::$viewTpl as $key => $file)
			self::getContent($file,$key);
		self::$content = self::getContent($tpl);
		
		if(config::get("auto_create_html",false))
		{
			$file = trim($_SERVER['PATH_INFO'],'/');
			//是否需要获取token
			if(config::get('token_open',false) && config::get('parse_mode','PATH_INFO') == 'PATH_INFO'){
				$info  = explode("/",$file);
				$token = array_shift($info);
				$file  = implode('/',$info);
			}
			
			if($file){
				$file = WEBROOT.'/'.$file;
				sf::getLib("Files")->write($file,self::$content);
			}
		}
		return self::$content;
	}
	
	private static function write($fileName,$content)
	{
		return file_put_contents(trim(config::get("cache_dir","cache"),"/")."/".str_replace("/","-",$fileName),$content);
	}
	
	private static function read($fileName)
	{
		$file = trim(config::get("cache_dir","cache"),"/")."/".str_replace("/","-",$fileName);
		if(is_file($file) && ((time() - filemtime($file)) < config::get("cache_time","120")))
			return file_get_contents($file);
		else return false;
	}
	
	public static function display($tpl)
	{
		exit(self::parse($tpl));
	}
	
	public static function part($part,$key='')
	{
		return self::getContent($part);
	}

}
?>