<?php 
require_once "addredis.class.php";
class Addword
{
	private $host="localhost";
	private $username="root";
	private $passwd="cai123";
	private $db="words";
	private $conn=null;
	
	public function __construct()
	{
		$this->conn=new mysqli($this->host, $this->username, $this->passwd) or die('连接mysql失败'.mysql_error);
		$this->conn->select_db($this->db);
		$this->conn->query("set names utf8");
	}
	public function setword($word, $trans)
	{
		$wordzone = substr($word,0,1);
		$sql = "SHOW TABLES LIKE '$wordzone' ";
		$this->conn->query($sql);
		$last_id=null;
		//表示此分类表已经存在，所以不用创建
		if($this->conn->affected_rows)
		{
			$sql = "SELECT * FROM $wordzone WHERE word='$word' ";
			$ifExists = $this->conn->query($sql);
			//只有当待插入的单词在表中不存在时
			if(!$this->conn->affected_rows)
			{
				$sql = "INSERT INTO $wordzone(word, trans) VALUES('$word', '$trans')";
				$this->conn->query($sql);
				//获取插入最近一次插入数据库的ID
				$res=$this->conn->query("SELECT LAST_INSERT_ID() FROM $wordzone");
				$last_id = $res->fetch_row()[0];
				$res->free();
				if(!$this->conn->affected_rows)
				{
					die("插入词条失败");
				}
			}
			else
			{
				$ifExists->free();
				die($word."已经存在于".$wordzone."表中，暂时禁止更新");
			}
			
		}
		else
		{	//当wordzone表不存在时，创建
			$sql = "CREATE TABLE $wordzone(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, word VARCHAR(32) NOT NULL DEFAULT '', trans VARCHAR(128) NOT NULL DEFAULT '')charset utf8";
			$this->conn->query($sql);
			
			$sql = "INSERT INTO $wordzone(word, trans) VALUES('$word', '$trans')";
			$this->conn->query($sql);
			$res=$this->conn->query("SELECT LAST_INSERT_ID() FROM $wordzone");
			$last_id = $res->fetch_row()[0];
			$res->free();
			if(!$this->conn->affected_rows)
			{
				die("插入词条失败");
			}
			
		}
		/*
		*插入单词时可以返回wordzone( word（key）  last_insert_id(value)),并插入redis缓存，用以索引
		* redis->setIndex($wordzone, $word, $last_id);
		*/
		if($last_id)
		{
			$redis = new AddRedis();
			$redis->setIndex($wordzone, $word, $last_id);
		}
	}
	/*
	public function getword($word)
	{
		$word = trim($word);
		$wordzone=substr($word, 0,1);
		$sql = "SHOW TABLES LIKE '$wordzone' ";
		$ifExists = $this->conn->query($sql);
		//表示此分类表已经存在，所以不用创建
		if($this->conn->affected_rows)
		{
			$ifExists->free();
			$sql = "SELECT trans FROM $wordzone WHERE word='$word' ";
			$trans = $this->conn->query($sql);
			if($this->conn->affected_rows)
			{
				$res = $trans->fetch_row()[0];
				$trans->free();
				return array($res);
			}
			else
			{
				return array($wordzone."表中不存在单词".$word);
			}
			
		}
		else
		{
			return array("不存在".$wordzone."表，无法获取".$word."的翻译");
		}
	}*/
}
/* 
$word = new Addword(); */

?>