<?php
class Dir
{
	private $fileList=array();
	private $line_count=0;
	
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
					if(is_file($path.'/'.$fileName) && $fileName!='sphinxapi.php')
					{	
						$extName=pathinfo($path."/".$fileName)["extension"];
						if(strtolower($extName)=='html' || strtolower($extName)=='php')
						{
							//上面把路径转成了GB2312，这里再转换会UTF-8编码
							$temp=mb_convert_encoding($path.'/'.$fileName, 'UTF-8', $encode);
							//$groupName=$this->groupFile($temp);
							$this->fileList[]=$temp;
						}
					}
					//如果是一个目录，则继续递归读取
					else if(is_dir($path.'/'.$fileName) && $fileName !='sphinx' && $fileName !='libs')
					{
						$this->readFileList($path.'/'.$fileName);
					}
				}	
			
			}

		}
		@closedir($fd);
		
	}
	
	public function getFilelist()
	{
		return $this->fileList;
	}
	public function getCount()
	{
		foreach($this->fileList as $file_list)
		{
			$str  = file_get_contents($file_list);
			$this->count_line($str,$file_list);
		}
		echo '<font color="red">累计'.$this->line_count.'行代码</font>';
	}

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
	
	public function count_line($content,$file_list)
	{
		if(preg_match_all("#\r\n#i", $content, $lines))
		{
			$this->line_count += count($lines[0]);
			echo $file_list.'文件有'.count($lines[0])."行代码<br />";
		}
		else
		{
			die("no matches items");
		}
	}
	
}
$dir=new Dir('E:\CodeEdit\php\ciba\niujin-beta');
//echo "<pre>";
//print_r($dir->getFilelist());
$dir->getCount();
?>