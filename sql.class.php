<?php
//require_once "cookie.php";
class Sql
{
	private $host="localhost";
	private $username="root";
	private $passwd="cai123";
	private $db="words";
	private $conn=null;

	private $uid=null;
	//根据user表的ID主键，关联到对应的DIC的UID字段里，进行查询单词的保存
	public function __construct($cookiename='',$word='')
	{
		$this->conn=new mysqli($this->host, $this->username, $this->passwd) or die('连接mysql失败'.mysql_error);
		$this->conn->select_db($this->db);
		$this->conn->query("set names utf8");

		$sql="SELECT id FROM user WHERE username='$cookiename' ";
		$result = $this->conn->query($sql);
		if($this->conn->affected_rows)
		{
			$this->uid = $result->fetch_row()[0];
			$result->free();
		}
		else
		{
			die("没有关于你的信息");
		}
		if($word!='')
		{
			$this->setWord($word);
		}
	}
	//将用户的查询记录保存到数据库
	public function setWord($word)
	{
		$sql="INSERT INTO dic(uid,word) VALUES($this->uid,'$word')";
		$this->conn->query($sql);
		if($this->conn->affected_rows)
		{
			//file_put_contents("word.txt", "插入单词{$word}成功",FILE_APPEND);
		}
		else
		{
			file_put_contents("word.txt", "插入单词{$word}失败",FILE_APPEND);
		}
	}
	public function getOneWord()
	{
		$sql = "SELECT word FROM dic WHERE uid={$this->uid} LIMIT 1";
		$result=$this->conn->query($sql);
		if(!$this->conn->errno)
		{
			$res=$result->fetch_row()[0];
			$result->free();
			return $res;
		}
		else
		{
			return false;
		}
	}
	public function getAllWord()
	{
		$sql = "SELECT word FROM dic WHERE uid={$this->uid} ";
		$result=$this->conn->query($sql);
		if($this->conn->affected_rows)
		{
			while($res = $result->fetch_row())
			{
				$words[]=$res[0];
			}
			$result->free();
			return array_unique($words);
		}
		else
		{
			return array("你没有查询历史");
		}
	}
	//根据USER表中保存的时间戳字段，当超过一段时间后，删除对应的查询单词保存记录
	public function deleteWord()
	{
		$sql = "DELETE FROM dic WHERE uid=".$this->uid ;
		$this->conn->query($sql);
	}
	public function clearHistory()
	{
		$sq01 = "DELETE FROM user WHERE id = ".$this->uid; 
		$sq02 = "DELETE FROM dic WHERE uid= ".$this->uid;
		$this->conn->query($sq02);
		$this->conn->query($sq01);
	}
	public function __destruct()
	{
		$this->conn->close();
	}
}

/* $sql = new Sql("MTI3LjAuMC4x");
$sql->setWord("hello"); */