<?php
require_once 'groupDicName.class.php';
require_once "storeWord.class.php";

class Check
    {

    private $groupName = null;
    private $redis = null;

    public function __construct()
        {
        $this->groupName = new GroupDicName();
        $this->redis = new StoreWord();
        }

    /*
      1) 待查询的单词的前两个字符和单词分类名能够完全匹配，比如：
      [64] => h-a

      [65] => h-b,c,d,e

      [66] => h-f,g,h,i

      [67] => h-j,k,l,m,n,o

      [68] => h-p,q,r,s,t,u,v,w,x,y,z

      2) 待查询的单词的第一个字符可以匹配，但第二个字符不能匹配，好比你输入的单词是以ms 开头，即表示不存在，比如：
      80] => m-a

      [81] => m-b,c,d,e

      [82] => m-f,g,h,i

      [83] => m-j,k,l,m,n,o

      [84] => m-p,z
      3) d待查询的单词可以匹配单词分类名，且但此分类名只有一个总分类时，比如：
      [144] => x

      [145] => y

      [146] => z


     */

    public function matchDic($userInput = '')
        {
        if (!empty($userInput))
            {
            $userInput = strtolower($userInput);
            $firstChar = substr($userInput, 0, 1);
            $secondChar = substr($userInput, 1, 1);

            $dicSort = trim($this->groupName->getIndex());
            //echo $dicSort; exit();
            $word = explode('#', $dicSort);

            if (strlen($userInput) >= 2 && strlen($userInput) < 3)
                {
                foreach ($word as $val)
                    {
                    $val = trim($val);
                    if ($firstChar == substr($val, 0, 1))
                        {
                        $firstMatch[] = $val;
                        }
                    }
                if (count($firstMatch) == 1)
                    {
                    return $firstMatch[0];
                    } else if (count($firstMatch) > 1)
                    {
                    //对分类索引进行排序，将包含单词范围的~放在最前面，进行特殊匹配
                    //这样的弊端是有一定的效率损耗......
                    //但能匹配每一种特殊情况.....

                    usort($firstMatch, function($one, $two) {
                        if (strpos($one, '~'))
                            return -1;
                        else
                            return 1;
                    });
                    //var_dump($firstMatch); exit();
                    foreach ($firstMatch as $word)
                        {
                        //匹配第1种情况，前两个字符都能匹配
                        $set = substr($word, strpos($word, '-') + 1);
                        //var_dump($set); echo 'inner layer';exit();
                        //echo $set."<br />";
                        if (preg_match('#^([a-z])~([a-z])#', $set, $match))
                            {
                            $startRange = $match[1];
                            $endRange = $match[2];
                            if (preg_match("#[$startRange-$endRange]#", $secondChar))
                                {
                                return $word;
                                }
                            } else if (gettype(strpos($set, $secondChar)) == "integer" && strpos($set, '~') === false)
                            {
                            return $word;
                            }
                        }
                    } else
                    {
                    die("请输入查询单词");
                    }
                } else if (strlen($userInput) >= 3)
                {
                $thirdChar = substr($userInput, 2, 1);
                $firstMatch = array();
                foreach ($word as $val)
                    {
                    $val = trim($val);
                    if ($firstChar == substr($val, 0, 1))
                        {
                        $firstMatch[] = $val;
                        }
                    }
                //var_dump($firstMatch);exit();
                //匹配第三种情况
                if (count($firstMatch) == 1)
                    {
                    return $firstMatch[0];
                    }
                /* 此前遗漏掉一种情况， o~n 中的~表示一个范围，而此前直接把~转换成,号了，所以不够准确 */ else if (count($firstMatch) > 1)
                    {
                    //对分类索引进行排序，将包含单词范围的~放在最前面，进行特殊匹配
                    //这样的弊端是有一定的效率损耗......
                    //但能匹配每一种特殊情况.....
                    //
								usort($firstMatch, function($one, $two) {
                        if (strpos($one, '~'))
                            return -1;
                        else
                            return 1;
                    });
                    //var_dump($firstMatch); exit();
                    foreach ($firstMatch as $word)
                        {
                        //匹配第1种情况，前两个字符都能匹配
                        $set = substr($word, strpos($word, '-') + 1);

                        if (preg_match('#^([a-z])([a-z])~([a-z])#', $set, $match))
                            {
                            $startChar = $match[1];
                            $startRange = $match[2];
                            $endRange = $match[3];
                            if (gettype(strpos($startChar, $secondChar)) == "integer" && preg_match("#[$startRange-$endRange]#", $thirdChar))
                                {
                                return $word;
                                }
                            } else if (preg_match('#(?<![a-z])([a-z])~([a-z])#', $set, $match))
                            {
                            $startRange = $match[1];
                            $endRange = $match[2];
                            if (preg_match("#[$startRange-$endRange]#", $secondChar, $match))
                                {
                                return $word;
                                }
                            } else if (($set[0] == $secondChar) && gettype(strpos($set, '~')) == "integer")
                            {
                            if (preg_match('#(?<![a-z])([a-z])~([a-z])#', $set, $match))
                                {
                                $startRange = $match[1];
                                $endRange = $match[2];
                                if (preg_match("#[$startRange-$endRange]#", $thirdChar, $match))
                                    {
                                    return $word;
                                    }
                                }
                            } else if (gettype(strpos($set, $secondChar)) == "integer" && strpos($set, '~') === false)
                            {
                            return $word;
                            }
                        }
                    } else
                    {
                    die("请输入查询单词");
                    }
                } else
                {
                return $userInput;
                }
            } else
            {
            die("请输入查询单词");
            }
        }

    public function ajaxMatch($userInput = '')
        {
        $wordRange = $this->matchDic($userInput);
        $words = $this->redis->getAllWord($wordRange);
        //var_dump($words);exit();
        $match = array();
        foreach ($words as $word)
            {
            if (stripos($word, $userInput) !== false)
                {
                $match[] = $word;
                }
            }
        if (count($match) > 0)
            {
            usort($match, function($one, $two) {
                if (strlen($one) < strlen($two))
                    return -1;
                if (strlen($one) == strlen($two))
                    return 0;
                if (strlen($one) > strlen($two))
                    return 1;
            });
            if (count($match) >= 10)
                {
                return array_slice($match, 0, 10);
                } else
                {
                return $match;
                }
            } else
            {
            return array('没有匹配项');
            }
        }

    }

/*  $check = new Check();
  //echo $check->matchDic('cobal');
  print_r($check->ajaxMatch('make_of')); */
?>