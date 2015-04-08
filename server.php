<?php
require_once "readDic.class.php";
require_once "readDir.class.php";
require_once "storeWord.class.php";
require_once "groupDicName.class.php";

class Server
{
	private $redis=null;
	private $groupName=null;
	
	public function __construct($dir)
	{
		$this->groupName=new GroupDicName();
		$this->redis=new StoreWord();
		$this->parseDic($dir);
	}
	//获取目录名，形式： 'a'=>'E:\CodeEdit\php\ciba\TXT格式的牛津电子词典\牛津电子词典\a-b.txt'
	public function getDir($path)
	{
		 $dir=new Dir($path);
		 return $dir->getFileList();
	}
	
	public function parseDic($dir)
	{
		$path=$this->getDir($dir);
		$count_words_num=0;
                                             // 'a'=>'对应的路径'
                                            //  'b'=>'对应路径'
		foreach ($path as $wordZone => $dir)
		{
				foreach ($dir as $dicPath)
				{
					$oxf=new Oxford($dicPath);
					$res=$oxf->oxf();
                                                                                                               //获取分类名比如a-b, a-c
					$gname=$this->groupName->formatName($dicPath);
					$this->groupName->setIndex($dicPath);
					$this->redis->setWord($gname, $res);
					echo str_repeat("   ", 2048);
					echo '单词库入库:  '. $dicPath. "<br />";
					$count_words_num++;
					sleep(1);
				}
				echo '<font color="red">'.$wordZone.'分类存储完毕</font><br />';
			
		}
		echo'<font color="blue">所有分类存储完毕，累计'.$count_words_num.'个分类hash表</font><br />';
	}

}

set_time_limit(1000);
ob_implicit_flush(true);
$ser=new Server('E:\CodeEdit\php\ciba\TXT格式的牛津电子词典\牛津电子词典');

?>