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
class Session
{  
	public static function start()
	{
		if(session_status() == PHP_SESSION_NONE){
			//处理SESSION
			if(config::get("session.use_mysql")){
				//@ini_set('session.save_handler','user');php7不支持了
				@ini_set('session.gc_maxlifetime',config::get("session.max_life_time",7200));
				$handler = new MysqlSessionHandler();
				@session_set_save_handler($handler, true);
			}else{
				if(config::get("session.session_save_path")) @session_save_path(config::get("session.session_save_path"));
			}
			@session_cache_limiter('private,must-revalidate');
			//@session_set_cookie_params(config::get("session.max_life_time",7200));
			session_start();
		}
	} 
}