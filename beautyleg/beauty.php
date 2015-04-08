<?php
require_once "rollpage.class.php";
//include "config.inc.php";

$href = $_SERVER['PHP_SELF'];

$rollpage = new Rollpage($href);
$result= $rollpage->getResult();
echo "<body bgcolor='#RGB(67,65,65)'><div style='margin-left:200px;'>";
foreach($result['data'] as $link)
{
    echo '<img src="'.$link.'">'."<br /><br />";
}
//$smarty->assign("image", $result['data']);
//$smarty->assign("nagv", $result['nagv']);
//$smarty->display("homepage.htm");
//显示分页导航
echo "<div style='margin-left:320px;'>".$result['nagv']."</div>";
echo "</div>";
?>
