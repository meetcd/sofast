<?php
namespace Sofast\Support;
use Sofast\Core\Config;
use SessionHandlerInterface;
use PDO;

class MysqlSessionHandler implements SessionHandlerInterface  
{  
    private $savePath;  
    private $pdo;//创建数据库连接  
  
    //打开session的时候会最开始执行这里。  
    public function open($savePath, $sessionName)  
    {  
        $pdo = new PDO('mysql:host='.Config::get("database.host").';dbname='.Config::get("database.database").';charset='.Config::get("database.charset"),Config::get("database.username"),Config::get("database.password"));  
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);  
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
        $this->pdo = $pdo;  
        return true;  
    }  
  
    public function close()  
    {  
        return true;  
    }  
  
    //从数据库中读取Session数据  
    public function read($id)
    {  
        $SessionData = '';
		$st = $this->pdo->prepare("select data from sessions where skey = ?");  
        $st->bindParam(1,$id);  
        $st->execute();  
        while($Row = $st->fetch(PDO::FETCH_BOTH)) {  
            $SessionData = $Row['data'];  
        }
        return $SessionData;  
    }  
  
    //用户访问的时候存入新的session,或更新旧的session.  
    //同时读取session中的userid或adminid写入数据表。  
    public function write($id, $data)  
    {  
        $time = time();  
        $st = $this->pdo->prepare("select skey from sessions where skey =?");  
        $st->bindParam(1,$id);  
        $st->execute();  
        if(($Row = $st->fetch(PDO::FETCH_BOTH))){  
            $st = $this->pdo->prepare("update  sessions set data =?, lastvisit=? where skey=?");  
            $st->bindParam(1,$data);  
            $st->bindParam(2,$time);  
            $st->bindParam(3,$id);  
            $st->execute();  
        }else{  
            $st = $this -> pdo->prepare("insert into sessions (data,skey,lastvisit) values (?,?,?)");  
            $st->bindParam(1,$data);  
            $st->bindParam(2,$id);  
            $st->bindParam(3,$time);  
            $st->execute();  
        }  
        return true;  
    }  
  
    //session销毁的时候，从数据表删除。  
    public function destroy($id)  
    {  
        $st = $this -> pdo->prepare("delete from sessions where skey=?");  
        $st->bindParam(1,$id);  
        $st->execute();  
        return true;  
    }  
  	
	/**
	 * 回收函数
	 */  
    public function gc($maxlifetime)  
    {  
        //清除过期session  
        $timeNow = time();  
        $st = $this -> pdo->prepare("delete from sessions where lastvisit < '".($timeNow - $maxlifetime)."' ");  
        $st->execute();  
        return true;  
    }

}