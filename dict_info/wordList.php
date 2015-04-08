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
if(!empty($_GET['wordlist']))
{
	$wordGroup = $_GET['wordlist'];
	$storeword = new StoreWord();
	$words = $storeword->getAllWord($wordGroup);
	echo "<div style='margin-left: 403px; '>";
	echo "<table border='1' style='width: 715px;'>";
	$len = count($words);
	for($i=0; $i<$len; $i=$i+4)
	{
		$one = isset($words[$i])?$words[$i]:'';
		$two = isset($words[$i+1])?$words[$i+1]:'';
		$three = isset($words[$i+2])?$words[$i+2]:'';
		$four = isset($words[$i+3])?$words[$i+3]:'';
		echo "<tr><td><a href='trans.php?word={$one}' target = '_blank' style='text-decoration: none'>{$one}</a></td><td><a href='trans.php?word={$two}' target = '_blank'  style='text-decoration: none'>{$two}</a></td><td><a href='trans.php?word={$three}' style='text-decoration: none' target = '_blank' >{$three}</a></td><td><a href='trans.php?word={$four}' style='text-decoration: none' target = '_blank' >{$four}</a></td></tr>";
	}
	echo "</table>";
}
?>
</html>