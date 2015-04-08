<?php 
require_once "./libs/Smarty.class.php";
$smarty = new Smarty();
$smarty->template_dir="template";
$smarty->compile_dir="compile";
$smarty->left_delimiter="<{";
$smarty->right->delimiter="}>";

?>