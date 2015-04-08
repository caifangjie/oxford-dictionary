<?php
require_once "format.class.php";
class StoreWord
{
	private $redis=null;
	private $trans;
	public function __construct()
	{
		$this->redis=new Redis();
		$this->redis->connect('127.0.0.1', 6379);
		$this->redis->auth('caifangjie');
	}
	
	public function setWord($wordZone,$word)
	{
		$hName=strtolower($wordZone).':OFX';
		foreach ($word as $key => $value)
		{
			$this->redis->hSetNx($hName, $key,$value);
		}
	}
	public function getWord($wordZone,$key)
	{
		$hName=$wordZone.':OFX';
		if($this->redis->hExists($hName, $key))
		{
			return $this->redis->hGet($hName, $key);
		}
		else
		{
			return 0;
		}
	}
	
	public function getAllWord($wordZone)
	{
		$hName=$wordZone.':OFX';
		return $this->redis->hKeys($hName);
	}
	public function getNumWord($wordZone)
	{
		$hName=$wordZone.':OFX';
		return $this->redis->hLen($hName);
	}
}

/* $redis=new StoreWord();
//$redis->setWord('a', array('all'=>'全部，所有', 'about'=>'关于','above'=>'上面，上部'));
var_dump( $redis->getWord('m-a', 'make')); */
?>