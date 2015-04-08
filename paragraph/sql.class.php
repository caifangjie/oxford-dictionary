<?php
require_once 'redis.class.php';
require_once 'format.class.php';
require_once 'dict.class.php';
class Sql
{
    private $host="localhost";
    private $username="root";
    private $passwd="cai123";
    private $db="words";
    private $conn=null;

    private $errMsg=0;
    private $id;
	private $word;

    private $redis=null;

    public function __construct()
    {
         $this->conn=new mysqli($this->host, $this->username, $this->passwd) ;
            if(!$this->conn)
            {
                $this->errMsg = '连接数据库失败'.__LINE__;
                exit();
            }
            $this->conn->select_db($this->db);
            $this->conn->query("set names utf8");
            $this->redis = new RedisServer();
    }
    //根据单词去redis中寻找ID
    public function getId($word)
    {
        $temp = $this->redis->getIndex($word);
        if($temp ==="none")
        {
            $this->id="none";
			$this->word=$word;
        }
        else
        {

            $this->id = intval($temp, 10);
        }
    }
    //根据ID获取简明翻译
    public function getMeaning()
    {
        if($this->id !="none")
		{
			$sql = "SELECT meaning FROM cetsix WHERE id = ".$this->id;
			$result = $this->conn->query($sql);
			if($this->conn->affected_rows > 0)
			{
				$word = $result->fetch_row()[0];
				$result->free();
				return $word;
			}
			else
			{
				return "没有对应的翻译";
			}
		}
		else
		{
			//从网络获取
			$url = "http://dict.cn/".$this->word;
			$http = new HttpWrap();
			$http->init($url);
			$trans = $http->getBasicTrans();
			return $trans;
		}

    }
    //根据id获取例句
    public function getExt()
    {
        $sql = "SELECT lx FROM cetsix WHERE id = ".$this->id;
        $result = $this->conn->query($sql);
         if($this->conn->affected_rows > 0)
         {
             $lx = $result->fetch_row()[0];
            $result->free();
            return $lx;
         }
         else
         {
             return "没有对应的翻译";
         }

    }
    //获取简明翻译和例句，以字符串的形式返回
    public function getAllWithString()
    {
        $sql = "SELECT meaning, lx FROM cetsix WHERE id = ".$this->id;
        $result = $this->conn->query( $sql);
         if($this->conn->affected_rows > 0)
         {
             $items = $result->fetch_assoc();
             $meaning = $items['meaning'];
            $lx = $items['lx'];
            $result->free();
            return $meaning."---".$lx;
         }
         else
         {
             return "没有对应的翻译";
         }

    }
    //返回翻译和例句，以数组的形式返回
    public function getAllWithArray()
    {
        $sql = "SELECT meaning, lx FROM cetsix WHERE id = ".$this->id;
        $result = $this->conn->query( $sql);
        if($this->conn->affected_rows > 0)
         {
            $items = $result->fetch_assoc();
             $meaning = $items['meaning'];
            $lx = $items['lx'];
            $result->free();
            return array($meaning, $lx);
         }
         else
         {
             return "没有对应的翻译";
         }
    }
    public function __destruct()
    {
        $this->conn->close();
    }
}

//$sql = new Sql();
//$sql -> getId("able");
//$format = new Format();
//var_dump( $format->stripBreak($sql->getAllWithArray()));

?>