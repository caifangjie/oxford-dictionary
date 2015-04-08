<?php
header("content-type: text/html; charset=utf-8");
require_once "sphinxapi.php";

class Sphinx
{
	private $host="127.0.0.1";
	private $port=3312;
	private $sphinx=null;
	private $index = "cetsix";
	private $conn=null;
	private $word;
	
	public function __construct()
	{
		$this->sphinx = new SphinxClient();
		$this->sphinx->setServer("127.0.0.1", 3312);
		//精确匹配模式
		$this->sphinx->setMatchMode(SPH_MATCH_ANY);
		$this->conn = new mysqli("localhost", 'root', 'cai123');
		$this->conn->select_db("words");
	}
	
	public function getWord($chinese)
	{
		$result = $this->sphinx->query($chinese, $this->index);
		if(isset($result['matches']))
		{
			$items = array_keys($result['matches']);
			if(count($items) > 5)
			{
				$items = array_chunk($items,5)[0];
			}
			foreach($items as $id)
			{
				$item = $this->conn->query("select word from cetsix where id= ".$id);
				if($item->num_rows)
				{
					$this->word[]=$item->fetch_row()[0];
					$item->free();
				}
			}
			return $this->word;
		}
		else
		{
			return array('没有对应的解释');
		}
	}
}

/*  $sph = new Sphinx();
 echo "<pre>";
 print_r($sph->getWord('好的'));  */
?>
