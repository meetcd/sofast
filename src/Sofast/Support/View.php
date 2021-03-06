<?php
namespace Sofast\Support;
use Sofast\Core\Config;
use Sofast\Core\Sf;
use Sofast\Core\Lang;

class View
{
	private static $viewData = array();
	private static $viewTpl = array();
	private static $content = '';
	private static $tplDir  = '';
	private static $fomrs = array();

	public static function set($key,$val='')
	{
		if(is_array($key)) self::$viewData = array_merge_recursive(self::$viewData,$key);
		else self::$viewData[$key] = $val;
	}
	
	public static function apply($name,$tpl)
	{
		$tpl && self::$viewTpl[$name] = $tpl;
	}
	
	public static function setTplDir($dir)
	{
		self::$tplDir = $dir;
	}
	
	public static function getTplDir()
	{
		return self::$tplDir;
	}
	
	public static function getContent($tpl,$key='')
	{
		if(!self::getTplDir()) self::setTplDir(config::get("view_dir"));
		$key = empty($key) ? $tpl : $key;
		extract(self::$viewData);
		ob_start();
		if(is_file(self::getTplDir().$tpl.".php")){
			include(self::getTplDir().$tpl.".php");
		}
		self::$viewData[$key] = str_replace('xmgl.scst.gov.cn','202.61.89.120',ob_get_contents());//替换以前的域名
		ob_end_clean();
		return self::$viewData[$key];
	}
	
	public static function parse($tpl)
	{
		foreach(self::$viewTpl as $key => $file)
			self::getContent($file,$key);
		self::$content = self::getContent($tpl);
		//插入csrf
		$_SESSION['__csrf'] = array();
		self::$content = preg_replace_callback('/<\/form>/is',function(){return self::getCsrf()."\n</form>";},self::$content);
		
		if(config::get("auto_create_html",false))
		{
			$file = trim($_SERVER['PATH_INFO'],'/');
			//是否需要获取token
			if(config::get('token_open',false) && config::get('parse_mode','PATH_INFO') == 'PATH_INFO'){
				$info  = explode("/",$file);
				$token = array_shift($info);
				$file  = implode('/',$info);
			}
			
			if($file){
				$file = WEBROOT.'/'.$file;
				sf::getLib("Files")->write($file,self::$content);
			}
		}
		return self::$content;
	}
	
	private static function write($fileName,$content)
	{
		return file_put_contents(trim(config::get("cache_dir","cache"),"/")."/".str_replace("/","-",$fileName),$content);
	}
	
	private static function read($fileName)
	{
		$file = trim(config::get("cache_dir","cache"),"/")."/".str_replace("/","-",$fileName);
		if(is_file($file) && ((time() - filemtime($file)) < config::get("cache_time","120")))
			return file_get_contents($file);
		else return false;
	}
	
	public static function display($tpl)
	{
		exit(self::parse($tpl));
	}

	public static function part($tpl)
	{
		return self::getContent($tpl);
	}
	
	public static function getCsrf()
	{
		$_SESSION['__csrf']['num'] += 1;
		$html = '';
		$form_id = '__csrf_form_'.$_SESSION['__csrf']['num'];
		$csrf = md5($form_id.time().rand(100,9999));
		$_SESSION['__csrf'][$form_id] = $csrf;//保存数据用于验证
		$html .= '<input name="'.$form_id.'" id="'.$form_id.'" type="hidden" value="'.$csrf.'" />';
		return $html;
	}
	
	/**
	 * 验证CSRF
	 */
	public static function checkCsrf()
	{   
		$num = (int)$_SESSION['__csrf']['num'];
		if($num < 1) return false;
		for($i=1;$i<=$num;$i++){
			$form_id = '__csrf_form_'.$i;
			if(isset($_POST[$form_id]) && $_POST[$form_id] == $_SESSION['__csrf'][$form_id]){
				unset($_SESSION['__csrf'][$form_id]);//验证后直接销毁
				return true;
			}
		}
		return false;
	}
	
}
?>