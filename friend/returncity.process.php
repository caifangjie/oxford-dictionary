<?php
header("content-type: text/html; charset=utf-8");
require_once 'sql.tool.php';
$province = $_GET['province'];
$sql = new Sql();
$cities = $sql->select_city_by_province($province);
echo json_encode($cities);
?>