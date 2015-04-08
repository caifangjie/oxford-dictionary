<html>
<head>
<style type="text/css">
*{margin:0px; padding:0px;}
div#layout{margin-left:415px; margin-top:5px; width:700px; }

div#user{ border:1px solid #cde6c7;background-color:#918597;}

div#info{position:relative; top:-146px; left: 200px;}

p#name{margin-top:-3px;}
p{margin-top: 12px; color:#56452d; }

p#intro{margin-bottom:12px;}
font{line-height:25px;}

span{color:#6950a1;margin-left:30px;}
span.image{}

div#ads_area{position:fixed; z-index:2; margin-left:50px; margin-top:150px;}
</style>
<script type="text/javascript">
//创建XHR
function getXMLHttpRequest()
{
	var xmlhttp=null;
	if(window.ActiveXObject)
	{
		xmlhttp = new ActiveXObject("Microsoft.XMLHttp");
	}
	else
	{
		xmlhttp=new XMLHttpRequest();
	}
	return xmlhttp;
}
/*
 *广告模块
*/
function removeAds()
{
	var div = document.getElementById("ads_area");
	if(div.childNodes.length > 0)
	{
		div.removeChild(div.firstChild);
	}
}
var timeout_id;
function closeAds()
{
	clearTimeout(timeout_id);
	removeAds();
}
function randomAds()
{
	removeAds();
	var xmlhttp = getXMLHttpRequest();
	var url = "/ciba/niujin-beta/friend/randomAds.process.php";
	//根据当前时间的秒数，对3取摩，进而轮流显示三个广告
	var current_date = new Date();
	var data = "?ads_num="+current_date.getSeconds()%3;
	//alert(url+data);
	if(xmlhttp)
	{
		xmlhttp.open("get", url+data, true);
		xmlhttp.onreadystatechange = function()
		{
			if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
			{
				var res = xmlhttp.responseText;
				var ads_info = eval("("+res+")");
				//alert(ads_info.shutdown_link);
				var image_link = ads_info.image_link;
				var ads_link = ads_info.ads_link;
				var shutdown_link = ads_info.shutdown_link;

				var div = document.createElement("div");

				//创建链接
				var a_link = document.createElement("a");
				a_link.setAttribute("href", ads_link);
				a_link.setAttribute("target", "__blank");

				//创建图片
				var img_link = document.createElement("img");
				img_link.setAttribute("src", image_link);
				img_link.setAttribute("title", "加盟QQ 330454419");
				img_link.setAttribute("style", "height:250px; width:190px;");

				//创建关闭按钮
				var logo_link = document.createElement("img");
				logo_link.setAttribute("style", "height:15px; width:15px;position:relative; top:-235px; left:-15px; z-index:3;")
				logo_link.setAttribute("title", "点击关闭广告");
				logo_link.setAttribute("onclick", "closeAds()");

				a_link.appendChild(img_link);
				div.appendChild(a_link);
				div.appendChild(logo_link);
				document.getElementById("ads_area").appendChild(div);

			}
		}
		xmlhttp.send(null);
	}
	timeout_id = setTimeout("randomAds()", 5000);
}
window.onload = function()
{
	randomAds();
}

</script>
<head>

<body bgcolor="#RGB(67,65,65)">
<img src="../image/addfriend.jpg" style='margin-left: 400px; margin-top:1px;'>
<div id="ads_area">
</div>
<div id="layout">
<?php
require_once "sql.tool.php";
require_once 'template.class.php';
$cityname = $_GET['cityname'];
$sql = new Sql();
$items = $sql->getCity($cityname);
$mb_string = new Mb_string();
foreach($items as $item)
{
    /*
     * 40----80----120---
     *   <0,40>
     *   <<40, 80>
     *   <<80,120>
     *   <<120,>
     *  width:550px;
     * 字符数：33个字符
     */
    $str_len = $mb_string->mb_len($item[6]);
    if($str_len > 132)
    {
        echo "<div id='user' style='width:680px; height: 300px;'>";
        echo "<p><span>姓名</span><span>{$item[0]}</span></p>";
        echo "<p><span>性别</span><span>{$item[1]}</span></p>";
        echo "<p><span>年龄</span><span>{$item[2]}</span></p>";
        echo "<p><span>QQ号</span><span>{$item[3]}</span></p>";
        echo "<p><span>住址</span><span>{$item[4]}</span></p>";
        echo "<p><span>标签</span><span>{$item[5]}</span></p>";
        echo "<p><span>宣言</span><span>".$mb_string->text_wrap($item[6])."</span></p>";
        echo "<p><span class='image' style='position: relative;left:530px;top:-303px;'><img src='".$item[7]."' /></span></p>";
    }
    else if($str_len >99 && $str_len <=132)
    {
        echo "<div id='user' style='width:680px; height: 290px;'>";
        echo "<p><span>姓名</span><span>{$item[0]}</span></p>";
        echo "<p><span>性别</span><span>{$item[1]}</span></p>";
        echo "<p><span>年龄</span><span>{$item[2]}</span></p>";
        echo "<p><span>QQ号</span><span>{$item[3]}</span></p>";
        echo "<p><span>住址</span><span>{$item[4]}</span></p>";
        echo "<p><span>标签</span><span>{$item[5]}</span></p>";
        echo "<p><span>宣言</span><span >".$mb_string->text_wrap($item[6])."</span></p>";
        echo "<p><span class='image' style='position: relative;left:530px;top:-303px;'><img src='".$item[7]."' /></span></p>";
    }
    else if($str_len > 66 && $str_len <= 99 )
    {
        echo "<div id='user' style='width:680px; height: 266px;'>";

        echo "<p><span>姓名</span><span>{$item[0]}</span></p>";
        echo "<p><span>性别</span><span>{$item[1]}</span></p>";
        echo "<p><span>年龄</span><span>{$item[2]}</span></p>";
        echo "<p><span>QQ号</span><span>{$item[3]}</span></p>";
        echo "<p><span>住址</span><span>{$item[4]}</span></p>";
        echo "<p><span>标签</span><span>{$item[5]}</span></p>";
         echo "<p><span>宣言</span><span >".$mb_string->text_wrap($item[6])."</span></p>";
        echo "<p><span class='image' style='position: relative;left:530px;top:-277px;'><img src='".$item[7]."' /></span></p>";
    }
    else if($str_len > 33 && $str_len <=66 )
    {
        echo "<div id='user' style='width:680px; height: 240px;'>";

        echo "<p><span>姓名</span><span>{$item[0]}</span></p>";
        echo "<p><span>性别</span><span>{$item[1]}</span></p>";
        echo "<p><span>年龄</span><span>{$item[2]}</span></p>";
        echo "<p><span>QQ号</span><span>{$item[3]}</span></p>";
        echo "<p><span>住址</span><span>{$item[4]}</span></p>";
        echo "<p><span>标签</span><span>{$item[5]}</span></p>";
        echo "<p><span>宣言</span><span >".$mb_string->text_wrap($item[6])."</span></p>";
        echo "<p><span class='image' style='position: relative;left:530px;top:-253px;'><img src='".$item[7]."' /></span></p>";
    }
    else if($str_len > 0 && $str_len <=33 )
    {
        echo "<div id='user' style='width:680px; height: 220px;'>";
        echo "<p><span>姓名</span><span>{$item[0]}</span></p>";
        echo "<p><span>性别</span><span>{$item[1]}</span></p>";
        echo "<p><span>年龄</span><span>{$item[2]}</span></p>";
        echo "<p><span>QQ号</span><span>{$item[3]}</span></p>";
        echo "<p><span>住址</span><span>{$item[4]}</span></p>";
        echo "<p><span>标签</span><span>{$item[5]}</span></p>";
         echo "<p><span>宣言</span><span >".$mb_string->text_wrap($item[6])."</span></p>";
        echo "<p><span class='image' style='position: relative;left:530px;top:-222px;'><img src='".$item[7]."' /></span></p>";
    }



    echo "</div>";
    echo "<br /><br />";
}
?>

</div>
</body>
</html>
