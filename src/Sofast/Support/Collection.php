<?php
namespace Sofast\Support;
class Collection
{
	private $cur_row			= 0;
	private $total_row			= 0;
	private $object				= NULL;
	private $field				= array();

	public function __construct($object=NULL,$field=array())
	{
		is_object($object) && $this->object = $object;
		$field && $this->field = $field;
	}
	
	public function setObject($object = NULL)
	{
		is_object($object) && $this->object = $object;
	}
	
	public function setField($field = array())
	{
		$this->field = $field;
		$this->total_row = $this->getTotal();
	}
	
	public function getTotal()
	{
		return count($this->field);
	}
	
	public function getObject($row='')
	{
		!$row && $row = $this->cur_row;
		if($this->field[$row])
		{
			$this->object->__construct($this->field[$row]);
			$this->cur_row ++;
			return $this->object;
		}
		return false;
	}
	
	public function getJson()
	{
		return '{"total":'.$this->getTotal().',"rows":'.json_encode($this->field).'}';
	}
	
	public function MoveTo($row = 1)
	{
		if($row > 0 && $row < $this->getTotal())
			return $this->getObject($row);
		else return false;
	}
	
	public function MoveNext()
	{
		$row = $this->cur_row++;
		return $this->MoveTo($row);
	}
	
	public function MoveFirst()
	{
		$row = $this->cur_row = 1;
		return $this->MoveTo($row);
	}
	
	public function toArray()
	{
		return $this->field;
	}
	
	public function reset()
	{
		$this->cur_row = 0;
	}
	
	public function getIndex()
	{
		return $this->cur_row;
	}
}
?>