<?php
require_once "../sql.class.php";
if(empty($_COOKIE['user']))
{
	echo "<script> alert('你没有查询历史')</script>";
}
else
{
	$sql = new Sql($_COOKIE['user']);
	$sql->clearHistory();
	
	echo "<script> alert('你的查询历史已经被清空');</script>";
	
}
?>