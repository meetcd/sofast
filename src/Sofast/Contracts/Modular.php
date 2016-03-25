<?php
namespace Sofast\Contracts;

interface Modular
{	
	
	public function __construct(){}
	public function setup(){}//安装模块
	public function remove(){}//删除模块
	public function config(){}//设置
	
}
?>