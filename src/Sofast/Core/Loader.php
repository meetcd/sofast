<?php
namespace Sofast\Core;
class loader
{

	public static function model($files)
	{
		if(is_array($files)){
			foreach($files as $file)
				self::loadfile($file,"model");
			return true;
		}
		return self::loadfile($files,"model");
	}
	
	public static function lib($files)
	{
		if(is_array($files)){
			foreach($files as $file)
				self::loadfile($file,"lib");
			return true;
		}
		return self::loadfile($files,"lib");
	}
	
	public static function controller($files)
	{
		if(is_array($files)){
			foreach($files as $file)
				self::loadfile($file,"controller");
			return true;
		}
		return self::loadfile($files,"controller");
	}
	
	public static function language($files)
	{
		$lang = lang::getLang();
		if(is_array($files)){
			foreach($files as $file)
				self::loadfile($lang."/".$file,"language");
			return true;
		}
		return self::loadfile($lang."/".$files,"language");
	}
	
	public static function plugin($files)
	{
		if(is_array($files)){
			foreach($files as $file)
				self::loadfile($file,"plugins");
			return true;
		}
		return self::loadfile($files,"plugins");
	}
	
	public static function helper($files)
	{
		if(is_array($files)){
			foreach($files as $file)
				self::loadfile($file,"helper");
			return true;
		}
		return self::loadfile($files,"helper");
	}
	
	public static function config($files)
	{
		if(is_array($files)){
			foreach($files as $file)
				self::loadfile($file,"config");
			return true;
		}
		return self::loadfile($files,"config");
	}
	
	public static function view($files)
	{
		if(is_array($files)){
			foreach($files as $file)
				self::loadfile($file,"view");
			return true;
		}
		return self::loadfile($files,"view");
	}
	
	public static function port($files)
	{
		if(is_array($files)){
			foreach($files as $file)
				self::loadfile($file,"port");
			return true;
		}
		return self::loadfile($files,"port");
	}
	
	private static function loadfile($file,$type='lib')
	{
		if($file = self::fileExist($file,$type)) return include_once($file);
		else return false;
	}
	
	public static function fileExist($file,$type='lib')
	{
		$_file = explode("/",$file);
		$file = ucfirst(array_pop($_file));
		$dir = $_file ? implode("/",$_file)."/" : '';
		if(is_file(config::get($type."_dir",APPPATH.$type."/").$dir.ucfirst($file).config::get($type."_ext",'.config.php')))
			return config::get($type."_dir",APPPATH.$type."/").$dir.ucfirst($file).config::get($type."_ext",'.config.php');
		else return false;
	}
	
	public static function folderExist($dir='',$type='lib')
	{
		if() return true;
		else return false;
	}
	
}