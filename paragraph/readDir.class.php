<?php
class Dir
{
private $fileList=array();

public function __construct($path)
{
        $this->readFileList($path);
}

function readFileList($path)
{
    $path=$this->transPathSep($path);
    $encode=mb_detect_encoding($path, array('GB2312','GBK','UTF-8','BIG5','LATIN1'));
    $path=mb_convert_encoding($path, 'GB2312', $encode);
    //用于路径读取时用UTF编码会失败，所以先转成GB2312
    if ($fd=opendir($path))
    {
            while($fileName=readdir($fd))
            {
                    //如果不是当前目录和上级目录
                    if($fileName !="." && $fileName !="..")
                    {
                            //如果是一个文件
                            if(is_file($path.'/'.$fileName))
                            {
                                    $extName=pathinfo($path."/".$fileName)["extension"];
                                    if(strtolower($extName)=='txt')
                                    {
                                            //上面把路径转成了GB2312，这里再转换会UTF-8编码
                                            $temp=mb_convert_encoding($path.'/'.$fileName, 'UTF-8', $encode);
                                            $groupName=$this->groupFile($temp);
                                            $this->fileList[$groupName]=$temp;
                                    }
                            }
                            //如果是一个目录，则继续递归读取
                            else if(is_dir($path.'/'.$fileName))
                            {
                                    $this->readFileList($path.'/'.$fileName);
                            }
                    }

            }

    }
        @closedir($fd);

}

public function getFileList()
{
        return $this->fileList;
}
//提取单词分类，比如从A-Z
private function groupFile($filename)
{
        $pos=strtolower(strripos($filename, '/'));
        $word=trim(basename(substr($filename, $pos+1),'.txt'));
        return $word;
}
	//转换window环境下路径的默认分隔符\为PHP识别更好的/
	//因为路径名中包含汉字时，必须转换为gb2312时，php才能识别
	//而在转换为gb2312后，如果路径名是以windows下的\分隔的，则被转换为gb2312的中文会被转义
	//最后就会导致读取对应的路径失败
	//******所以在{路径名}中包含中文时，一定要先转换路径分隔符，然后转换为gb2312编码
	private function transPathSep($path)
	{
		$system=$_SERVER["SERVER_SOFTWARE"];
		$pat="#\((.*?)\)#";
		$sysVer=null;
		if(preg_match($pat,$system,$match))
		{
			$sysVer=$match[1];
		}
		else
		{
			die("匹配系统类型失败<br />");
		}
		if(strtolower($sysVer)=="win32")
		{
			$realPath=str_replace("\\","/",$path);
			return $realPath;
		}
	}

}

/* $dir=new Dir('E:\CodeEdit\php\ciba\niujin-alpha\paragraph\data');
$list=$dir->getFileList();
echo "<pre>";
foreach ($list as $name => $path)
{
	echo $name.'-----'.mb_strlen($name).'------'.$path.'------'.mb_strlen($path)."<br />";
}
echo "</pre>";  */

?>