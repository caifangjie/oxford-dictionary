<?php
class Mb_string
{
	/*
	 * 用于多字节截取，并返回前140个字符
	*/
	public function truncate_chars($intro,$truncate_len=140)
	{
            $string='';
            $len = strlen($intro);
            $true_len=0;
            for($i=0; $i<$len; $i++)
            {
                if($true_len < $truncate_len)
                {
                    if(ord(substr($intro, $i, 1))>0xa0)
                    {
                        $string .= substr($intro, $i, 3);//因为是以UTF-8编码保存的
                        $i++;$i++;
                        $true_len++;
                    }
                    else
                    {
                        $string .= $intro[$i];
                        $true_len++;
                    }
                }
            }
            return $string;
	}

	/*
	 * 返回多字节字符串的长度
	*/
	public function mb_len($str)
	{
            $len = strlen($str);
            $true_len=0;
            for($i=0; $i<$len; $i++)
            {
                if(ord(substr($str, $i, 1))>0xa0)
                {
                        $i++;$i++;
                        $true_len++;
                }
                else
                {
                        $true_len++;
                }
            }
            return $true_len;
	}
        /*
         * 多字节字符串截取
         */
        public function mb_sub($str, $start_pos, $sub_len)
        {
            $str_len = $this->mb_len($str);
            if($sub_len > $str_len)
            {
                $sub_len = $str_len;
            }
            if($start_pos > $str_len)
            {
                return false;
            }
            $pre_str = $this->truncate_chars($str, $start_pos);
            return $this->truncate_chars(substr($str, strlen($pre_str)), $sub_len);
        }
        /*
         * 简单的格式化包装器
         */
        public function text_wrap($str, $len=33)
        {
            $start_pos = 0;
            $string='';
            if($this->mb_len($str) >= $len)
            {
                $num = ceil($this->mb_len($str)/$len);
                for($i=0; $i<$num; $i++)
                {
                    $string .= "<font class='text_wrap'>".$this->mb_sub($str, $start_pos, $len)."</font>";
                    $start_pos += $len;
                }
            }
            else
            {
                return $str;
            }
            return $string;
        }

}

//$mb_string = new Mb_string();
//echo $mb_string->truncate_chars($str);
//echo $mb_string->mb_len($str);
//echo $mb_string->mb_sub($str, 6, 10);
//echo $mb_string->mb_len("当输入字符大于40个字符时。当输入字符大于40个字符时。当输入字符") ;
//echo $mb_string->text_wrap($str);
?>