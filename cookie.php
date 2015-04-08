<?php
require_once "sql.class.php";
function setOrGetUser()
{
	$mysqli = new mysqli("localhost", "root", "cai123") or die ('连接数据库失败');
	$mysqli->select_db("words");
	$mysqli->query("set names utf8");
	if(empty($_COOKIE))
	{
		$username=md5($_SERVER["REMOTE_ADDR"].time().rand(1,500));
		setcookie("user", $username, time()+30*24*3600);


		$sql="insert into user(username) values('$username')";
		$mysqli->query($sql) or die('插入用户名失败');
		return $username;
	}
	else
	{
		$username = $_COOKIE['user'];
		//如果cookie不为空，则检查数据库，对应的cookie信息是否存在，不存在则插入本条cookie信息
		$sql = "SELECT * FROM user WHERE username ='$username' ";
		$res = $mysqli->query($sql);
                
		if($mysqli->affected_rows==0)
		{
			$sql="INSERT INTO user(username) VALUES('$username') ";
			$mysqli->query($sql);
			if($mysqli->affected_rows<1)
			{
				die('插入用户失败');
			}
			return $username;
		}
		else
		{
			$time = $res->fetch_assoc();
			//当超过最大时间时，删除用户的查询史
			if($time['udate']+3600*24*10 > time())
			{
				$sql = new Sql($username);
				$sql->deleteWord();
			}
			$res->free();
		}
		return $username;
	}

}

//echo setOrGetUser();

