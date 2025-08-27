<?php
namespace Sofast\Core;
use Sofast\Core\Exception as sfException;

/**
 * 路由控制
 */
class Router {
	private static $uri_string = '';
	private static $get = array();
	private static $folder = '';
	private static $module = 'App';
	private static $loader = NULL;

	public static function getFolder() {
		return self::$folder;
	}

	public static function getController() {
		return self::$get['controller'];
	}

	public static function getModule() {
		return self::$get['module'];
	}

	public static function get($key = '') {
		if ($key) {
			return self::$get[$key];
		} else {
			return self::$get;
		}

	}

	public static function getMethod() {
		return self::$get['method'];
	}

	public static function getUri() {
		return self::$uri_string;
	}

	public static function parse() {
		self::get_uri_string();
		self::parse_routes(self::$uri_string);
		$_GET = array_merge($_GET, self::$get);
		return self::$get;
	}

	private static function set_request($get = array()) {
		$loaders = spl_autoload_functions();
		//复合名称替换掉“-”
		$_gets = explode('-', $get[0]);
		$_get = implode('', array_map('ucfirst', $_gets));
		try {
			if (is_dir(config::get("controller_dir", APPPATH . "Controller/") . self::$folder . $_get)) {
				self::$folder .= ucfirst(array_shift($get)) . "/";
				self::set_request($get);
				return;
			} elseif (is_file($loaders[0][0]->findFile('App\\Controller\\' . self::$folder . $_get))) {
				self::$get['controller'] = self::$folder . ucfirst(array_shift($get));
				self::$get['method'] = $get[0] ? array_shift($get) : config::get("router.default_method", 'index');
			} else {
//默认控制器
				self::$get['controller'] = config::get("router.default_controller", 'welcome');
				self::$get['method'] = config::get("router.default_method", 'index');
			}

			for ($i = 0, $n = count($get); $i < $n; $i += 2) {
				self::$get[$get[$i]] = str_replace("'", "’", $get[($i + 1)]);
			}

		} catch (sfException $e) {
			$e->halt();
		}
	}

	private static function get_uri_string() {
		$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('REQUEST_URI');
		if (trim($path, '/') != '' && $path != $_SERVER['PHP_SELF']) {
			self::$uri_string = $path;
			return;
		}
		$path = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
		if (trim($path, '/') != '') {
			self::$uri_string = $path;
			return;
		}

		$path = (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO');
		if (trim($path, '/') != '' && $path != $_SERVER['PHP_SELF']) {
			self::$uri_string = str_replace($_SERVER['SCRIPT_NAME'], '', $path);
			return;
		}

		self::$uri_string = '';
	}

	private static function parse_routes($path) {
		//过滤
		$path = htmlspecialchars(ltrim($path, '/'), ENT_QUOTES);
		//解析PATH
		if ($path == '') {
			self::$get['controller'] = config::get("router.default_controller", 'welcome');
			self::$get['method'] = config::get("router.default_method", 'index');
		} else {
			if (config::get('parse_mode', 'PATH_INFO') == 'QUERY_STRING') {
				self::$get['module'] = $_GET[config::get("module_tag", "module")] ? $_GET[config::get("module_tag", "module")] : config::get("router.default_module", 'App');
				self::$get['controller'] = $_GET[config::get("controller_tag", "module")] ? $_GET[config::get("controller_tag", "module")] : config::get("router.default_controller", 'welcome');
				self::$get['method'] = $_GET[config::get("method_tag", "act")] ? $_GET[config::get("method_tag", "act")] : config::get("router.default_method", 'index');
				//取TOKEN
				self::$get['token'] = $_GET[config::get("token_tag", "token")] ? $_GET[config::get("token_tag", "token")] : getToken();
			} else {
				//处理PATH_INFO中的QUERY_STRING
				$paths = parse_url(trim($path, "/"));
				if ($_url_query = $paths['query']) {
					$path = $paths['path'];
					$_urls = explode('&amp;', $_url_query);
					foreach ($_urls as $_url) {
						$_maps = explode('=', $_url);
						self::$get[$_maps[0]] = $_maps[1];
					}
				}
				//解析路由
				$router = config::get("router.rule");
				if (isset($router[$path])) {
					self::set_request(explode("/", $router[$path]));
					return;
				}

				foreach ((array) $router as $key => $val) {
					$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
					if (preg_match('#^' . $key . '$#', $path)) {
						if (strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE) {
							$val = preg_replace('#^' . $key . '$#', $val, $path);
						}
						self::set_request(explode('/', $val));
						return;
					}
				}
				self::set_request(explode('/', $path));
			}
		}
	}

	public static function create_routes($uri) {
		$router = array_flip((array) config::get("router.rule"));
		$uri = trim($uri, "/");
		if (isset($router[$uri])) {
			return $router[$uri];
		}

		foreach ((array) $router as $key => $val) {
			$key = preg_replace('#\\$+\d{1,2}#', '([^/]+)', $key);

			if (preg_match_all('#^' . $key . '$#', $uri, $out)) {
				$_val = preg_split('#\([^_\(\)]*\)#', $val);
				for ($i = 0, $n = count($_val); $i < $n; $i++) {
					$str .= $_val[$i] . $out[$i + 1][0];
				}
				return $str;
			}
		}

		return $uri;
	}

}
?>