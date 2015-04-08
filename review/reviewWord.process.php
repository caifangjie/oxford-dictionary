<?php 
header("content-type: text/html");
require_once "reviewWord.class.php";
if(!empty($_GET['type']))
{
	$type=$_GET['type'];
	$review = new ReviewWord();
	if($type==1)
	{
		$res['flag']=1;
		$res['result']=$review->getWord();
		echo json_encode($res);
	}
	else if($type==2)
	{
		$word=$_GET['word'];
		$res['flag']=2;
		$res['result']=$review->getTrans($word);
		echo json_encode($res);
	}
}