<?php 
header("content-type: text/html; charset=utf-8");
require_once 'sql.tool.php';
$ads_num = $_GET['ads_num'];
$sql = new Sql();
$ads = $sql->selectAds($ads_num+1);
//file_put_contents("ads.txt", $ads_num.'----'.json_encode($ads)."\r\n", FILE_APPEND);exit();
echo json_encode($ads);

?>