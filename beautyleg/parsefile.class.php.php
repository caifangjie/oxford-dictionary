<?php
class Sql
{
    private $conn=null;
    private $src=array();
    public function __construct($filename)
    {
        $this->conn = new mysqli("localhost", "root", "cai123", "words");
        $this->readFile($filename);
        $this->writeFile();
    }
    private function readFile($filename)
    {
        $content = file_get_contents($filename);
        $part = preg_split("#\r\n#", $content, -1, PREG_SPLIT_NO_EMPTY);
        $src=array();
        foreach($part as $item)
        {
            preg_match_all('#http://#i', $item, $match);
            if(count($match[0]>1))
            {
                $links = preg_split('#\.jpg#i', $item, -1, PREG_SPLIT_NO_EMPTY);
                foreach($links as $link)
                {
                    $this->src[]=$link.".jpg";
                }
            }
            else
            {
               $this->src[]=$item;
            }
        }
    }
    private function writeFile()
    {
        if(count($this->src)>500)
        {
            $loop = ceil(count($this->src)/500);
            for($i=0; $i<$loop; $i++)
            {
                $links = array_chunk($this->src, 500)[$i];
                $sql = "INSERT INTO image(link) VALUES";
                foreach($links as $link)
                {
                    $sql .='("'.$link.'"),';
                }
                $sql = substr($sql, 0, strlen($sql)-1);
                $this->conn->query($sql);
                if($this->conn->affected_rows > 0)
                {
                    continue;
                }
                else
                {
                    die("写入数据库失败");
                }
            }
        }
        else
        {
            $sql = "INSERT INTO image(link) VALUES";
            foreach($this->src as $link)
            {
                $sql .='("'.$link.'"),';
            }
             $sql = substr($sql, 0, strlen($sql)-1);
            $this->conn->query($sql);
            if($this->conn->affected_rows > 0)
            {
                exit();
            }
            else
            {
                die("写入数据库失败");
            }
        }
    }
}

$sql = new Sql("links001.txt");

?>
