<?php
namespace Sofast\Contracts;

interface user
{	
	/**
	 * 待办事项列表
	 */
	private $todoList = array();
	
	/**
	 * 增加待办事项
	 */
	public function addToDo($subject,$url,$note='')
	{
			
	}
}
?>