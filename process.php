<?php
require_once "storeWord.class.php";
require_once "filterword.class.php";
require_once "format.class.php";
require_once "processCheck.class.php";
require_once "cookie.php";
require_once "sql.class.php";
require_once "./addword/addredis.class.php";
require_once "sphinx.class.php";

header("Content-type: plain/text; charset=utf-8");
header("Cache-control:no-store");
if(!empty($_GET['enword']))
{
	$en=strtolower(trim($_GET['enword']));
	$filter = new Filter($en);
	$en=$filter->getResult();
	
	$check = new Check();
	$translation = new FormatTrans;
	
	//返回ajax查询结果
	if($_GET['type']==1)
	{
                                            //如果查询英文单词
		if(ctype_alpha($en))
		{
			$redis=new StoreWord();
			$addredis = new AddRedis();
			$wordZone=$check->matchDic($en);
			
			//将查询过的单词根据cookie写入到数据库
			$username=setOrGetUser();
			$sql = new Sql($username, $en);
			
			$ch=$redis->getWord($wordZone,$en);
			$res['flag']=1;
			
			$trans='';
			if($ch===0)
			{
				//从redis中找到用户添加的单词对应的ID，并根据此ID从MySQL中找到用户添加的词条对应的翻译
				$chinese = $addredis->getTrans($en);
				$trans = ($chinese == 0 ? array("没有找到对应的翻译") : $chinese);
				//$trans = array("没有找到对应的翻译");
			}
			else
			{
				$trans = $translation->trans($ch);
			}
				$res['result']=$trans;
				echo json_encode($res);
		}
                //如果查询中文
		else
		{
			$sphinx = new Sphinx();
			$trans = $sphinx->getWord($en);
			$res['flag']=1;
			$res['result']=$trans;
			//file_put_contents("ch.txt", $en."---".implode("--", $trans), FILE_APPEND);
			echo json_encode($res);
		}
	}
	
	//返回ajax自动补全集
	else if($_GET['type']==2)
	{
		$matchWord=$check->ajaxMatch($en);
		//file_put_contents("word.txt", $en."---".serialize($matchWord)."\r\n", FILE_APPEND);
		$res['flag']=2;
		$res['result']=$matchWord;
		echo json_encode($res);
	}
}
else
{
	file_put_contents('log.txt', "receive NON data \r\n",FILE_APPEND);
}