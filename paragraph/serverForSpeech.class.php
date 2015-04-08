<?php
require_once 'readSpeech.class.php';
require_once 'readDir.class.php';

class ServerForSpeech
{
    static public function  init()
    {
        $dir = new Dir('E:\CodeEdit\php\ciba\niujin-alpha\paragraph\data');
        $list = $dir->getFileList();
        $readSpeech = new ReadSpeech();
        foreach($list as $name => $path)
        {
            $readSpeech->setSpech($name, $path);
            if($readSpeech->errMsg !=0) die("导入失败");
        }
        echo "导入OK";
    }
}
ServerForSpeech::init();
?>
