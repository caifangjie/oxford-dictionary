<?php
/*唯一的参数是控制器传过来的数据，数据的形式以数据传递，可以作为一个通用分页类*/
class Rollpage
{
	//分成多少页
	private $page_total;
	
	//分页大小
	private $page_size = 15;
	
	//被分页的页面地址
	private $page_link;
	
	//当前页
	private $page_current ;
	
	//需要被分页的数据,这里是对数组进行分页
	private $words;
	
	//整体翻页大小
	private $rollpage_size=10;
	
	//首页
	private $home;
	
	//尾页
	private $end;
	
	//共有多少页
	private $total;
	
	private $link='';
	private $pos='';
	private $nagv='';
	private $table='';
	
	public function __construct($words, $href)
	{
		$len = count($words);
		$this->words=$words;
		$this->page_total = ($len > 0)? ceil($len/$this->page_size):0;
		$this->page_link=$href;
		$this->check();
	}
	private function check()
	{
		if(!isset($_GET['offset']))
		{
			$this->page_current=1;
		}
		else
		{
			$offset = $_GET['offset'];
			if($offset > $this->page_total)
			{
				$this->page_current = $this->page_total;
			}
			else if($offset < 1)
			{
				$this->page_current = 1;
			}
			else
			{
				$this->page_current = $offset;
			}
		}
	}
	
	//分页数据
	private function data()
	{
		return array_chunk($this->words, $this->page_size)[$this->page_current-1];
	}
	
	//首页
	private function home()
	{
		$this->home = "<a href =".$this->page_link."?offset=1 style='text-decoration:none ;'>首页</a>&nbsp;";
	}
	
	//尾页
	private function end()
	{
		$this->end = "<a href ='".$this->page_link."?offset=".$this->page_total." '  style='text-decoration:none ;' >尾页</a>&nbsp;&nbsp;&nbsp;";
	}
	
	//总页数
	private function total()
	{
		$this->total="共有<span style='color:#eeabf0;'>".$this->page_total."</span>页";
	}
	
	//创建分页链接，当前页禁用<a>..</a>并突出显示
	private function link()
	{
		if($this->page_total > $this->rollpage_size)
		{
			//根据分页大小，当分页总数大于10页时，每次显示10页内容
			$start = floor(($this->page_current-1)/$this->rollpage_size)*$this->rollpage_size+1;
			$end = $start+$this->rollpage_size;
			//整体向上翻10页
			if($this->page_current > $this->rollpage_size)
			{
				$offset = $start-1;
				$this->link .= "<a href='{$this->page_link}"."?offset={$offset}' "." style=\"text-decoration:none\" > ".'<<<'."<a>&nbsp;";
			}
			//整体翻十页关键代码
			for($start; $start < $end; $start++)
			{
				if($start < $this->page_total)
				{
					if($this->page_current == $start)
					{
						$this->link .= "<span style='text-decoration:none; font-size: 19px; color: white;' >".$start."</span>&nbsp;";
					}
					else
					{
						$this->link .= "<a href='{$this->page_link}"."?offset={$start}' "." style=\"text-decoration:none\" >".$start."<a>&nbsp;";
					}
				}
				
			}
			//根据边界条件，显示上一页
			if($this->page_current > 1)
			{
				$next_page = $this->page_current-1;
				$this->link .= "<a href='{$this->page_link}"."?offset={$next_page}' "." style=\"text-decoration:none\" > ".'上一页'."<a>&nbsp;";
			}
			if($this->page_current < $this->page_total)
			{
				$next_page = $this->page_current+1;
				$this->link .= "<a href='{$this->page_link}"."?offset={$next_page}' "." style=\"text-decoration:none\" > ".'下一页'."<a>&nbsp;";
			}
			//整体向下翻10页
			if($end < $this->page_total)
			{
				$this->link .= "<a href='{$this->page_link}"."?offset={$end}' "." style=\"text-decoration:none\" > ".'>>>'."<a>&nbsp;";
			}
		}
		//根据分页大小，当分页总数小于10页时，显示基本分页信息
		else
		{
			for($i=1; $i <= $this->page_total;$i++)
			{
				//当前页时，突出显示
				if($this->page_current == $i)
				{
					$this->link .= "<span style='text-decoration:none; font-size: 19px; color: white;' >".$i."</span>&nbsp;";
				}
				else
				{
					$this->link .= "<a href='{$this->page_link}"."?offset=$i' "." style=\"text-decoration:none\" >".$i."<a>&nbsp;";
				}
			}
		}
	}
	
	//添加一个跳转表单
	private function pos()
	{
		$this->pos = "<form  action='{$this->page_link}' method='get' style='display: inline;'><input type='text' name='offset' style='width:40px;' >&nbsp;".
		"<input type='submit' value='跳转' id='submit' style = 'width: 35px; height: 20px ; margin-top: 2px ; padding:1px;' ></form>&nbsp;";
	}
	
	//分页信息的头部(1)需要修改为通用的
	private function header()
	{
		$this->table .= "<div style='margin-left: 400px; margin-top: -18px;'>
		<span style='width: 120px; height:30px; background-color: #a00000; color: #00a000; font-size: 18px;'>查询历史</span>
		<span style='width: 120px; height:30px;  margin-left: 10px ;background-color: #a00000;  font-size: 18px;'><a href='clearHistory.php' target='_blank'  style='text-decoration:underline;color: #00a000; '>清空历史</a></span>
		<span style='width: 120px; height:30px;  margin-left: 10px ;background-color: #a00000;  font-size: 18px;'><a href='reviewWord.html' target='_blank'  style='text-decoration:underline;color: #00a000; '>复习单词</a></span></div>";
		
		$this->table .= "<table style=' width: 720px;  margin-left: 400px; border: 1px solid gray; padding-left: 80px;'>";
	}
	
	//分页主体(2)需要修改为通用的
	private function table()
	{
		$this->header();
		$word = $this->data();
		$len = count($word);
		for($i=0; $i < $len; $i = $i+3)
		{
			$one = isset($word[$i]) ? $word[$i] : "";
			$two = isset($word[$i+1]) ? $word[$i+1] : "";
			$three = isset($word[$i+2]) ? $word[$i+2] : "";
			$this->table .= "<tr>";
			$this->table .= "<td>".$one."</td>"."<td>".$two."</td>"."<td>".$three."</td>";
			$this->table .= "</tr>";
		}
		$this->table .= "</table>";
		
	}
	
	//用户接口，生成分页信息
	public function getLink()
	{
		$this->home();
		$this->end();
		$this->total();
		$this->link();
		$this->pos();
		$this->table();
		$this->nagv = $this->home.$this->link.$this->end.$this->pos.$this->total;
		echo $this->table;
		echo "<div style='margin-left: 400px; width: 720px; margin-top: 2px;' >".$this->nagv."</div>";;
	}
}
?>