<?php
/*
    本类的工作主要是负责根据id和单词（id, word）建立索引，
    索引的形式是(前缀，单词,id)
*/
require_once "redis.class.php";
class BuildIndex
{
    private $host="localhost";
    private $username="root";
    private $passwd="cai123";
    private $db="words";
    private $conn=null;

    private $wordsNum;
    private $errMsg=0;

    private $redis=null;

    public function __construct()
    {
            $this->conn=new mysqli($this->host, $this->username, $this->passwd) ;
            if(!$this->conn)
            {
                $this->errMsg = '连接数据库失败'.__LINE__;
                exit();
            }
            $this->conn->select_db($this->db);
            $this->conn->query("set names utf8");

            $sql = "SELECT COUNT(*) FROM cetsix";
            $result = $this->conn->query($sql);
            if($this->conn->affected_rows > 0)
            {
                $this->wordsNum = $result->fetch_row()[0];
                $result->free();
            }
            else
            {
                $this->errMsg="查询词条总数失败".__LINE__;
                exit();
            }
            $this->redis = new RedisServer();
            $this->buildIndex();


    }
    private function buildIndex()
    {
        $onceReadSize  = 500;
        $loop= ceil($this->wordsNum/500);

        for($i=1; $i<=$loop; $i++)
        {
            $sql = "SELECT id, word FROM cetsix LIMIT  ".($i-1)*$onceReadSize." , ".$onceReadSize;
            $result = $this->conn->query($sql);
            $index = array();
            while($row = $result->fetch_assoc())
            {
                 $word = strtolower(trim($row['word']));
                $index[] = array(substr($word, 0, 1), $word,$row['id']);
            }
            $this->redis->setIndex($index);
            unset($index);
            $result->free();
            echo str_repeat("   ", 2048);
            echo "创建索引".$i*$onceReadSize."条,Ok..<br />";
            sleep(2);
        }
        echo "索引创建完毕";
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}

set_time_limit(1000);
ob_implicit_flush(true);
$index  = new BuildIndex();

?>