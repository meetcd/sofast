<?php
namespace Sofast\Support;
use Sofast\Core\Sf;
class Model
{	
	public function __construct(){}
	
	public function __clone()
	{
		return $this->cleanObject();
	}
	
	public function selectAll($addWhere = '',$addSql = '',$showMax = 0,$select = '')
	{
		$db = sf::getLib("db");
		
		if($select) $sql = $select." ";
		else $sql = "SELECT * FROM ".$this->table." ";
		$addWhere && $sql .= "WHERE ".$addWhere." ";
		$addSql && $sql .= $addSql;
		$showMax && $sql .= ' LIMIT '.$showMax;
		
		$query = $db->query($sql);
		
		return sf::getLib("collection",clone $this,$db->result_array($query));
	}
	
	public function getPager($addWhere = '',$addSql = '',$showMax = 20,$select = '',$key = '',$form_vars=array())
	{
		$db = sf::getLib("db");
		
		if($select) $sql = $select." ";
		else $sql = "SELECT * FROM `".$this->table."` ";
		$addWhere && $sql .= "WHERE ".$addWhere." ";
		$addSql && $sql .= $addSql." ";
		
		if(!router::get("totalnum".$key)){
			$_sql = "SELECT COUNT(*) AS NUM FROM `".$this->table."` ";
			$addWhere && $_sql .= "WHERE ".$addWhere." ";
			$addSql && $_sql .= $addSql." ";
			$row = $db->fetch_first($_sql);
			$total = $row['NUM'];
		}else $total = router::get("totalnum".$key);
		
		$pager = sf::getLib("pager",$total,$showMax,$key,$form_vars);
		$sql .= "LIMIT ".$pager->getStartNum().",".$pager->getShowNum();
		$query = $db->query($sql);
		
		$pager->setField($db->result_array($query));
		$pager->setObject(clone $this);
		
		return $pager;
	}
	
	public function getJson($addWhere = '',$addSql = '',$showMax = 12)
	{
		$sort = input::getInput("mix.sort") ? input::getInput("mix.sort") : 'id';
		$order = input::getInput("mix.order") ? input::getInput("mix.order") : "asc";
		$page = input::getInput("mix.page") ? input::getInput("mix.page") : 1;
		$rows = input::getInput("mix.rows") ? input::getInput("mix.rows") : 10;
		
		$db = sf::getLib("db");
		
		if($select) $sql = $select." ";
		else $sql = "SELECT * FROM `".$this->table."` ";
		$addWhere && $sql .= "WHERE ".$addWhere." ";
		if($addSql) $sql .= $addSql." ";
		else $sql .= " ORDER BY $sort $order ";

		if(!router::get("totalnum".$key)){
			$query = $db->query($sql);
			$total = $db->num_rows($query);
		}else $total = router::get("totalnum".$key);
		
		$sql .= "LIMIT ".($rows*($page-1)).",".$rows;
		$query = $db->query($sql);
		
		while($row = $db->fetch_array($query)){
			$result[] = $this->fillObject($row)->toArray();	
		}
		return '{"total":'.$total.',"rows":'.json_encode($result).'}';
	}
	
	public function validate(){}//验证函数
	
}
?>