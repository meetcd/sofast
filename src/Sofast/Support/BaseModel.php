<?php
namespace Sofast\Support;
use Sofast\Core\Sf;
use Sofast\Core\Router;
use Sofast\Support\Collection;
use Sofast\Support\Pager;

class BaseModel
{	
	/**
	 * SQL构建器
	 * @var null
	 */
	private $builder    = NULL;
	/**
	 * 模型属性
	 * @var array
	 */
	private $attributes = array();
	/**
	 * 模型变更数据
	 * @var array
	 */
	private $fieldData  = array();
	private $fields = '*';
	
	public function __clone()
	{
		return $this->cleanObject();
	}
	
	public function setFields($fields=array())
	{
		if(!in_array($this->primary_key,$fields)) $fields[] = $this->primary_key;
		$this->fields = trim(implode(',',$fields),',');
		return $this;
	}
	
	public function selectAll($addWhere = '',$addSql = '',$showMax = 0,$select = '')
	{
		$db = sf::getLib("db");
		
		if($select) $sql = $select." ";
		else $sql = "SELECT $this->fields FROM ".$this->table." ";
		$addWhere && $sql .= "WHERE ".$addWhere." ";
		$addSql && $sql .= $addSql;
		$showMax && $sql .= ' LIMIT '.$showMax;
		
		$query = $db->query($sql);
		
		return new collection(clone $this,$db->result_array($query));
	}
	
	public function select($addWhere = '',$addSql = '',$start=0,$showMax = 0)
	{
		$db = sf::getLib("db");
		
		$sql = "SELECT $this->fields FROM ".$this->table." ";
		$addWhere && $sql .= "WHERE ".$addWhere." ";
		$addSql && $sql .= $addSql;
		$showMax && $sql .= " LIMIT $start,$showMax ";
		
		$query = $db->query($sql);
		
		return new collection(clone $this,$db->result_array($query));
	}
	
	public function getPager($addWhere = '',$addSql = '',$showMax = 20,$select = '',$key = '',$form_vars=array())
	{
		$db = sf::getLib("db");
		
		if($select) $sql = $select." ";
		else $sql = "SELECT $this->fields FROM `".$this->table."` ";
		$addWhere && $sql .= "WHERE ".$addWhere." ";
		$addSql && $sql .= $addSql." ";
		
		if(!router::get("totalnum".$key) || count($_POST) > 0){
			$_sql = "SELECT COUNT(*) AS NUM FROM `".$this->table."` ";
			$addWhere && $_sql .= "WHERE ".$addWhere." ";
			$addSql && $_sql .= $addSql." ";
			$row = $db->fetch_first($_sql);
			$total = $row['NUM'];
		}else $total = router::get("totalnum".$key);
		
		$pager = new pager($total,$showMax,$key,$form_vars);
		$sql .= "LIMIT ".$pager->getStartNum().",".$pager->getShowNum();
		$query = $db->query($sql);
		
		$pager->setField($db->result_array($query));
		$pager->setObject(clone $this);
		
		return $pager;
	}
	
	public function validate(){}//验证函数

	/**
	 * 魔法函数
	 * @param  string $method 			调用方法
	 * @param  array $args    			参数
	 * @return object|array|string|int 	返回值
	 */
	public function __call($method, $args)
    {
        $methods = preg_split("/(?=[A-Z])/",$method);
        switch(array_shift($methods))
        {
        	case 'get':
        		return $this->attributes[strtolower(implode('_',$methods))];
        	break;
        	case 'set':
        		$col_name = strtolower(implode('_',$methods));
        		$this->fieldData[$col_name] = $args[0];
        		$this->attributes[$col_name] = $args[0];
        		return $this;
        	break;
        	default:
        		return $this;
        	break;
        }
    }

    public function save()
	{
		$db = sf::getLib("db");
		if($this->fieldData){
		if(!$this->is_new)
		{
			return $db->update($this->fieldData,"`id` = '".$this->getId()."' ",$this->table); 
		}
		if($this->id = $db->insert($this->fieldData,$this->table)){
			$this->is_new = false;
			return true;
		}else return false;
    }
	}

	public function remove($addWhere = '')
	{
		if(!$addWhere) return false;
		$db = sf::getLib("db");
    	$sql = "DELETE FROM `$this->table` WHERE $addWhere ";
    	$db->query($sql);
    	return $db->affected_rows();
	}

	public function toArray()
	{
		return $this->attributes;
	}

	public function cleanObject()
	{
		$this->attributes 	= array();
		$this->fieldData  	= array();
		$this->is_new 		= true;
		return $this;
	}

	public function fillObject($data=array())
	{
		$this->cleanObject();
		if(!$data) return $this;
		if($data["is_new"]) $this->is_new = true;
		else $this->is_new = false;
		$this->attributes = $data;
		return $this;
	}

	public function __construct($data='')
	{
		if(!$data) return $this;
		if(is_array($data))
			return $this->fillObject($data);
		else return $this->selectByPk($data);
	}

	public function selectByPk($pk='')
	{
		if(!$pk) return $this;
		$pk = (int)$pk;
    	$db = sf::getLib("db");
		$sql = "SELECT * FROM `$this->table` WHERE `id` = '$pk' ";
		$query = $db->query($sql);
		if($db->num_rows($query)) $this->fillObject($db->fetch_array($query));
		return $this;
	}

	public function delete()
	{
		if(!$this->id) return false;
		$db = sf::getLib("db");
		$db->query("DELETE FROM `$this->table` WHERE `id` = '$this->id'");
		return $db->affected_rows();
	}

	public function isNew()
	{
		return $this->is_new;
	}
	
}