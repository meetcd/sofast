<?php
namespace Sofast\Core;
use Sofast\Core\Config;
use Sofast\Support\MysqlSessionHandler;

/**
 * 会话管理
 *
 * @package Sofast\Core
 * @author  meetcd
 * @version $Id$
 */
class Session {
	public static function start() {
		if (session_status() == PHP_SESSION_NONE) {
			//处理SESSION
			if (strtolower(Config::get("session.save_handler")) == 'mysql') {
				@ini_set('session.gc_maxlifetime', Config::get("session.max_life_time", 7200));
				$handler = new MysqlSessionHandler();
				@session_set_save_handler($handler, true);
			} else {
				@ini_set("session.save_handler", Config::get("session.save_handler", 'files'));
				@ini_set("session.save_path", Config::get("session.save_path", WEBROOT . "/sessions"));
			}
			@session_cache_limiter('private,must-revalidate');
			session_start();
		}
	}
}