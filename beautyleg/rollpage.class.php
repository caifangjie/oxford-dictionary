<?php
class Rollpage
{
    //有多少条数据
    private $total_items;

    //分页数
    private $total_page;

    //当前页
    private $current_page;

    //分页大小
    private $page_size=15;

    //整体翻页大小
    private $rollpage_size=10;

    /*
     * $total_page = ceil($total_item / $page_size);
     */

    //被分页的页面地址
    private $page_href;

    //首页
    private $home_link;
	
    //尾页
    private $tail_link;
	
    //上一页
    private $prev_link;
	
     //数字翻页
    private $num_link;
	
    //下一页
    private $next_link;
	
    //跳转框
    private $form_link;

    //导航条
    private $nagv_link;

    //数据库连接句柄
    private $conn;

	//接受需要被分页的地址,,,这里可以把对数据库操作的事情写入配置项里，这样就是一个通用的分页类
    public function __construct($href)
    {
        $this->page_href = $href;

        //获取分页大小
        $this->conn = new mysqli("localhost", "root", "cai123", "words");
        $result = $this->conn->query("select count(*) from image");
        $this->total_items = $result->fetch_row()[0];
        $result->free();
        $this->total_page = ceil($this->total_items / $this->page_size);

        //对参数的边界检查
        $this->boundCheck();

    }
	//边界检查
    private function boundCheck()
    {
        if(!isset($_GET['offset']))
        {
            $this->current_page = 1;
        }
        else
        {
            if($_GET['offset'] > $this->total_page)
            {
                $this->current_page = $this->total_page;
            }
            else if($_GET['offset'] < 1)
            {
                 $this->current_page = 1;
            }
            else
            {
                 $this->current_page = $_GET['offset'];
            }
        }
    }
   
    //显示跳转框
    private function jumpPage()
    {
        $this->form_link = "<form  action='{$this->page_href}' method='get' style='display: inline;'><input type='text' name='offset' style='width:40px;' >&nbsp;".
		"<input type='submit' value='跳转' id='submit' style = 'width: 35px; height: 20px ; margin-top: 2px ; padding:1px;' ></form>&nbsp;";
    }

    //首页
    private function homePage()
    {
        $this->home_link = "<a href='{$this->page_href}?offset=1'"." style='text-decoration:none ;'>首页</a>&nbsp;";
    }

    //尾页
    private function tailPage()
    {
        $this->tail_link = "<a href ='".$this->page_href."?offset=".$this->total_page." '  style='text-decoration:none ;' >尾页</a>&nbsp;&nbsp;&nbsp;";
    }

    //下一页
    private function nextPage()
    {
        if($this->current_page < $this->total_page)
        {
            $next_page = $this->current_page + 1;
            $this->next_link = "<a href='{$this->page_href}"."?offset={$next_page}' "." style=\"text-decoration:none\" > ".'下一页'."<a>&nbsp;";
        }
    }

    //上一页
    private function prevPage()
    {
        if($this->current_page > 1)
        {
                $next_page = $this->current_page-1;
                $this->prev_link = "<a href='{$this->page_href}"."?offset={$next_page}' "." style=\"text-decoration:none\" > ".'上一页'."<a>&nbsp;";
        }
    }

    //显示数字分页
    private function numPage()
    {
        //如果总页数大于10（即设置的每次显示十页$this->rollpage_size）
        if($this->total_page > $this->rollpage_size)
        {
            $start = floor(($this->current_page - 1)/$this->rollpage_size)*$this->rollpage_size +1 ;
            $end = $start + $this->rollpage_size;

            //向前翻十页
            if($this->current_page > $this->rollpage_size)
            {
                $offset = $start-1;
		$this->num_link .= "<a href='{$this->page_href}"."?offset={$offset}' "." style=\"text-decoration:none\" > ".'<<<'."<a>&nbsp;";
            }

            //每次显示十页
            for($start; $start < $end; $start++)
            {
                if($start < $this->total_page)
                {
                     //如果等于当前页
                    if($this->current_page == $start)
                    {
                            $this->num_link .= "<span style='text-decoration:none; font-size: 19px; color: white;' >".$start."</span>&nbsp;";
                    }
                    else
                    {
                            $this->num_link .= "<a href='{$this->page_href}"."?offset={$start}' "." style=\"text-decoration:none\" >".$start."<a>&nbsp;";
                    }
                }
            }
            //向后翻10页
            if($end < $this->total_page)
            {
               $this->num_link .= "<a href='{$this->page_href}"."?offset={$end}' "." style=\"text-decoration:none\" > ".'>>>'."<a>&nbsp;";
            }
        }
        //如果总页数小于10（即设置的每次显示十页$this->rollpage_size），则显示所有页
        else
        {
            for($i=1; $i <= $this->total_page;$i++)
            {
                //当前页时，突出显示
                if($this->current_page == $i)
                {
                        $this->num_link .= "<span style='text-decoration:none; font-size: 19px; color: white;' >".$i."</span>&nbsp;";
                }
                else
                {
                        $this->num_link .= "<a href='{$this->page_href}"."?offset=$i' "." style=\"text-decoration:none\" >".$i."<a>&nbsp;";
                }
            }
        }

    }
	//生成导航条
    private  function getNagv()
    {
        $this->homePage();
        $this->prevPage();
        $this->numPage();
        $this->nextPage();
        $this->tailPage();
        $this->jumpPage();

        $this->nagv_link = $this->home_link.$this->prev_link.$this->num_link.$this->next_link.$this->tail_link.$this->form_link;
        return $this->nagv_link;

    }
	//生成需要显示的数据
	 private function getData()
    {
        $sql = "select link from image limit ".($this->current_page-1)*$this->page_size.' , '.$this->page_size;
        $result = $this->conn->query($sql);
        $links = array();
        if($result->num_rows)
        {
            while($row = $result->fetch_row())
            {
                $links[] = $row[0];
            }
            $result->free();
           return $links;
        }
        else
        {
            die("查询失败");
        }
    }
	
	//接口： 返回显示的数据和分页导航条
	public function getResult()
	{
		$result['nagv']=$this->getNagv();
		$result['data']=$this->getData();
		return $result;
	}
	

}

?>
