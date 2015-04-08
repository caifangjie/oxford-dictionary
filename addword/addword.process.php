<?php 
require_once "addword.class.php";
require_once "../processCheck.class.php";
require_once "../storeWord.class.php";
header("content-type: text/html");
if( !empty($_GET['word']) && !empty($_GET['trans']))
{
		
	$word = trim($_GET["word"]);
	$trans = trim($_GET["trans"]);
	if(!preg_match('#[a-z]+#', $word)) die("添加的单词只能是英文");
	
	//判断添加的词条是否已经存在
	$check = new Check();
	$redis = new StoreWord();
	$wordzone = $check->matchDic($word);
	if($redis->getWord($wordzone, $word)!= 0)
		die($word."已经存在redis数据库中，禁止添加");
	
	//判断提交的词条对应的翻译是否超过128，如果超过128则截断；
	$string='';
	if(strlen($trans)>128)
	{
		for($i=0; $i<128; $i++)
		{
			if(ord(substr($trans, $i, 1))>0xa0)
			{
				$string .= substr($trans, $i, 3);//因为是以UTF-8编码保存的
				$i++;$i++;
			}
			else
			{
				$string .= $trans[$i];
			}
		}
		$trans = $string;
	}
	if(strlen($word)>32) $word=substr($word, 0,32);
	$words = new Addword();
	$words->setword($word, $trans);
	$red = dechex(mt_rand(0,255)); 
	$green = dechex(mt_rand(0,255)); 
	$blue = dechex(mt_rand(0,255)); 
	$color="#".$red.$green.$blue;
	echo "<span style='color:$color'>添加词条成功</span>";
}
else
{
	echo "添加词条失败".basename(__FILE__)." ::". __LINE__;
}
?>