<html>
<head>
<style type="text/css">
td{text-align:center;color: #000000;}
</style>
</head>
<body bgcolor="#RGB(67,65,65)">
<img src="../image/logo.jpg" style='margin-left: 400px;'/><br />
<?php
require_once "../storeWord.class.php";
require_once "../processCheck.class.php";
require_once "../format.class.php";
if(!empty($_GET['word']))
{
	$word = $_GET['word'];
	$check = new Check();
	$store = new StoreWord();
	$trans = new FormatTrans();
	$wordzone = $check->matchDic($word);
	$trans =$trans->trans($store->getWord($wordzone, $word));
	echo "<div style='margin-left: 410px; width: 715px; '>";
	foreach ($trans as $translation)
	{
		echo "<p  style='background-color: #acacac; border:1px solid grey; width: 650px ; padding-left: 15px; text-indent: 18px;'>{$translation}</p>";
	}
	echo "</div>";
}
?>
</body>
</html>