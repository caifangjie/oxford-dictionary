<?php
class Oxford
{
	private $OfileName=null;
	private $ODicString='';
	private $ODicUnit=array();
	private $ODicWord=array();
	private $ODicTrans=array();
	private $ODicEncoding=null;
	
	public function __construct($fname)
	{
		$encode=mb_detect_encoding($fname, array('GB2312','GBK','UTF-8','BIG5','LATIN1'));
		$this->OfileName=mb_convert_encoding(trim($fname), 'GB2312', $encode);

		$temp=file_get_contents($this->OfileName, false, null, 0, 64);
		$this->ODicEncoding=mb_detect_encoding($temp, array('GB2312','GBK','UTF-8','BIG5','LATIN1'));
		
	}
	
	//读取文件并保存到$OfileName中
	protected function readDicFromFile()
	{
		if(!file_exists($this->OfileName))
		{
			die('文件不存在'.__LINE__);
		}
		if(!is_readable($this->OfileName))
		{
			die('文件不可读'.__LINE__);
		}
		$fp=fopen($this->OfileName, 'r') or die('打开文件失败'.__LINE__);
		
		while(!feof($fp))
		{
			$this->ODicString .= fread($fp, 10240);
		}
		fclose($fp);
                //当字符信息不是UTF-8时，自动进行转换
		if(strtoupper($this->ODicEncoding)!='UTF-8')
		{
			$this->ODicString=mb_convert_encoding($this->ODicString, 'UTF-8', $this->ODicEncoding);
		}
		
	}
	//根据音标进行分割并保存到ODicUnit单元中，保存为数组
        //音标的右边必须是回车换行符，左边必须是空格 ， 音标是包括在//中
	protected  function splitWithVoice($pattern='#(?<=\r\n)/[^/]+?/(?=\s)#ui')
	{
		$this->ODicUnit=preg_split($pattern, $this->ODicString);
	}
        
	//从第二个到倒数第二个单元里面，保存的都是上一个单词的翻译，和下一个单词
	//根据音标分割成单元后，第一个单元里保存的是第一个单词，最后一个单元里则保存的是最后一个翻译
	//并且单词是结尾，单词和上一个单词的翻译之间肯定是存在回车换行符的，根据这个特征，提取出单词
	//所以依次从第二个单元到倒数第二个单元里，根据特征提取出单词。
	protected  function grepWord()
	{
		//提取首单词
		$this->ODicWord[]=trim(strtolower($this->ODicUnit[0]));
		
		$pat='#\r\n(.*)(?:\r\n)$#i'; //提取单词
		$len=count($this->ODicUnit);
		
		for($i=1; $i<$len-1; $i++)
		{
			if(preg_match($pat, $this->ODicUnit[$i], $match))
			{
				$this->ODicWord[]=trim(strtolower($match[1]));
			}
			else
			{
				die('匹配单词失败'.__LINE__."<br />");
			}
		}
	}
	
	//从第二个到倒数第二个单元里面，保存的都是上一个单词的翻译，和下一个单词
	//根据音标分割成单元后，第一个单元里保存的是第一个单词，最后一个单元里则保存的是最后一个翻译
	//在每个单元中反向搜索单词第一次出现的位置，根据这个位置可以提取出上一个单词的翻译
	protected  function grepTrans()
	{
		$len=count($this->ODicUnit);
		for($i=1; $i<$len-1; $i++)
		{
			if($pos=strripos($this->ODicUnit[$i], $this->ODicWord[$i]))
			{
				array_push($this->ODicTrans,substr($this->ODicUnit[$i], 0, $pos));
			}
			else
			{
				die("此方法不可行".__LINE__."<br />");
			}
			
		}
                //将最后一个翻译入栈
		array_push($this->ODicTrans,$this->ODicUnit[$len-1]);
	}

        //用户接口
	public function oxf()
	{
		$this->readDicFromFile();
		$this->splitWithVoice();
		$this->grepWord();
		$this->grepTrans();
		
		
		$len=count($this->ODicWord);
		$oxfWord=array();
		for($i=0; $i<$len; $i++)
		{
			$oxfWord[$this->ODicWord[$i]] = $this->ODicTrans[$i];
			//echo $this->ODicWord[$i]."<br />";
		}
		return $oxfWord;
		//return $this->ODicUnit;
		
	}

}//class Oxford

/* $oxf=new Oxford(' E:/CodeEdit/php/ciba/TXT格式的牛津电子词典/牛津电子词典/A/A-c.txt ');
$res=$oxf->oxf();
echo count($res)."<br /><br />";
 echo "<pre>";
print_r($res); */
