<?php
namespace Sofast\Support;
use Sofast\Core\Config;
use ReflectionClass;
use SessionHandlerInterface;
use PDO;
 
class MySessionHandler implements SessionHandlerInterface  
{  
    private $savePath;  
    private $pdo;//创建数据库连接  
  
    //打开session的时候会最开始执行这里。  
    public function open($savePath, $sessionName)  
    {
        //前面4行，是我们的数据库类来创建数据库连接，这样在其他几个函数就可以直接使用$pdo，操作数据库。  
        $pdo = new PDO(Config::get("session.dns"),Config::get("session.user"),Config::get("session.password"));  
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
        $st = $this->pdo->prepare("select data from sessions where skey = ?");  
        $st->bindParam(1,$id);  
        $st->execute();  
        while($Row = $st->fetch(PDO::FETCH_BOTH)) {  
            @$SessionData = @$Row['data'];  
        }  
        return (string)@$SessionData;  
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
        $st = $this -> pdo->prepare("delete  from sessions where skey=?");  
        $st->bindParam(1,$id);  
        $st->execute();  
        return true;  
    }  
  
    //回收session的时候，让用户下线。记录下线时间。清除超期session。不是每次都会执行。是一个概率问题。可以在php.ini中调节。默认1/100。概率是session.gc_probability/session.gc_divisor。默认情况下，session.gc_probability ＝ 1，session.gc_divisor ＝100，可在php.ini中修改  
    public function gc($maxlifetime)  
    {
        //回收的时候存入在线时长。修改在线状态。代码略  
        //清除过期session  
        $timeNow = time();  
        $st = $this -> pdo->prepare("delete from sessions where ($maxlifetime + lastvisit) < $timeNow");  
        $st->execute();  
        return true;  
    } 

}