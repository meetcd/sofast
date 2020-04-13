<?php
namespace Sofast\Support;
use Sofast\Core\Config;
use Sofast\Core\Sf;
use Sofast\Core\Lang;

class template
{
	private $viewData = array();
	private $viewTpl = array();
	private $content = '';
	private $tplDir  = '';
	private $fomrs = array();
	
	function __construct($dir='')
	{
		$this->tplDir = $dir;
	}
	
	public function set($key,$val='')
	{
		if(is_array($key)) $this->viewData = array_merge_recursive($this->viewData,$key);
		else $this->viewData[$key] = $val;
	}
	
	public function apply($name,$tpl)
	{
		$tpl && $this->viewTpl[$name] = $tpl;
	}
	
	public function setTplDir($dir)
	{
		$this->tplDir = $dir;
	}
	
	public function getTplDir()
	{
		return $this->tplDir;
	}
	
	public function getContent($tpl,$key='')
	{
		if(!$this->getTplDir()) $this->setTplDir(config::get("view_dir"));
		$key = empty($key) ? $tpl : $key;
		extract($this->viewData);
		ob_start();
		if(is_file($this->getTplDir().$tpl.".php")){
			include($this->getTplDir().$tpl.".php");
		}
		$this->viewData[$key] = str_replace('xmgl.scst.gov.cn','202.61.89.120',ob_get_contents());//替换以前的域名
		ob_end_clean();
		return $this->viewData[$key];
	}
	
	public function parse($tpl)
	{
		foreach($this->viewTpl as $key => $file)
			$this->getContent($file,$key);
		$this->content = $this->getContent($tpl);
		//插入csrf
		$_SESSION['__csrf'] = array();
		$this->content = preg_replace_callback('/<\/form>/is',function(){return $this->getCsrf()."\n</form>";},$this->content);
		
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
				sf::getLib("Files")->write($file,$this->content);
			}
		}
		return $this->content;
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
	
	public function display($tpl)
	{
		exit($this->parse($tpl));
	}

	public function part($tpl)
	{
		return $this->getContent($tpl);
	}
	
	public function getCsrf()
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
	public function checkCsrf()
	{   
		$num = (int)$_SESSION['__csrf']['num'];
		if($num < 1) return true;
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