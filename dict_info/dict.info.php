<html>
<head>
</head>
<body bgcolor="#RGB(67,65,65)">
<img src="../image/logo.jpg" style='margin-left: 400px;'/><br />
<?php 
require_once "../groupDicName.class.php";
require_once "../storeWord.class.php";
$groupName = new GroupDicName();
$dicSort=trim($groupName->getIndex());
$storeword = new StoreWord();
//echo $dicSort; exit();
$words=explode('#', $dicSort);
$sortDict;
foreach($words as $word)
{
	$sortChar = substr($word, 0,1);
	$sortDict["$sortChar"][]=$word;
}
echo "<div style='margin-left: 403px; '>";
echo "<table border='1' style='width: 700px;'>";
echo "<tr><td colspan='3' style='color:#a00; padding-left: 300px;' >字典统计信息</td></tr>";
echo "<tr style='color: 003300;'><td>单词大类</td><td>单词分类</td><td>单词数量</td></tr>";
$totalNum=0;
foreach($sortDict as $subDict)
{
	$rowspan = count($subDict)+1;
	echo "<tr><td rowspan='$rowspan'>".substr($subDict[0],0,1)."</td></tr>";
	foreach($subDict as $word)
	{
		$totalNum += $storeword->getNumWord($word);
		echo "<tr><td><a href='wordList.php?wordlist=$word' target='_blank' title='点击查看具体单词' style='text-decoration: none; color: 0000ee'>$word</a></td><td>".$storeword->getNumWord($word)."</td></tr>";
	}
}
echo "<tr><td colspan='2'>附注:<span style='color:e00'>(-)</span> 表示起始<span style='color:e00'>(,)</span>表示包含范围 <span style='color:e00'>(~)</span>表示单词的跨度</td><td>单词总数<span style='color:e00'>($totalNum)</span></td></tr>";
echo "</table>";
?>
</body>
</html>