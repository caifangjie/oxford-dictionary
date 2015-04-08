<?php
class RedisServer
{
    private $redis=null;
    private $prefix="index::";

    public function __construct()
    {
            $this->redis=new Redis();
            $this->redis->connect('127.0.0.1', 6379);
            $this->redis->auth('caifangjie');
    }
    //设置索引，索引的形式是 (前缀+单词首字母， 单词，数字索引【对应数据库】)
    public function setIndex(&$index)
    {
        foreach($index as $item)
        {
            $this->redis->hSet($this->prefix.$item[0], $item[1], $item[2]);
        }
    }
    //返回单词对应的数字索引
    public function getIndex($word)
    {
        $zone = substr(strtolower(trim($word)), 0,1);
        if($this->redis->hExists($this->prefix.$zone, $word))
        {
            return $this->redis->hGet($this->prefix.$zone, $word);
        }
        else
        {
            return "none";
        }
    }
}

?>