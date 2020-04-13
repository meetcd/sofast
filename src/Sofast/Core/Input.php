<?php
namespace Sofast\Core;
class input
{
	private static $sfInput = array();
	private static $is_do = false;
	
	/**
	 * 输入参数类初始化
	 * @Author   meetcd
	 * @DateTime 2020-03-02T10:59:59+0800
	 * @return   void 
	 */
	public static function init()
	{
		self::$sfInput['post'] = &$_POST;
		self::$sfInput['get'] = &$_GET;
		self::$sfInput['cookie'] = &$_COOKIE;
		self::$sfInput['env'] = &$_ENV;
		self::$sfInput['files'] = &$_FILES;
		self::$sfInput['request'] = &$_REQUEST;
		self::$sfInput['session'] = &$_SESSION;
		self::$sfInput['server'] = &$_SERVER;
		array_walk_recursive(self::$sfInput,"processVariables");
		//混合POST和GET参数
		self::$sfInput['mix'] = array();
		self::$sfInput['mix'] = array_merge(self::$sfInput['mix'], self::$sfInput['get']);
		self::$sfInput['mix'] = array_merge(self::$sfInput['mix'], self::$sfInput['post']);
		self::$is_do = true;
	}
	
	/**
	 * 获取前端输入参数
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:00:32+0800
	 * @param    string  $key 输入参数的键
	 * @return   string  $key对应的参数值
	 */
	public static function getInput($key='')
	{
		!self::$is_do && self::init();
		if(!$key) return self::$sfInput;
		$keys = explode(".",$key);
		$result = self::$sfInput;
		foreach($keys as $key){
			$result = $result[$key];
		}
		return $result;
	}
	
	/**
	 * 获取混合输入参数
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:01:36+0800
	 * @param    string   $key 混合参数的键
	 * @return   [type]   $key对应的混合参数的值
	 */
	public static function getMix($key='')
	{
		if(self::getInput("post.".$key)) return self::getInput("post.".$key);
		else if(self::get($key)) return self::get($key);
		else return '';
	}
	
	/**
	 * 获取混合参数精简写法
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:02:42+0800
	 * @param    string                   $key 混合参数的键
	 * @return   string                   $key对应混合参数的值
	 */
	public static function mix($key='')
	{
		if(self::getInput("post.".$key)) return self::getInput("post.".$key);
		else if(self::getInput("get.".$key)) return self::getInput("get.".$key);
		else return '';
	}
	
	/**
	 * 获取POST输入参数的值
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:03:56+0800
	 * @param    string                   $key POST参数的键
	 * @return   [type]                   $key对应POST参数的值
	 */
	public static function post($key='')
	{
		if(!$key) return $_POST;
		return $_POST[$key];
	}
	
	/**
	 * 获取GET参数的值
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:04:55+0800
	 * @param    string                   $key GET参数的键
	 * @return   [type]                   $key对应的GET参数的值
	 */
	public static function get($key='')
	{
		if(!$key) return $_GET;
		return $_GET[$key];
	}
	
	/**
	 * 获取SESSION参数值
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:04:55+0800
	 * @param    string                   $key SESSION参数的键
	 * @return   [type]                   $key对应的SESSION参数的值
	 */
	public static function session($key='')
	{
		if(!$key) return $_SESSION;
		return $_SESSION[$key];
	}
	
	/**
	 * 获取SERVER参数的值
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:04:55+0800
	 * @param    string                   $key SERVER参数的键
	 * @return   [type]                   $key对应的SERVER参数的值
	 */
	public static function server($key='')
	{
		if(!$key) return $_SERVER;
		return $_SERVER[$key];
	}
	
	/**
	 * 获取客户端IP
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:07:02+0800
	 * @return  string  客户端IP地址
	 */
	public static function getIp()
	{
		if ($_SERVER['HTTP_X_FORWARDED_FOR'])
		{
			 return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif ($_SERVER['REMOTE_ADDR'])
		{
			 return $_SERVER['REMOTE_ADDR'];
		}
		elseif ($_SERVER['HTTP_CLIENT_IP'])
		{
			 return $_SERVER['HTTP_CLIENT_IP'];
		}else return false;
	}
	
}