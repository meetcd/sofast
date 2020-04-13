<?php
namespace Sofast\Core;
class input
{
	private static $sfInput = array();
	private static $is_do = false;
	
	/**
	 * ����������ʼ��
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
		//���POST��GET����
		self::$sfInput['mix'] = array();
		self::$sfInput['mix'] = array_merge(self::$sfInput['mix'], self::$sfInput['get']);
		self::$sfInput['mix'] = array_merge(self::$sfInput['mix'], self::$sfInput['post']);
		self::$is_do = true;
	}
	
	/**
	 * ��ȡǰ���������
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:00:32+0800
	 * @param    string  $key ��������ļ�
	 * @return   string  $key��Ӧ�Ĳ���ֵ
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
	 * ��ȡ����������
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:01:36+0800
	 * @param    string   $key ��ϲ����ļ�
	 * @return   [type]   $key��Ӧ�Ļ�ϲ�����ֵ
	 */
	public static function getMix($key='')
	{
		if(self::getInput("post.".$key)) return self::getInput("post.".$key);
		else if(self::get($key)) return self::get($key);
		else return '';
	}
	
	/**
	 * ��ȡ��ϲ�������д��
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:02:42+0800
	 * @param    string                   $key ��ϲ����ļ�
	 * @return   string                   $key��Ӧ��ϲ�����ֵ
	 */
	public static function mix($key='')
	{
		if(self::getInput("post.".$key)) return self::getInput("post.".$key);
		else if(self::getInput("get.".$key)) return self::getInput("get.".$key);
		else return '';
	}
	
	/**
	 * ��ȡPOST���������ֵ
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:03:56+0800
	 * @param    string                   $key POST�����ļ�
	 * @return   [type]                   $key��ӦPOST������ֵ
	 */
	public static function post($key='')
	{
		if(!$key) return $_POST;
		return $_POST[$key];
	}
	
	/**
	 * ��ȡGET������ֵ
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:04:55+0800
	 * @param    string                   $key GET�����ļ�
	 * @return   [type]                   $key��Ӧ��GET������ֵ
	 */
	public static function get($key='')
	{
		if(!$key) return $_GET;
		return $_GET[$key];
	}
	
	/**
	 * ��ȡSESSION����ֵ
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:04:55+0800
	 * @param    string                   $key SESSION�����ļ�
	 * @return   [type]                   $key��Ӧ��SESSION������ֵ
	 */
	public static function session($key='')
	{
		if(!$key) return $_SESSION;
		return $_SESSION[$key];
	}
	
	/**
	 * ��ȡSERVER������ֵ
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:04:55+0800
	 * @param    string                   $key SERVER�����ļ�
	 * @return   [type]                   $key��Ӧ��SERVER������ֵ
	 */
	public static function server($key='')
	{
		if(!$key) return $_SERVER;
		return $_SERVER[$key];
	}
	
	/**
	 * ��ȡ�ͻ���IP
	 * @Author   meetcd
	 * @DateTime 2020-03-02T11:07:02+0800
	 * @return  string  �ͻ���IP��ַ
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