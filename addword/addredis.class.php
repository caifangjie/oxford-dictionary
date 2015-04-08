<?php 
class AddRedis
{
	private $redis=null;
	private $host="localhost";
	private $username="root";
	private $passwd="cai123";
	private $db="words";
	private $conn=null;
	
	public function __construct()
	{
		$this->redis=new Redis();
		$this->redis->connect('127.0.0.1', 6379);
		$this->redis->auth('caifangjie');
		
		$this->conn=new mysqli($this->host, $this->username, $this->passwd) or die('连接mysql失败'.mysql_error);
		$this->conn->select_db($this->db);
		$this->conn->query("set names utf8");
	}
	//把添加的单词作为键，last_insert_id作为值，插入到redis
	public function setIndex($wordzone, $word,$num_index)
	{
		$zone = $wordzone."::USER";
		$this->redis->hSetNx($zone, $word, $num_index);
	}
	//从redis中获取last_insert_id，然后去数据库查询用户添加的单词
	public function getIndex($word)
	{
		$zone = strtolower(substr($word, 0,1))."::USER";
		if($this->redis->hExists($zone, $word))
		{
			return $this->redis->hGet($zone, $word);
		}
		else
		{
			return 0;
		}
	}
	public function getTrans($word)
	{
		//从获取ID，去数据库中查询单词
		$id = $this->getIndex($word);
		if($id != 0)
		{
			$table = strtolower(substr($word, 0,1));
			$sql = "SELECT trans FROM {$table} WHERE id = {$id}";
			$res = $this->conn->query($sql);
			if($this->conn->affected_rows)
			{
				$trans = $res->fetch_row()[0];
				$res->free();
				return array($trans);
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return 0;
		}
	}
}

/*  $word = new AddRedis();
var_dump($word->getTrans("makebbb"));  */
?>