<?php
//关闭载入包含js时的警告提示
error_reporting(E_ERROR | E_PARSE);
class DomTree
{
    //DOM句柄
    private $doc=null;

    //保存基本解释
    private $basic_meaning=array();

    //保存英汉双解
    private $en_or_ch=array();

    //保存英英释义
    private $en_to_en=array();

    //保存例句
    private $example=array();

    //保存常用句型
    private $sentences=array();

    //保存词汇表
    private $glossary=array();

    //保存经典名人名言
    private $auth=array();

    //保存常见错误用法
    private $use_in_wrong = array();

    //保存近义词
    private $approximate_words = array();

    //保存百科解释
    private $baike_trans = array();


    public function __construct($source)
    {
        $this->doc = new DomDocument();

        //判断$source类型
        if(is_file($source))
        {
            file_exists($source)?$this->doc->loadHTMLFile($source):die("文件不存在");
        }
        else if(is_string($source))
        {
           empty($source)?die("传入的字符串不能为空"):$this->doc->loadHTML($source);
        }
        else
        {
            preg_match('#^(http|ftp)://#i', $source)?$this->doc->loadHTML(file_get_contents($source)):die("不支持的资源类型");
        }

        //获取div元素列表
        $div_list = $this->doc->getElementsByTagName("div");
        $div_list_len = $div_list->length;

        for($i=0; $i<$div_list_len; $i++)
        {
            if($div_list->item($i)->hasAttribute("class"))
            {
                switch(trim($div_list->item($i)->getAttribute ("class")))
                {
                    case "basic clearfix":
                        $this->getBasicMeans($div_list->item($i));
                        break;

                    case "layout dual":
                        $this->getEnOrCh($div_list->item($i));
                        break;

                    case "layout en":
                        $this->getEnToEn($div_list->item($i));
                        break;

                    case "layout sort":
                        $this->getExample($div_list->item($i));
                        break;

                    case "layout patt":
                        $this->normalSentence($div_list->item($i));
                        break;

                    case "layout coll":
                        $this->getGlossary($div_list->item($i));
                        break;

                    case "layout auth":
                        $this->getAuth($div_list->item($i));
                        break;

                    case "layout comn":
                        $this->useInWrong($div_list->item($i));
                        break;

                    case "layout nfw":
                        $this->getApproximateWords($div_list->item($i));
                        break;

                    case "layout baike";
                        $this->getBaike($div_list->item($i));
                        break;
                }
            }
        }

    }

    //获取基本解释
    private function getBasicMeans($basic_div)
    {
        $li_list = $basic_div->getElementsByTagName("li");
        $li_list_len = $li_list->length;
        for($i=0; $i<$li_list_len; $i++)
        {
            $item = $li_list->item($i);
            if($item->hasAttribute("style"))
            {
                continue;
            }
            else
            {
                $strong_list  = $item->getElementsByTagName("strong");
                $strong_list_len = $strong_list->length;
                for($j=0; $j<$strong_list_len; $j++)
                {
                    $this->basic_meaning[]=$strong_list->item($j)->nodeValue;
                }
            }
        }
    }

    //获取英汉双解释义
    private function getEnOrCh($div_elem)
    {
        $li_list = $div_elem->getElementsByTagName("li");
        $li_list_len = $li_list->length;
        for($i=0; $i<$li_list_len; $i++)
        {
            $this->en_or_ch[]=$li_list->item($i)->nodeValue;

        }
    }

    //获取英英释义
    private function getEnToEn($div_elem)
    {
        $li_list = $div_elem->getElementsByTagName("li");
        $li_list_len = $li_list->length;
        for($i=0; $i<$li_list_len; $i++)
        {
            $this->en_to_en[]= $this->strip_Empty($li_list->item($i)->nodeValue);
        }
    }
    //格式化操作
    private function strip_Empty($string)
    {
        if(is_string($string))
        {
            return preg_replace('#\s{2,}#', ' ', $string);
        }
    }

    //获取例句
    private function getExample($div_elem)
    {
        if($div_elem->hasChildNodes())
        {
            $ol_list = $div_elem->getElementsByTagName("ol");
            $ol_list_len = $ol_list->length;
            for($i=0; $i<$ol_list_len; $i++)
            {
               $li_list = $ol_list->item($i)->getElementsByTagName("li");
               $li_list_len = $li_list->length;
               for($j=0; $j<$li_list_len; $j++)
               {
                   $this->example[] = $this->strip_Empty($li_list->item($j)->nodeValue);
               }
            }
        }
    }

    //常见句型
    private function normalSentence($div_elem)
    {
        $ol_list = $div_elem->getElementsByTagName("ol");
        $ol_list_len = $ol_list->length;
        for($i=0; $i<$ol_list_len; $i++)
        {
            //获取英语句型
            $li_list = $ol_list->item($i)->getElementsByTagName("li");
            $li_list_len = $li_list->length;
            for($j=0; $j<$li_list_len; $j++)
            {
                $this->sentences[]=$this->strip_Empty($li_list->item($j)->nodeValue);
            }

        }
    }

    //常见词汇
    private function getGlossary($div_elem)
    {
        $ul_list = $div_elem->getElementsByTagName("ul");
        $ul_list_len = $ul_list->length;
        for($i=0; $i<$ul_list_len; $i++)
        {
            //获取常见词汇
            $li_list = $ul_list->item($i)->getElementsByTagName("li");
            $li_list_len = $li_list->length;
            for($j=0; $j<$li_list_len; $j++)
            {
                $this->glossary[]=$this->strip_Empty($li_list->item($j)->nodeValue);
            }

        }
    }

    //获取名人名言
    private function getAuth($div_elem)
    {
        $ul_list = $div_elem->getElementsByTagName("ul");
        $ul_list_len = $ul_list->length;
        for($i=0; $i<$ul_list_len; $i++)
        {
            //获取列表
            $li_list = $ul_list->item($i)->getElementsByTagName("li");
            $li_list_len = $li_list->length;
            for($j=0; $j<$li_list_len; $j++)
            {
                $this->auth[]=$this->strip_Empty($li_list->item($j)->nodeValue);
            }

        }
    }

    //获取常见错误用法
    private function useInWrong($div_elem)
    {
        $ol_list = $div_elem->getElementsByTagName("ol");
        $ol_list_len = $ol_list->length;
        for($i=0; $i<$ol_list_len; $i++)
        {
            //获取错误用法列表
            $li_list = $ol_list->item($i)->getElementsByTagName("li");
            $li_list_len = $li_list->length;
            for($j=0; $j<$li_list_len; $j++)
            {
                $this->use_in_wrong[]=$this->strip_Empty($li_list->item($j)->nodeValue);
            }

        }
    }

    //获取近义词
    private function getApproximateWords($div_elem)
    {
        $ul_list = $div_elem->getElementsByTagName("ul");
        $ul_list_len = $ul_list->length;
        for($i=0; $i<$ul_list_len; $i++)
        {
            $li_list = $ul_list->item($i)->getElementsByTagName("li");
            $li_list_len = $li_list->length;
            for($j=0; $j<$li_list_len; $j++)
            {
                $a_list = $li_list->item($j)->getElementsByTagName("a");
                $a_list_len = $a_list->length;
                for($k=0; $k<$a_list_len; $k++)
                {
                    $this->approximate_words[]=$a_list->item($k)->nodeValue;
                }
            }

        }
    }

    //获取百科解释
    private function getBaike($div_elem)
    {
        $ul_list = $div_elem->getElementsByTagName("ul");
        $ul_list_len = $ul_list->length;
        for($i=0; $i<$ul_list_len; $i++)
        {
            //获取列表
            $li_list = $ul_list->item($i)->getElementsByTagName("li");
            $li_list_len = $li_list->length;
            for($j=0; $j<$li_list_len; $j++)
            {
                $this->baike_trans[]=$li_list->item($j)->nodeValue;
            }

        }
    }

    //接口：  返回基本释义
    public function getBasicMeaning()
    {
        if(!empty($this->basic_meaning))
        {
            return $this->basic_meaning;
        }
    }

    //接口： 返回英汉双解
    public function getEnOrChMeaning()
    {
        if(!empty($this->en_or_ch))
        {
            return $this->en_or_ch;
        }
    }

    //接口：  返回英英释义
    public function getEnToEnMeaning()
    {
        if(!empty($this->en_to_en))
        {
            return $this->en_to_en;
        }
    }

     //接口：  返回例句
    public function getExampleMeaning()
    {
        if(!empty($this->example))
        {
            return $this->example;
        }
    }
    //接口：  返回常用句型
    public function getNormalSentenceMeaning()
    {
        if(!empty($this->sentences))
        {
            return $this->sentences;
        }
    }

    //接口：  返回词汇表
    public function getGlossaryMeaning()
    {
        if(!empty($this->glossary))
        {
            return $this->glossary;
        }
    }

    //接口：  返回名人名言
    public function getAuthMeaning()
    {
        if(!empty($this->auth))
        {
            return $this->auth;
        }
    }

    //接口：  返回常见错误用法
    public function getUseInWrongMeaning()
    {
        if(!empty($this->use_in_wrong))
        {
            return $this->use_in_wrong;
        }
    }

    //接口：  获取近义词
    public function getApproximateWordsMeaning()
    {
        if(!empty($this->approximate_words))
        {
            return $this->approximate_words;
        }
    }

    //接口： 获取百度百科的解释
    public function getBaikeMeaning()
    {
        if(!empty($this->baike_trans))
        {
            return $this->baike_trans;
        }
    }

    //返回所有的翻译
    public function getAllMeaning()
    {
        $all_meaning = array();
        $all_meaning['basic_meaning'] = $this->getBasicMeaning();
        $all_meaning['en_or_ch'] = $this->getEnOrChMeaning();
        $all_meaning['en_to_en'] = $this->getEnToEnMeaning();
        $all_meaning['example']=$this->getExampleMeaning();
        $all_meaning['normal_sentence'] = $this->getNormalSentenceMeaning();
        $all_meaning['glossary_sentence'] = $this->getGlossaryMeaning();
        $all_meaning['auth_sentence'] = $this->getAuthMeaning();
        $all_meaning['wrong_use'] = $this->getUseInWrongMeaning();
        $all_meaning['approximate_words'] = $this->getApproximateWordsMeaning();
        $all_meaning['baike_meaning'] = $this->getBaikeMeaning();
        return $all_meaning;
    }
}

$dom = new DomTree("./com.html");

$trans = $dom->getAllMeaning();
echo "<pre>";
print_r($trans);
?>
