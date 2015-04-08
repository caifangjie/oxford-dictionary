<?php
require_once 'format.class.php';
require_once 'sql.class.php';

if(!empty($_GET['word']))
{
    $word = strtolower(trim($_GET['word']));
    $sql = new Sql();
    $sql->getId($word);
    $trans = $sql->getMeaning();
    echo $trans;
}

?>
