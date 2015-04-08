<?php 
require_once "sql.tool.php";
class Picture
{
	//上传文件
	private $upfile;
	//保存后的源文件
	private $img;
	//上传文件的类型
	private $fileType;
	//上传图片保存的路径
	private $path;
	//上传图片的名字
	private $fname;
	//缩略图的宽度
	private $width;
	//缩略图的高度
	private $height;
	
	//错误信息
	private $noticeMsg=null;
	private $sql;
	
	public function __construct($upfile)
	{
		$config = require "config.php";
		$this->width=$config['image']['width'];
		$this->height=$config['image']['height'];
		if(is_uploaded_file($upfile['tmp_name']))
		{
			$this->upfile = $upfile;
		}
		else
		{
			$this->noticeMsg = '非法文件';
		}
		$this->sql = new Sql();
		$this->moveFile();
		$this->zoomInPic();
		echo "<script> parent.document.getElementById('feedback').innerHTML='{$this->noticeMsg}'</script>";
	}
	private function checkFilesStatus()
	{
		//上传失败,分析原因
		if($this->upfile['error']>0)
		{
			switch($this->upfile['error'])
			{
				case 1:
					//超过表单定义的MAX_FILE_SIZE
					$this->noticeMsg = '文件太大';
					break;
				case 2:
					//超过系统定义,php.ini中设定的
					$this->noticeMsg = '文件太大';
					break;
				case 3:
					$this->noticeMsg = '文件不完整';
					break;
				case 4:
					$this->noticeMsg = '文件为空';
					break;
			}
			return 0;
		}
		else
		{//上传成功
			$this->noticeMsg = '上传成功';
			return 1;
		}
	}
	//中文转码
	private function transEncoding($data)
	{
		if(preg_match('#[\x{4e00}-\x{9fa5}]#ui', $data))
		{
			$file_name = preg_replace('#[\x{4e00}-\x{9fa5}]#ui',mt_rand(1,500).'_', $data );
			return $file_name;
		}
		else
		{
			return $data;
		}
	}
	//检测上传文件类型
	private function checkFileType()
	{
		$fileType = strtolower(substr(strchr($this->upfile['name'],'.'),1));
		//file_put_contents("up_file.txt", $this->upfile['name']."\r\n", FILE_APPEND);exit();
		$this->fileType = $fileType;
		$defaultType=array('jpg', 'jpeg', 'png', 'gif');
		if(!in_array($fileType, $defaultType)) 
		{
			$this->noticeMsg='文件类型不对';
			return 0;
		}
		return 1;
	}
	//保存上传文件
	private function moveFile()
	{
		if($this->checkFilesStatus() && $this->checkFileType())
		{
			$dirName = './upload/'.date("Ymd");
			if(!is_dir($dirName))
			{	
				mkdir($dirName,0777, true);
			}
			$toFileName = $dirName."/".time().mt_rand(1,500).$this->transEncoding($this->upfile['name']);
			$this->path = $dirName;
			//die($toFileName);
			if(!move_uploaded_file($this->upfile['tmp_name'], $toFileName))
			{
				die('移动文件失败');//系统错误
			}
			else
			{
				$this->img = $toFileName;
			}
		}
	}
	private function fileName()
	{
		//$fname = basename($this->upfile['name'], strchr($this->upfile['name'], '.'));
		$fname = basename(strtolower($this->img), '.'.$this->fileType);
		//die(stristr($this->img, '.'));
		//die($fname);
		$this->fname = time().mt_rand(1,500).$fname.'small';
	}
	//生成图片缩略图
	private function zoomInPic()
	{
		$src;
		$this->fileName();
		switch($this->fileType)
		{
			case 'jpg':
			case 'jpeg':
				$src = imagecreatefromjpeg($this->img);
				break;
			case 'gif':
				$src = imagecreatefromgif($this->img);
				break;
			case 'png':
				$src = imagecreatefrompng($this->img);
				break;
			
		}
		//获取上传图片的宽和高
		$src_w = imagesx($src);
		$src_h = imagesy($src);
		//生成缩略图
		//在属性里定义缩略图的宽和高，并将原图按照比例缩放
		//缩放算法是: 新宽/原宽 = 新高/原高,这样就能等比例缩放了.....
		if($src_w > $src_h)
		{
			$new_w = $this->width;
			$new_h = ceil(($this->width/$src_w)*$src_h);
		}
		else
		{
			$new_h = $this->height;
			$new_w = ceil(($this->height/$src_h)*$src_w);
		}
		//$new_h = $this->height;
		//$new_w = ceil(($this->height/$src_h)*$src_w);
		//$new_w = $this->width;
		
		$paint = imagecreatetruecolor($new_w, $new_h);
		imagecopyresampled($paint, $src, 0,0,0,0, $new_w, $new_h, $src_w, $src_h);
		//在原图上保存缩略图
		switch($this->fileType)
		{
			case 'jpg':
			case 'jpeg':
				$src = imagejpeg($paint, $this->path.'/'.$this->fname.'.'.$this->fileType);
				break;
			case 'gif':
				$src = imagegif($paint, $this->path.'/'.$this->fname.'.'.$this->fileType);
				break;
			case 'png':
				$src = imagepng($paint, $this->path.'/'.$this->fname.'.'.$this->fileType);
				break;
		}
		//释放资源
		imagedestroy($paint);
		sleep(2);
		unlink($this->img);
		//因为返回的是img链接资源，所以可以省略HEADER头部
		$this->noticeMsg="<img src=".$this->path.'/'.$this->fname.'.'.$this->fileType.">";
		$sources = $this->path.'/'.$this->fname.'.'.$this->fileType;
		$this->sql->setImage("$sources");
	}
	
}

?>