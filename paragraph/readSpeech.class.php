<?php
class ReadSpeech
{
    private $host="localhost";
    private $username="root";
    private $passwd="cai123";
    private $db="words";
    private $conn=null;

    public $errMsg=0;

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
    }

    public function setSpech($name, $path)
    {
        $sql = "INSERT INTO presidentSpeech(name, path) VALUES('$name' , '$path')";

        $this->conn->query($sql);
        if(!($this->conn->affected_rows > 0))
        {
            $this->errMsg="写入名字和路径失败".__LINE__;
            exit();
        }
    }
    public function getSpeechById($id)
    {
        $sql = "SELECT name,path FROM presidentSpeech WHERE id = ".$id;
        $result = $this->conn->query($sql);
        if($this->conn->affected_rows > 0)
        {
            $items = $result->fetch_row();
            $name = $items[0];
            $path = $items[1];
            $result->free();
            return array($name,$path);
        }
        else
        {
            $this->errMsg="获取路径失败".__LINE__;
            exit();
        }
    }
    public function getAllSpeech()
    {
        $sql = "SELECT id , name  FROM presidentSpeech ";
        $result = $this->conn->query($sql);
        $res=array();
        if($this->conn->affected_rows > 0)
        {
            while($rows = $result->fetch_assoc())
            {
                $id = $rows['id'];
                $name = $rows['name'];
                $res[] = array($id, $name);
            }
            $result->free();
            return $res;
        }
        else
        {
            $this->errMsg="总统演讲信息失败".__LINE__;
            exit();
        }
    }
    public function __destruct()
    {
        $this->conn->close();
    }
}


?>