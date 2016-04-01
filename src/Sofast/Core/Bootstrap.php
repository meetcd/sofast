<?php
namespace Sofast\Core;
use Sofast\Core\Config;
use Sofast\Core\Router;
use Sofast\Core\Lang;
use Sofast\Core\Exception as sfException;
use Illuminate\Database\Capsule\Manager as Capsule;

class bootstrap {
	private static function init()
	{
		session_start();
		config::set('start_time',getmicrotime());
		//加载配置文件
		config::init();
		//初始化pathinfo
		router::parse();
		//加载语言文件
		lang::setLang(config::get("default_lang","chinese"));
		lang::init();
		//更新内核
		sf::update();
	}
	
	public static function run()
	{
		self::init();
		//启动数据组件
		$Capsule = new Capsule;
		$Capsule->addConnection(config::get('database'));
		// 使用DB
		$Capsule->bootEloquent();

		$controller = sf::getController(router::getController());
		try{
			method_exists($controller , "load") && $controller->load();
			if(!method_exists($controller , router::getMethod()))
				throw new sfException(sprintf(lang::get("Call to undefined method %s::%s"),get_class($controller),router::getMethod()));
			$controller->{router::getMethod()}();
			method_exists($controller , "shutdown") && $controller->shutdown();
		}catch(sfException $e){
			method_exists($controller , "shutdown") && $controller->shutdown();
			$e->halt();
		}	
	}
}
?>