<?php
class Filter
{
	private $keyword=array('fuck', 'shit', 'slut', 'nut', 'idiot', 'pussy', 'cunt','whore', 'bitch',
							'penis', 'mother fucker', 'son of bitch', 'damn');
	private $word;
	private $result;
	
	public function __construct($en)
	{
		$this->word = $en;
		$onefilter = $this->filterKey();
		$this->result = $this->filterWord($onefilter);
	}
	private function filterKey()
	{
		if(preg_grep("#{$this->word}#", $this->keyword))
		{
			return 'goddess';
		}
		else
		{
			return $this->word;
		}
	}
	private function filterWord($word)
	{
		$word=trim($word);

		if(preg_match_all('#[\x{4e00}-\x{9fa5}]+#u', $word, $ch))
		{
			if(count($ch[0]) >1)
			{
				//$items = preg_split('#\s+?#', $ch[0], -1, PREG_SPLIT_NO_EMPTY);
				$chinese='';
				foreach($ch[0] as $item)
				{
					$chinese .=$item;
				}
				return $chinese;
			}
			else
			{
				return implode('',$ch[0]);
			}
		}
		else if(preg_match('#[_\+\?\*\^\$\#\%\&\/\\,\.!@=\`\"]#',$word, $res))
		{
			return  "goddess";
		}
		else 
		{
			return $word;
		}
			
	}
	public function getResult()
	{
		return $this->result;
	}
}

/*  $filter = new Filter('中国  的');
echo $filter->getResult();  */

