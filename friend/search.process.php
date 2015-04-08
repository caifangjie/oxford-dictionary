<?php 
header("content-type: text/html; charset=utf-8");
require_once 'check.class.php';
require_once 'sql.tool.php';
if(!empty($_GET['province']) && !empty($_GET['city']) && !empty($_GET['age']) && !empty($_GET['sex']) && !empty($_GET['tag']))
{
	$check = new Check();
	$province = $_GET['province'];
	$city = $_GET['city'];
	$age_list = $check->checkAge($_GET['age']);
	$sex = $_GET['sex'];
	$tag = implode(',',explode('__',$_GET['tag']));
	//file_put_contents("user_info.txt", $province.'---'.$city.'---'.$age.'---'.$sex.'---'.$tag."\r\n", FILE_APPEND);
	
	$sql = new Sql();
	$user_info = $sql->searchUserInfo($province, $city,$age_list, $sex, $tag);
	echo json_encode($user_info);
	//file_put_contents('user_info.txt', json_encode($user_info)."\r\n", FILE_APPEND);
}

?>