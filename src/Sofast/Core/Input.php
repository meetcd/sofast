<?php
namespace Sofast\Core;
class Input {
	private static $sfInput = array();
	private static $is_do = false;

	/**
	 * 初始化
	 * @Author   meetcd
	 * @DateTime 2020-03-02T10:59:59+0800
	 * @return   void
	 */
	public static function init() {
		self::$sfInput['post'] = &$_POST;
		self::$sfInput['get'] = &$_GET;
		self::$sfInput['cookie'] = &$_COOKIE;
		self::$sfInput['env'] = &$_ENV;
		self::$sfInput['files'] = &$_FILES;
		self::$sfInput['request'] = &$_REQUEST;
		self::$sfInput['session'] = &$_SESSION;
		self::$sfInput['server'] = &$_SERVER;
		self::$sfInput['json'] = self::getJson();
		array_walk_recursive(self::$sfInput, "processVariables");
		self::$sfInput['mix'] = array();
		self::$sfInput['mix'] = array_merge(self::$sfInput['mix'], self::$sfInput['get']);
		self::$sfInput['mix'] = array_merge(self::$sfInput['mix'], self::$sfInput['post']);
		self::$is_do = true;
	}

	/**
	 * 获取输入
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function getInput($key = '') {
		!self::$is_do && self::init();
		if (!$key) {
			return self::$sfInput;
		}

		$keys = explode(".", $key);
		$result = self::$sfInput;
		foreach ($keys as $key) {
			$result = $result[$key];
		}
		return $result;
	}

	/**
	 * 获取混合输入
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function getMix($key = '') {
		if (self::getInput("post." . $key)) {
			return self::getInput("post." . $key);
		} else if (self::get($key)) {
			return self::get($key);
		} else {
			return '';
		}

	}

	/**
	 * 获取混合输入
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function mix($key = '') {
		if (self::getInput("post." . $key)) {
			return self::getInput("post." . $key);
		} else if (self::getInput("get." . $key)) {
			return self::getInput("get." . $key);
		} else {
			return '';
		}

	}

	/**
	 * 获取POST输入
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function post($key = '') {
		if (!$key) {
			return $_POST;
		}

		return $_POST[$key];
	}

	/**
	 * 获取GET输入
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function get($key = '') {
		if (!$key) {
			return $_GET;
		}

		return $_GET[$key];
	}

	/**
	 * 获取Json输入
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function json($key = '') {
		$json = self::getJson();
		if (!$key) {
			return $json;
		}
		return $json[$key];
	}

	/**
	 * 获取session
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function session($key = '') {
		if (!$key) {
			return $_SESSION;
		}

		return $_SESSION[$key];
	}

	/**
	 * 获取服务器参数
	 * @param  string $key [description]
	 * @return [type]      [description]
	 */
	public static function server($key = '') {
		if (!$key) {
			return $_SERVER;
		}

		return $_SERVER[$key];
	}

	/**
	 * 获取IP地址
	 * @return [type] [description]
	 */
	public static function getIp() {
		if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ($_SERVER['REMOTE_ADDR']) {
			return $_SERVER['REMOTE_ADDR'];
		} elseif ($_SERVER['HTTP_CLIENT_IP']) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} else {
			return '0.0.0.0';
		}

	}

	/**
	 * 获取JSON数据
	 **/
	private static function getJson() {
		$jsonStr = file_get_contents("php://input");
		if ($jsonData = json_decode($jsonStr, true)) {
			return $jsonData;
		} else {
			return array();
		}
	}
}