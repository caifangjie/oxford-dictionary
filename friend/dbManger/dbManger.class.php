<?php 
class DbManger
{
	private $host="localhost";
	private $username="root";
	private $passwd="cai123";
	private $dbname="words";
	
	private $conn=null;
	private $err_msg='';
	
	private $select_rows=0;
	private $desc_rows = 0;
	private $truncate_rows=0;
	
	/*
	 * 初始化数据库连接
	*/
	public function __construct()
	{
		$this->conn = new mysqli($this->host, $this->username, $this->passwd, $this->dbname);
		if(!$this->conn)
		{
			$this->err_msg = "链接数据库失败";
			die("链接数据库失败，安全退出");
		}
		$this->conn->query("set names utf8");
	}
	public function dump(&$array)
	{
		echo "<pre>";
		print_r($array);
	}
	/*
	 * 获取省份信息
	*/
	public function select_province()
	{
		$sql = "select cityname from province";
		$province_name=array();
		$result = $this->conn->query($sql);
		if($this->conn->affected_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$province_name[] = $row['cityname'];
			}
			$result->free();
		}
		else
		{
			$this->err_msg = "查询省份信息失败";
			die("查询省份信息失败，安全退出");
		}
		//根据查询得到的省份信息，查询市区信息
		$this->select_city($province_name);
		//$this->dump($province_name);
	}
	/*
	 * 根据省份信息获取对应的城市信息
	*/
	public function select_city(&$province_name)
	{
		$city_name = array();
		if(is_array($province_name))
		{
			foreach($province_name as $province)
			{
				$sql = "select enname from ".$province;
				$result = $this->conn->query($sql);
				if($this->conn->affected_rows > 0)
				{
					while($row = $result->fetch_assoc())
					{
						$city_name[$province][] = $row['enname'];
					}
					$result->free();
				}
				else
				{
					$this->err_msg = "查询市区信息失败";
					die("查询市区信息失败,安全退出");
				}
			}
		}
		else
		{
			$this->err_msg = "参数必须以数组的形式";
			die("参数必须以数组的形式, 安全退出");
		}
		//$this->dump($city_name);
		$this->select_cityinfo_by_age($city_name);
	}
	/*
	 * 根据年龄分段获取城市信息对应的分表信息
	*/
	public function select_cityinfo_by_age(&$province_name)
	{
		$city_info = array();
		if(is_array($province_name))
		{
			foreach($province_name as $key => $province)
			{
				foreach($province as $city_name)
				{
					$sql = "select tbname from ".$city_name;
					$result = $this->conn->query($sql);
					if($this->conn->affected_rows > 0)
					{
						while($row = $result->fetch_assoc())
						{
							$city_info[$key][$city_name][]=$row['tbname'];
						}
						$result->free();
					}
					else
					{
						$this->err_msg = "获取市区信息失败";
						die("获取市区信息失败, 安全退出");
					}
				}
			}
		}
		else
		{
			$this->err_msg = "参数必须以数组的形式";
			die("参数必须以数组的形式, 安全退出");
		}
		//$this->dump($city_info);
		$this->select_userinfo_by_cityinfo($city_info,"truncate");
		//$this->alter_userinfo_by_cityinfo($city_info, "alter table which_table_name_will_be_alter add column show_or_hidden enum('yes','no') not null default 'yes';", "show_or_hidden");
		//$this->alter_userinfo_by_cityinfo($city_info, "alter table which_table_name_will_be_alter add column pub_date timestamp", "pub_date");
	}
	
	/*
	 * 根据垂直分割表，获取每个表的信息,$choice表示操作类型
	*/
	public function select_userinfo_by_cityinfo(&$city_infomation, $choice)
	{
		$user_info = array();
		$choice = strtolower(trim($choice));
		
		if(is_array($city_infomation))
		{
			foreach($city_infomation as $province_index => $province_info)
			{
				foreach($province_info as $city_index => $city_info)
				{
					foreach($city_info as $city_name)
					{
						switch($choice)
						{
							case "select":
								$sql = "select * from ".$city_name;
								$result = $this->conn->query($sql);
								if($this->conn->affected_rows > 0)
								{
									while($row = $result->fetch_row())
									{
										$user_info[$province_index][$city_index][$city_name][]=$row;
										$this->select_rows++;
										//var_dump($row); 
									}
									$result->free();
								}
							case "desc":
								$sql = "desc ".$city_name;
								$result = $this->conn->query($sql);
								if($this->conn->affected_rows > 0)
								{
									while($row = $result->fetch_row())
									{
										$user_info[$province_index][$city_index][$city_name][]=$row;
										$this->desc_rows++;
										//var_dump($row); 
									}
									$result->free();
								}
							case "truncate":
								$sql = "truncate ".$city_name;
								$bool = $this->conn->query($sql);
								if(!$bool)
								{
									$this->err_msg = "清空表".$city_name."操作失败";
									die($this->err_msg);
								}
								else
								{
									$this->truncate_rows++;
								}
						}
					}
				}
			}
		}
		else
		{
			$this->err_msg = "参数必须以数组的形式";
			die("参数必须以数组的形式, 安全退出");
		}
		$this->dump($user_info);
	}
	
	/*
	 * 批量修改用户信息
	*/
	private function alter_userinfo_by_cityinfo(&$city_infomation, $sql, $field_name)
	{
		if(is_array($city_infomation))
		{
			foreach($city_infomation as $province_index => $province_info)
			{
				foreach($province_info as $city_index => $city_info)
				{
					foreach($city_info as $city_name)
					{
						$sql_field_exists_or_not = "select {$field_name} from ".$city_name;
						$exists_or_not = $this->conn->query($sql_field_exists_or_not);
						if($this->conn->affected_rows > 0)
						{
							//说明存在这个字段
							$exists_or_not->free();
							continue;
						}
						else if($this->conn->affected_rows < 0)
						{
							//说明存在这个字段不存在
							$sql_sentence = str_replace("which_table_name_will_be_alter", $city_name, $sql);
							$result = $this->conn->query($sql_sentence);
							if($this->conn->affected_rows < 0)
							{
								$this->err_msg = "alter修改表字段失败";
								die("alter修改表".$city_name."字段失败, 安全退出");
							}
						}
					}
				}
			}
		}
		else
		{
			$this->err_msg = "参数必须以数组的形式";
			die("参数必须以数组的形式, 安全退出");
		}
	}
	/*
	 * 反馈信息
	*/
	public function affected_rows()
	{
		if($this->select_rows > 0)
		{
			echo "<br /><span style='color:grey; font-size:18px;'>查询返回{$this->select_rows}条信息</span><br />";
		}
		else if($this->desc_rows > 0)
		{
			echo "<br /><span style='color:grey; font-size:18px;'>查询返回{$this->desc_rows}条描述信息</span><br />";
		}
		else if($this->truncate_rows > 0)
		{
			echo "<br /><span style='color:grey; font-size:18px;'>删除{$this->truncate_rows}个表</span><br />";
		}
	}

	/*
	 * 用于测试
	*/
	public function test()
	{
		$sql = "truncate gz_24_28";
		$result = $this->conn->query($sql);
		//var_dump($result);
		echo $result;
	}
}
$db = new DbManger();
$db->select_province();
$db->affected_rows();
//$db->test();

?>