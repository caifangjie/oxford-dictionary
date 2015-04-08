<?php 
class CheckCode
{
	private $defaultcode="abcdefghijklmnopqrstuvwyzBCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	private $codelen;
	private $image;
	private $width=60;
	private $height=30;
	private $code;
	private $font_size=20;
	private $dst_img;
	//生成伪随机4位验证码
	public function __construct()
	{
		//创建图片，并填充背景颜色
		$this->image = imagecreatetruecolor($this->width, $this->height);
		$bg_color = imagecolorallocate($this->image, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
		imagefill($this->image, 0,0, $bg_color);
		//获取随机字符，并保存到SESSION中
		$this->codelen = strlen($this->defaultcode);
		for($i=0; $i<4; $i++)
		{
			$index = mt_rand(0, $this->codelen);
			$this->code .=$this->defaultcode[$index];
		}
		session_start();
		$_SESSION['checkcode']=$this->code;
	}
	//划10条线
	private function line()
	{
		for($i=0; $i<10; $i++)
		{
			imageline($this->image, mt_rand(1, $this->width), mt_rand(1, $this->height),
									 mt_rand(1, $this->width), mt_rand(1, $this->height),
									mt_rand(120,220));
		}
	}
	//画点，50个雪花点
	private function dot()
	{
		for($i=0; $i<50; $i++)
		{
			imagesetpixel($this->image, mt_rand(1, $this->width),mt_rand(1, $this->height),mt_rand(0,255));
		}
	}
	//写字
	private function font()
	{
		$len = strlen($this->code);
		$font_size = imagefontwidth($this->font_size);
		$x=2;
		for($i = 0; $i<$len; $i++)
		{
			imagestring($this->image, $font_size, $x, mt_rand(5,15), $this->code[$i], mt_rand(100,255));
			$x = $x+14;
		}
	}
	private function merge()
	{
		$this->dst_img = imagecreatetruecolor($this->width, $this->height);
		$bg_color = imagecolorallocate($this->dst_img, 255, 255, 255);
		imagefill($this->dst_img, 0,0, $bg_color);
		
		imagecopymergegray($this->dst_img,$this->image, 0,0,0,0,$this->width, $this->height, mt_rand(20,50));
	}
	public function getImage()
	{
		$this->line();
		$this->dot();
		$this->font();
		$this->merge();
		header("content-type:image/jpeg");
		imagejpeg($this->dst_img);
		imagedestroy($this->image);
		imagedestroy($this->dst_img);
	}
}
$code = new CheckCode();
$code->getImage();
?>