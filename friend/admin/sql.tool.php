<?php
class Sql
{
	private $host;
	private $username;
	private $passwd;
	private $dbname;
	private $tablename;
        private $province;
        private $image_table;

	private $conn;
	public $errMsg=0;

	public function __construct()
	{
		$config = require "config.php";
		$this->host = $config['db']['host'];
		$this->username = $config['db']['username'];
		$this->passwd = $config['db']['passwd'];
		$this->dbname = $config['db']['dbname'];
		$this->tablename = $config['db']['tablename'];
        $this->province = $config['db']['province_name'];
        $this->image_table = $config['db']['image_table'];

		$this->conn = new mysqli("{$this->host}", "{$this->username}", "{$this->passwd}");
		if(!$this->conn) $this->errMsg="链接数据库失败";
		$this->conn->select_db("{$this->dbname}");
		$this->conn->query("SET NAMES utf8");
	}

        public function select_city_by_province($province_name)
        {
            //根据省份获取对应的城市列表的表名
            $sql = "select cityname from ".$this->province." where pname = '{$province_name}'";
            $result = $this->conn->query($sql);
            if($result->num_rows > 0)
            {
                    $city_name = $result->fetch_row()[0];
            }

            $result->free();

            //根据城市列表的表名获取一些列的城市名字
            $sql = "select enname, chname from ".$city_name;
            $result = $this->conn->query($sql);
            $city = array();
            if($result->num_rows > 0)
            {
                    while($row = $result->fetch_assoc())
                    {
                            $city[] = array("en"=>$row['enname'], "ch"=>$row['chname']);
                    }
            }

            $result->free();
            return $city;

        }
		
		//获取可读的省份名字
		private function select_province($province_name)
		{
			$sql = "select pro_name from province where pname = '$province_name' ";
			
			$result = $this->conn->query($sql);
			
			if($this->conn->affected_rows  > 0)
			{
				$res = $result->fetch_row();
				$result->free();
				return $res[0];
			}
		}
		
		//获取可读的城市名字
		private function select_city($province_name,$city_name)
		{
			$city = $province_name.'city';
			$sql = "select chname from ".$city." where enname = '$city_name' ";
			
			$result = $this->conn->query($sql);
			if($this->conn->affected_rows > 0)
			{
				$res = $result->fetch_row();
				$result->free();
				return $res[0];
			}
		}
		
		//写入用户保存的信息
        public function setInfo($name='', $sex='', &$ageField, $qq='', $province='',$city='',&$tag,$intro='')
        {
                $age = $ageField['age'];
                $start_age = $ageField['start_age'];
                $end_age = $ageField['end_age'];
				
				//根据用户所在城市和年龄，选择正确的存储表
                $sql = "select tbname from ".$city." where start_age = ".$start_age." and end_age= ".$end_age;
				//echo $sql; exit();
                $result = $this->conn->query($sql);
                $user_table;
                if($result->num_rows > 0)
                {
                    $table = $result->fetch_row();
                    $user_table = $table[0];
                }

                $result->free();


                if(is_array($tag))
                {
                    $tag = implode(',', $tag);
                }

		session_start();
		$last_insert_id = $_SESSION['uid'][0];
		$province_name = $this->select_province($province);
		$city = $this->select_city($province, $city);
		
		$sql = "insert into ".$user_table."( name, sex,age, qq,area, tag, intro, image_id )
                values('$name', '$sex','$age', '$qq', '$province_name,$city', '$tag', '$intro',$last_insert_id)";
		
		$this->conn->query($sql);
		if($this->conn->affected_rows <= 0 )
		{
			$this->errMsg = "用户信息插入数据库失败";
		}
        }

		//保存用户上传的图像
        public function setImage($image='')
        {
            $sql = "INSERT INTO {$this->image_table}(image)  VALUES('$image')";
            $this->conn->query($sql);
            if($this->conn->affected_rows > 0)
            {
                    $result = $this->conn->query("SELECT LAST_INSERT_ID() FROM $this->image_table");
                    $last_insert_id = $result->fetch_row()[0];
                    session_start();
                    $_SESSION['uid']= $last_insert_id ;
                    $result->free();
            }
            else
            {
                    $this->errMsg = "用户图片插入数据库失败";
            }
        }
		
		//根据条件搜索用户信息
		public function searchUserInfo($province, $city,$age_list, $sex, $tag)
		{
			$age = $age_list['age'];
			$start_age = $age_list['start_age'];
			$end_age = $age_list['end_age'];
			
			//根据城市名称和年龄段获取分表名字
			$sql = "select tbname from ".$city." where start_age = '$start_age' and end_age = '$end_age' ";
			$result = $this->conn->query($sql);
			if($result->num_rows > 0)
			{
				$temp = $result->fetch_assoc();
				$vertical_city_name_tbl = $temp['tbname'];
				$result->free();
			}
			//file_put_contents("sql.txt", $sql."\r\n".$vertical_city_name_tbl."\r\n", FILE_APPEND);
			
			//根据年龄，性别，特征返回用户信息
			$sql = "select info.id, info.name, info.sex, info.age, info.qq, info.area, info.image_id , show_or_hidden as status from 
			(select id,name, sex, age, qq,area,image_id, show_or_hidden from $vertical_city_name_tbl 
			where age = '$age' and sex = '$sex' and tag = '$tag') as info, {$this->image_table} as img
			where info.image_id = img.id";
			//file_put_contents("sql.txt", $sql."\r\n", FILE_APPEND);
			
			$result = $this->conn->query($sql);
			if($result->num_rows > 0)
			{
				$user_info = array();
				while($row = $result->fetch_row())
				{
					$user_info[] = array('unique_id'=>$row[0],'name'=>$row[1], 'sex'=>$row[2],'age'=>$row[3], 'qq'=>$row[4],'area'=>$row[5], 
					'image_id'=>$row[6], 'status'=>$row[7],'city_name'=>"$vertical_city_name_tbl");
				}
				$result->free();
				return $user_info;
			}
			else
			{
				return array(array('name'=>"none"));
			}
			
		}
		
		/*
		 *  隐藏表单的某个字段
		*/
		public function hiddenField($table_name, $id)
		{
			$sql = "update {$table_name} set show_or_hidden='no' where id= ".$id;
			$this->conn->query($sql);
		}
		
		/*
		 *显示某个字段
		*/
		public function showField($table_name, $id)
		{
			$sql = "update {$table_name} set show_or_hidden='yes' where id= ".$id;
			$this->conn->query($sql);
		}
		
		/*
		 * 删除某条信息
		*/
		public function delField($city_name, $id)
		{
			$sql = 'delete from  '.$city_name.' where id='.$id;
			$this->conn->query($sql);
		}
		
		/*
		 * 删除某张图片
		*/
		public function delImage($image_id)
		{
			$sql = "delete from {this->image_table} where id = ".$image_id;
			$this->conn->query($sql);
		}
		
	//首页默认情况下显示武汉的交友信息
	public function getCity($city_name="wuhan")
	{
		$sql = "SELECT tbname FROM {$city_name}";
		$result = $this->conn->query($sql);
		$cities;
		if(!($this->conn->affected_rows > 0))
		{
			$this->errMsg = '获取用户信息失败';
		}
		while($row = $result->fetch_row())
		{
			$cities[]=$row[0];
		}
		$result->free();
        return $this->getInfoByAge($cities);
	}
        private function getInfoByAge($cities)
        {
            $user_info=array();
            foreach($cities as $city)
            {
                $sql = "select name, sex ,age,qq,area,tag,intro,image_id from ".$city." where show_or_hidden ='yes' ";
               
                $result = $this->conn->query($sql);
                if($this->conn->affected_rows > 0)
                {
                    while($row = $result->fetch_row())
                    {
                        $image_id = $row[7];
                        $sql = "select image from photograph where id = ".$image_id;
                        $image_link = '';
                        $img_result = $this->conn->query($sql);
                        if($this->conn->affected_rows > 0)
                        {
                            $img = $img_result->fetch_row();
                            $image_link = $img[0];
                            $img_result->free();
                        }
                        $user_info[] = array($row[0], $row[1],$row[2], $row[3],$row[4], $row[5],$row[6], $image_link);
                    }
                    $result->free();
                }/* 
				else
				{
					$user_info[] = array("未知名", "你猜","你猜", "你猜","出生的路上", "你猜","向世界宣布我在路上", "http://img0.bdstatic.com/img/image/shouye/xinshouye/qiche1231.jpg");
					return $user_info;
				} */
            }
            return $user_info;
        }

		public function selectAds($ads_num)
		{
			//获取广告图片链接和点击链接
			$sql = "select image_link, ads_link from ads where id = $ads_num";
			$result = $this->conn->query($sql);
			$ads = array();
			if($result->num_rows > 0)
			{
				$ads_info = $result->fetch_assoc();
				$ads['image_link']=$ads_info['image_link'];
				$ads['ads_link']=$ads_info['ads_link'];
				$result->free();
			}
			//获取关闭按钮的地址
			$sql = "select image_link from ads where id=127";
			$result = $this->conn->query($sql);
			if($result->num_rows > 0)
			{
				$ads_info = $result->fetch_assoc();
				$ads['shutdown_link'] = $ads_info['image_link'];
				$result->free();
			}
			return $ads;
		}
		
		public function setAdvice($advice)
        {
                $sql = "INSERT INTO advice(adv) VALUES('$advice')";
                $this->conn->query($sql);
                if(!($this->conn->affected_rows>0)) $this->errMsg='用户建议插入失败';
        }
        public function getAdvice()
        {
                $sql = "SELECT adv FROM advice";
                $result = $this->conn->query($sql);
                $advices;
                while($row = $result->fetch_row())
                {
                        $advices[]=$row;
                }
                $result->free();
                return $advices;
        }
}
?>