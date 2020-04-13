<?php
namespace Sofast\Contracts;

interface module
{	
	private $module_name = NULL;
	public function install(){}//安装模块
	public function uninstall(){}//删除模块
	public function config(){}//设置
}
?>