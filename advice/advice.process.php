<?php 
require_once "../friend/sql.tool.php";
if(!empty($_POST['code']) && !empty($_POST['adv']))
{
	$code = $_POST['code'];
	session_start();
	if(strtolower($code) != strtolower($_SESSION['checkcode']))
	{
		echo "<script>alert('验证码错误'); history.go(-1);</script>";
	}
	else
	{
		$adv = $_POST['adv'];
		$sql = new Sql();
		$sql->setAdvice($adv);
		echo "<script>alert('感谢你的反馈'); history.go(-1);</script>";
	}
}

?>