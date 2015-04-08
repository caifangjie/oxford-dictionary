<?php
/*本类的主要作用是用于格式化输出*/
class Format
{
    public function stripBreak($data)
    {
        if(is_string($data))
        {
            if(preg_match('#/r/n#i', $data))
            {
                return str_replace("/r/n", "  ", $data);
            }
            else
            {
                return $data;
            }
        }
        else if(is_array($data))
        {
             $result = array();
            foreach($data as $item)
            {
                if(preg_match('#/r/n#i',$item))
                {
                    $result[]= str_replace("/r/n", "  ", $item);
                }
                else
                {
                    $result[]=$item;
                }
            }
            return $result;
        }
    }
}
?>
