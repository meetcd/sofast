<?php
use Sofast\Core\Config;
use Sofast\Core\Router;
use Sofast\Core\Sf;

if (!function_exists('add_event')) {
	/**
	 * 增加事件
	 *
	 * @param  array  $array
	 * @return array
	 */
	function add_event($hook, $function) {

	}
}

if (!function_exists('do_event')) {
	/**
	 * 执行事件
	 *
	 * @param  array  $array
	 * @return array
	 */
	function do_event($hook) {
		$events = config::get("events." . $hook);
		foreach ($events as $event) {

		}
	}
}

if (!function_exists('append_config')) {
	/**
	 * Assign high numeric IDs to a config item to force appending.

	 * @param  array  $array
	 * @return array
	 */
	function append_config(array $array) {
		$start = 9999;

		foreach ($array as $key => $value) {
			if (is_numeric($key)) {
				$start++;

				$array[$start] = Arr::pull($array, $key);
			}
		}

		return $array;
	}
}

if (!function_exists('getmicrotime')) {
	/**
	 * Assign high numeric IDs to a config item to force appending.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function getmicrotime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}
}

if (!function_exists('include_dir')) {
	/**
	 * Assign high numeric IDs to a config item to force appending.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function include_dir($dir) {
		$handle = @dir($dir);
		if (!$handle) {
			return false;
		}

		while (($file = $handle->read()) !== false) {
			if (is_file($dir . '/' . $file)) {
				include_once $dir . '/' . $file;
			}

		}
		$handle->close();
	}
}

if (!function_exists('processVariables')) {
	/**
	 * Assign high numeric IDs to a config item to force appending.
	 *
	 * @param  array  $var
	 * @param  string  $key
	 * @return array
	 */
	function processVariables(&$var, $key) {
		if (is_array($var)) {
			foreach ($var as $k => $v) {
				processVariables($var[$k], $k);
			}

		} else {
			$var = addslashes(xss_clean($var));
		}
	}
}

if (!function_exists('site_url')) {
	function site_url($uri = '', $route = true, $real_uri = '') {
		$site_url = $real_uri ? trim($real_uri, "/") : trim(config::get("base_url"), "/");
		if (config::get("index_page")) {
			$site_url .= "/" . config::get("index_page");
		}

		if (config::get('token_open', false)) {
			$site_url .= "/" . getToken();
		}

		$site_url .= "/" . ($route ? router::create_routes($uri) : trim($uri, "/"));
		return trim($site_url);
	}
}

if (!function_exists('site_path')) {
	function site_path($uri = '', $real_uri = '') {

		return trim($real_uri ? $real_uri : config::get("base_url"), '/') . "/" . trim($uri, '/');
	}
}

if (!function_exists('base_url')) {
	function base_url() {
		return trim(config::get("base_url"), '/') . "/";
	}
}

if (!function_exists('link_to')) {
	function link_to($uri = '', $name = 'link', $opt = array()) {
		if ($name == "/") {
			return $name;
		}

		foreach ((array) $opt as $key => $val) {
			$a_opt .= " " . $key . '="' . $val . '"';
		}
		return '<a href="' . site_url($uri) . '" ' . $a_opt . ' >' . $name . '</a>';
	}
}

if (!function_exists('getFromUrl')) {
	function getFromUrl($debarUrl = '', $targetUrl = '') {
		$fromUrl = $_POST['fromUrl'] ? $_POST['fromUrl'] : $_SERVER['HTTP_REFERER'];
		//过滤跨站攻击
		$fromUrl = xss_clean($fromUrl);
		$fromUrl = strpos($fromUrl, '#') ? substr($fromUrl, 0, strpos($fromUrl, '#')) : $fromUrl;
		if ($debarUrl && $targetUrl && strpos($fromUrl, $debarUrl)) {
			return $targetUrl;
		} else {
			return $fromUrl;
		}

	}
}

if (!function_exists('getColumnStr')) {
	function getColumnStr($subject = '', $orderfield = '') {
		$result = router::get();
		$o_orderfield = $result['orderfield'];
		$result['ordermode'] = ($result['ordermode'] == 'DESC') ? 'ASC' : 'DESC';
		$result['orderfield'] = $orderfield;

		if (config::get('token_open', false)) {
			array_shift($result);
			unset($_GET['token']);
			unset($result['token']);
		}

		$str = array_shift($result);
		$str .= '/' . array_shift($result);

		foreach ($result as $key => $val) {
			$str .= '/' . $key . '/' . $val;
		}

		//为排序字段加上标记
		if ($o_orderfield == $orderfield) {
			$class = array('class' => $result['ordermode']);
		} else {
			$class = array();
		}

		return link_to($str, $subject, $class);
	}
}

if (!function_exists('getToken')) {
	function getToken() {
		return substr('TK' . md5(session_id() . $_SESSION['userid']), 0, 6);
	}
}

if (!function_exists('_include')) {
	function _include($file = '') {
		return include config::get("view_dir") . $file;
	}
}

if (!function_exists('M')) {
	function M($m) {
		return sf::getModel($m);
	}
}

if (!function_exists('showText')) {
	function showText($char) {
		$char = htmlspecialchars($char);
		$char = rtrim($char);
		$char = str_replace(" ", "&nbsp;", $char);
		$char = nl2br($char);
		$char = str_replace("<?", "< ?", $char);
		return $char;
	}
}

/**
 * 去掉数据中的括号，主要用于serialize时候出现的双引号但引号的问题
 *
 */
if (!function_exists('removeQuotation')) {
	function removeQuotation($char) {
		if (is_array($char)) {
			foreach ($char as $key => $val) {
				$char[$key] = removeQuotation($val);
			}
			return $char;
		}

		$char = str_replace('\"', '”', $char);
		$char = str_replace("\'", '’', $char);
		$char = str_replace('\\', '|', $char);
		return $char;
	}
}

/**
 * 去掉json格式的非法字符
 *
 */
if (!function_exists('cleanJson')) {
	function cleanJson($char) {
		if (is_array($char)) {
			foreach ($char as $key => $val) {
				$char[$key] = cleanJson($val);
			}
			return $char;
		}

		$char = str_replace('\"', '”', $char); //替换双引号
		$char = str_replace("\'", '’', $char); //替换单引号
		$char = str_replace('\\', '|', $char); //替换反斜杠
		$char = str_replace(";", '；', $char); //替换分号
		$char = str_replace(",", '，', $char); //替换逗号
		$char = str_replace("\t", '', $char); //移除tab
		$char = str_replace("\r", '', $char); //移除退回
		$char = str_replace("\n", '', $char); //移除换行
		$char = trim($char); //移除前后空格
		return $char;
	}
}

if (!function_exists('xss_clean')) {
	function xss_clean($data) {
		// Fix &entity\n;
		$data = str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
		do {
// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		} while ($old_data !== $data);
		// we are done...
		return $data;
	}
}

if (!function_exists('getCsrf')) {
	function getCsrf($c = '', $m = '') {
		$c = $c ? $c : Router::getController();
		$m = $m ? $m : Router::getMethod();
		return md5($_COOKIE['PHPSESSID'] . $c . $m);
	}
}

if (!function_exists('get_url_query_params')) {
	/**
	 * [get_url_query_params 从URL的query中获取参数]
	 * @param  string $query URL中query部分
	 * @return array         参数数组
	 */
	function get_url_query_params($query) {
		$params = $_querys = array();
		$_querys = explode('&', $query);
		foreach ((array) $_querys as $_query) {
			if ($_query) {
				$_item = explode('=', $_query);
				$params[trim($_item[0])] = trim($_item[1]);
			}
		}
		return $params;
	}
}

if (!function_exists('mk_dir')) {
	/**
	 * 创建目录
	 * @param  string $_dir_name 目录名称
	 * @return bool 创建结果
	 */
	function mk_dir($_dir_name) {
		//判断是否可以创建
		return mkdir($_dir_name, 0777, ture);
	}
}
