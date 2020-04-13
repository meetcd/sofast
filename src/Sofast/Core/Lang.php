<?php
namespace Sofast\Core;
/**
 * 类名：lang
 * 功能：处理语言的国际化（多语言）
 * $Id: language.class.php 158 2009-07-20 09:47:02Z meetcd $
 */
class lang
{
	private static $sfLang = array();//语言库
	private static $lang = 'english';//当前语言

	/**
	 * 初始化语言翻译类
	 * @Author   meetcd
	 * @DateTime 2020-03-02T10:58:35+0800
	 * @param    string $dir 语言文件存放路劲
	 * @return   void
	 */
	public static function init($dir = '')
	{
		$dir = $dir ? APPPATH. $dir . '/' : APPPATH.'language/';
		include_dir($dir.self::getLang());
	}

	/**
	 * 加载语言文件
	 * @Author   meetcd
	 * @DateTime 2020-03-02T10:49:45+0800
	 * @return   [type]                   [description]
	 */
	public static function load()
	{
		$agrs = func_get_args();
		for($i=0,$n=count($agrs);$i<$n;$i++)
			myloader::language($agrs[$i]);
	}
	
	/**
	 * 将当前的语言类型保存起来，在没有更换语言种类之前，默认采用当前语言
	 * @Author   meetcd
	 * @DateTime 2020-03-02T10:50:39+0800
	 * @param    string  $lang 使用语言类型
	 */
	public static function setLang($lang='english')
	{
		if($lang){
			$_COOKIE[config::get("lang_cookie_name","sflang")] = $lang;
			return self::$lang = $lang;
		}
		if($_COOKIE[config::get("lang_cookie_name","sflang")]) return self::$lang = $_COOKIE[config::get("lang_cookie_name","sflang")];
		else return self::$lang = config::get("default_lang","english");
	}
	
	/**
	 * 取得当前语言种类
	 * @Author   meetcd
	 * @DateTime 2020-03-02T10:51:12+0800
	 * @return  string 当前语言的类型
	 */
	public static function getLang()
	{
		if($_COOKIE[config::get("lang_cookie_name","sflang")])//已经设置了语言就直接返回语言种类
			return $_COOKIE[config::get("lang_cookie_name","sflang")];
		!self::$lang && self::setLang();//没有语言种类就直接采用默认语言种类
		return self::$lang;
	}
	
	/**
	 * 向语言库加入新的语言翻译
	 * @Author   meetcd
	 * @DateTime 2020-03-02T10:51:45+0800
	 */
	public static function set()
	{
		$agrs = func_get_args();
		if(is_array($agrs[0]))
			self::$sfLang = array_merge(self::$sfLang,$agrs[0]);
		elseif($agrs[1]){
			$keys = array_reverse(explode(".",$agrs[0]));
			$val = $agrs[1];
			foreach($keys as $key){
				$result = array();
				$result[$key] = $val;
				$val = $result;
			}
			self::$sfLang = array_merge(self::$sfLang,$result);
		}
	}
	
	/**
	 * 取得指定值的当前语言翻译
	 * @Author   meetcd
	 * @DateTime 2020-03-02T10:56:35+0800
	 * @param    string $key 获取语言对应的标记
	 * @return   string  获取对应标记的语言翻译内容
	 */
	public static function get($key='')
	{
		if(!$key) return self::$sfLang;
		$keys = explode(".",$key);
		$result = self::$sfLang;
		foreach($keys as $key){
			$result = $result[$key];
		}
		if($result) return $result;
		else return $key;//没有翻译就直接返回当前字符
	}
}
