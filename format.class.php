<?php
//格式化翻译
class FormatTrans
{
	private $result=array();
	public function trans($str)
	{	
		$trans = $this->getTrans($str);
		$item = $this->format($trans);
		$this->result = $this->result($this->normal($item));
		return $this->result;
	}
	public function getTrans($str)
	{
		$pat='#\.\s(\d{1,2})\s#';
		if(preg_match($pat, $str))
		{
			$res=preg_split($pat, $str,-1 , PREG_SPLIT_NO_EMPTY);
			return $res;
		}
		else
		{
			$res[] = $str;
			return $res;
		}
	}
	
	public function format($str)
	{
			$result;
			foreach($str as $string)
			{
				if(preg_match('#[\x{4e00}-\x{9fa5}]\.\s#iu', $string))
				{
					$items = preg_split('#(?<=[\x{4e00}-\x{9fa5}])\.\s#iu', $string,-1, PREG_SPLIT_NO_EMPTY);
					foreach($items as $item)
					{
						$item = trim($item);
						if(($pos=strpos($item, "*")) !==false)
						{
							$item = substr($item, $pos+1);
							$result[]=$item;
						}
						else if(preg_match('#^\d#', $item, $num))
						{
							$pos = strpos($item, $num[0])+1;
							$item =substr($item, $pos);
							$result[]=$item;
						}
						else
						{
							$result[]=$item;
						}
						
					}
				}
				else
				{
					$result=$str;
					return $result;
				}
			}
			
			return $result;
	}
	public function normal($items)
	{
		$result = array();
		foreach($items as $translation)
		{
				if(preg_match('#\*#', $translation) )
				{
					$items = preg_split('#\s\*\s#iu', $translation,-1, PREG_SPLIT_NO_EMPTY);
					foreach($items as $item)
					{
						$result[] = $item;
					}
				}
				else
				{
					$result[] = $translation;
				}
		}
		return $result;
	}
	public function result($str)
	{
		$result;
		foreach($str as $string)
		{
			if(preg_match('#[\x{4e00}-\x{9fa5}]:#iu', $string))
			{
				$items = preg_split('#(?<=[\x{4e00}-\x{9fa5}]):#iu', $string, -1, PREG_SPLIT_NO_EMPTY);
				foreach($items as $item)
				{
					$result[]=$item;
				}
			}
			else
			{
				$result[] = $string;
			}
		}
		return $result;
	}
}

?>