<html>

<body bgcolor="#RGB(67,65,65)">
<img src="../image/logo.jpg" style='margin-left: 400px;' /><br />
<?php
require_once "../sql.class.php";
require_once "../rollpage/rollpage.class.php";
if(empty($_COOKIE['user']))
{
	die("你没有查询历史");
}
else
{
	$username = $_COOKIE['user'];
	$sql = new Sql($username);
	if($words = $sql->getAllWord())
	{
		//将数据传给分页类进行分页
		$href = $_SERVER['PHP_SELF'];
		$page = new Rollpage($words, $href);
		$page->getLink();
	}
	else
	{
		die("你没有查询历史");
	}
}
?>
</body>
</html>