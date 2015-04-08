<?php 
require_once "sql.tool.php";
if(!empty($_GET['op']))
{
	$sql = new Sql();
	if($_GET['op'] == 'hidden')
	{
		if(!empty($_GET['city_name']) && !empty($_GET['id']))
		{
			$city_name = $_GET['city_name'];
			$id = $_GET['id'];
			$sql->hiddenField($city_name, $id);
		}
	}
	else if($_GET['op'] == 'del')
	{
		$city_name = $_GET['city_name'];
		$id = $_GET['id'];
		$image_id = $_GET['image_id'];
		$sql->delField($city_name, $id);
		$sql->delImage($image_id);
	}
	else if($_GET['op']=='show')
	{
		$city_name = $_GET['city_name'];
		$id = $_GET['id'];
		$image_id = $_GET['image_id'];
		$sql->showField($city_name, $id, $image_id);
	}
	
}

?>