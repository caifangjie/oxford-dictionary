<?php
require "/libs/Smarty.class.php";
error_reporting(E_ERROR | E_PARSE);
$config = new stdclass();
$config->smarty = new Smarty();
$config->smarty->template_dir="template";
$config->smarty->compile_dir="compile";
$config->smarty->left_delimiter = '<{';
$config->smarty->right_delimiter = '}';

?>