<?php
require_once "../sql.class.php";
require_once "../processCheck.class.php";
require_once "../storeWord.class.php";
require_once "../format.class.php";
class ReviewWord
{
	private $cookiename=null;
	private $words=null;
	private $check=null;
	private $storeword=null;
	private $trans;
	public function __construct()
	{
		if(empty($_COOKIE['user']))
		{
			die("你没有查询历史");
		}
		else
		{
			$this->cookiename = $_COOKIE['user'];
			
		}
		$sql = new Sql($this->cookiename);
		$this->check = new Check();
		$this->storeword=new StoreWord();
		$this->trans = new FormatTrans();
		if(!($this->words = $sql->getAllWord())) die('你没有查询史');
	}
	public function getWord()
	{
			$key = array_rand($this->words, 1);
			return $this->words[$key];
	}
	public function getTrans($word)
	{
		$wordzone = $this->check->matchDic($word);
		$trans = $this->trans->trans($this->storeword->getWord($wordzone, $word));
		return $trans;
	}
}
?>