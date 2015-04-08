<?php
require_once 'readDir.class.php';
require_once "storeWord.class.php";
class GroupDicName
{
	private $fileName='groupname';
	private $redis=null;
	
	public function __construct()
	{
		$this->redis=new Redis();
		$this->redis->connect('127.0.0.1', 6379);
		$this->redis->auth('caifangjie');
	}
	//提取不带.txt扩展名的文件名
	public function getGroupName($dir)
	{
		return basename(strtolower(substr($dir, strrpos($dir,'/')+1)),'.txt');
	}

	//在将a-z相应开头的单词写入redis时，根据相应的分类名，依次写入各个相应的hash表
	//比如absolute会写入redis缓存的a-b:OFX的hash表
	//根据单词开头的两个字符进行单词的分类，有利于在查询时的想对精确匹配
	public function formatName($dir)
	{
		return $this->getGroupName($dir);
	}
	//设置单词的分类索引，定位redis缓存服务器相应的hash表
	//第一次设置索引时，如果索引不存在则创建，并同时设置这个索引
	//当索引存在时，就不创建，而是直接追加
	public function setIndex($dir)
	{
		$bname=$this->getGroupName($dir);
		if(!$this->redis->exists('index'))
		{
			$this->redis->set('index', '#'.$bname);
		}
		else
		{
			$this->redis->append('index', '#'.$bname);
		}
	}
	
	public function getIndex()
	{
		$index = $this->redis->getRange('index',1,-1);
		return $index;
	}
	
}

/* $redis = new GroupDicName();
echo "<pre>";
print_r($redis->getIndex()); */
?>