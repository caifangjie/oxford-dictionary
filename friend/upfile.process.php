<?php
require_once "upfile.class.php";
require_once "sql.tool.php";
require_once "check.class.php";
$check = new Check();
if($_POST['uploadtype']=="userpic")
{
	if(!empty($_FILES['pic']['name']))
	{
		$picture = new Picture($_FILES['pic']);
	}
}
else if($_POST['uploadtype']=="userinfo")
{
	$sql = new Sql();
	if(!empty($_POST['username']) && !empty($_POST['sex']) &&
                !empty($_POST['age']) && !empty($_POST['qq']) &&
                !empty($_POST['area']) && !empty($_POST['city']) &&
                !empty($_POST['tag']) && !empty($_POST['intro']))
	{
		$username = $check->checkUser($_POST['username']);
		$sex = $_POST['sex'];
		$age = $check->checkAge($_POST['age']);
		$qq = $check->checkQq($_POST['qq']);
		$province = $_POST['area'];
        $city = $_POST['city'];
        $tag = $_POST['tag'];
        $intro = $check->checkIntro($_POST['intro']);
		
		$sql->setInfo("$username", "$sex", $age, "$qq", "$province","$city",$tag,"$intro");
		if($sql->errMsg ==0)
		{
			header("Location: homepage.php?cityname=wuhan");
			exit();
		}
		else
		{
			echo $sql->errMsg;
		}
	}
	else
	{
		echo "<script>alert('输入不能为空'); history.go(-1)</script>";
	}
}
?>