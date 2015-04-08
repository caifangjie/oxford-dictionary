<?php
header("content-type: text/html; charset=gb2312");
class HttpWrap
{
	//定义超时时间
    public $timeout=10;
	
	//定义连接状态
    public $status='';

	//主机名
    public $host;
	
	//端口号
    public $port=80;
	
	//第一次连接时将主机名解析成IP
    private $ip;
	
	//连接资源
    private $conn;
	
	//连接的地址
	private $url;
	
	//解析URL中的路径
    private $path;
    
	//URL中包含的模式，比如FTP,HTTPS
    private $scheme;

	//请求方式，比如GET,POST,PUT
    public $http_method='GET';
	
	//HTTP的版本信息
    public $http_version="HTTP/1.1";
	
	//代理软件信息
    public $agent="Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0";
	
	//定义可接收的MIME信息
    public $accept="image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*";
	
	//压缩方式
    public $gzip="gzip";
	
	//上级域名或者连接地址
    public $referer;
	
	//设置COOKIE
    public $cookie;
	
	//提交类型
    public $submit_type="application/x-www-form-urlencoded";
	
	//可接收的语言类型
    public $accept_language="zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3";
	
	//保持长连接
    public $connection="keep-alive";

	//HTTP请求行
    private $cmd_line;
	
	//HTP请求头部
    private $header;
	
	//HTTP请求，如果包含主体信息
    public $post_content;

	//重定向地址
    private $redirect;
	
	//是否支持GZIP压缩
    private $is_gzip;
	
	//HTTP响应状态码,比如200,404等
    public $response_num;
	
	//HTTP响应头部信息
    public $response_header;
	
	//HTTP响应主体
	public $response_body;
	
	//HTTP响应主体信息的长度
    public $response_body_length=0;
    
	//用于保存下一页地址
    public $roll_link;
	
	//用于保存下一组地址
    public $roll_group;
	
	//用于判断是否匹配到了下一页或者下一组
	public $match_status=0;
	
	//响应主体信息保存在那个路径下的哪个文件里
    public $filename;
	
	//响应的编码信息
    public $encoding;
	

   public  function init($url)
    {
        $this->url=$url;
		//解析url信息
        $url_pair = parse_url($url);
		
		//保存主机名
        $this->host = $url_pair['host'];
		//保存url中包含的路径信息
        $this->path = $url_pair['path'];
		//保存使用的模式信息
        $this->scheme = $url_pair['scheme'];

		//通过域名解析出对应的IP地址,用于加速在连接超时，或者连接被重置时的连接
        if(empty($this->ip))
        {
            $this->ip = gethostbyname($this->host);
        }

        if(!empty($url_pair['port']))
        {
            $this->port = $url_pair['port'];
        }
     
		//如果连接到远地主机成功，则发送请求
		if($this->connect())
		{
			$this->sendRequest();
		}
		else
		{	
			//如果连接失败，则休眠几秒，继续重连,比如出现网络不稳定时
			echo str_repeat("  ", 2048);
			echo $this->status.",  <font color='red'>网络异常，重新链接中....</font></br />";
			$this->conn=null;
			sleep(mt_rand(3,5));
			$this->init($this->url);
		}
		
        //如果响应头部存在重定向，则对重定向发送请求
        if($this->redirect)
        {
			//默认设置只允许对当前域名下的主机的重定向，比如页面间的跳转
            if(preg_match("#^http://".preg_quote($this->host)."#i",$this->redirect))
            {
                $this->referer=$this->host."/".parse_url($this->redirect)['path'];
                $this->init($this->redirect);
            }
        }
        if($this->roll_link)
        {
            $next_url = substr($this->url,0,strripos($this->url, '/')+1).$this->roll_link;
			
            //如果下一页等于当前页,也就是最后一页时，对下一组网页发送请求
            if(strtolower(trim(basename($this->url,'.html'))) == strtolower(trim(basename($next_url,'.html'))))
            {
               $next_group = $this->getNextGroup($this->response_body);
               echo "<font color='color'>即将采集下一组</font><br />";
			   if($next_group)
			   {
					$this->init($next_group);
			   }
			   else
			   {	
					//如果解析下一组的地址失败时，保存到日志,,,经过反复测试，这个问题不存在
					file_put_contents("log.txt", $this->url, FILE_APPEND);
					die($this->match_status);
			   }
               
            }
            else
            {
				//对下一页进行请求
                $this->init($next_url);
            }

        }
        else
        {
			//存在一个奇怪的问题，从返回的响应主体信息中解析下一页的地址时，偶尔会出现问题
			//暂时通过从本地保存的网页中解析，解决了这个问题，但增加了IO开销
			echo str_repeat("  ",2048);
            echo "<font color='red'>没有下一页,原因是</font>：".$this->match_status.",尝试从文件中读取匹配模式<br />";
			sleep(mt_rand(1,2));
			$this->getRollLink(file_get_contents($this->filename));
			
			//当出现这个问题时，通过从文件中读取，100%的解决了这个问题，通常这个问题在开始出现的比较多
			//越往后，越稳定，这个问题也就基本不存在了
			if($this->roll_link)
			{
				$next_url = substr($this->url,0,strripos($this->url, '/')+1).$this->roll_link;
				$this->init($next_url);
			}
			else
			{
				die("<font color='red'>从文件中匹配也失败了......</font><br />");
			}
			
        }

    }

   private function connect()
   {
       $this->conn = fsockopen($this->ip,$this->port,$errno,$errstr,$this->timeout);
       if($this->conn)
       {
           $this->status = '链接成功';
           return true;
       }
       else
       {
            switch($errno)
            {
                case -3:
                        $this->status="创建socket链接失败";
                case -4:
                        $this->status="dns查询失败";
                case -5:
                        $this->status="链接被拒绝或超时";
                default:
                        $this->status="创建连接失败";
            }
            return false;
       }
   }
   
   private function sendRequest()
   {
		//当请求的url中不存在路径时，默认设置为 /  这也是遵循HTTP协议的
       if(empty($this->path))
       {
           $this->path="/";
       }
	   //请求行: 请求方法 请求路径  HTTP版本信息
       $this->cmd_line=$this->http_method." ".$this->path." ".$this->http_version."\r\n";

       if(!empty($this->host))
       {
           $this->header .= "Host: ".$this->host."\r\n";
       }

       if(!empty($this->agent))
       {
           $this->header .="User-Agent: ".$this->agent."\r\n";
       }

       if(!empty($this->accept))
       {
           $this->header .= "Accept: ". $this->accept ."\r\n";
       }
	   
       if(!empty($this->gzip))
       {
           if ( function_exists("gzinflate") )
           {
                $this->header .= "Accept-encoding: gzip\r\n";
            }
            else
            {
                $this->status = "不支持压缩";
            }
       }
	   //第一次请求时,url指向当前页，后续请求时，referer总是指向上一个页面
       if(empty($this->referer))
       {
           $this->header .= "Referer: ".$this->url."\r\n";
		   $this->referer = $this->url;
       }
	   else
	   {
			$this->header .= "Referer: ".$this->referer."\r\n";
	   }
	   
	   //客户端可以接受的语言类型
       if(!empty($this->accept_language))
       {
           $this->header .= "Accept-Language: ".$this->accept_language."\r\n";
       }
	   //设置cookie，第一次请求时为空。第二次请求时，根据第一次请求完成时的头部SET-COOKIE信息来决定
       if(!empty($this->cookie))
       {
           if(!is_array($this->cookie))
           {
               $this->header .="Cookie: ".$this->cookie;
           }
           else
           {
				//如果是数组就循环出每一项
               if(count($this->cookie) >0)
               {
                   $cookie = "Cookie: ";
                   foreach($this->cookie as $key => $val)
                   {
                       $cookie.=$key."=".urlencode($val).";";
                   }
				   //去掉最后的换行符
                  $cookie = substr($cookie, 0, strlen($cookie)-1)."\r\n";
               }
               $this->header .= $cookie;
           }
       }
	   
		//当有主体信息提交时，需要设置content-type为application/x-www-form-urlencoded
       if(!empty($this->post_content))
       {
           $this->header .= "Content-length: ".strlen($this->post_content)."\r\n";
		   if(!empty($this->submit_type))
		   {
			   $this->header .="Content-Type: ".$this->submit_type."\r\n";
		   }
       }
	   
	   //保持长连接
       if(!empty($this->connection))
       {
           $this->header .= "Connection: ".$this->connection."\r\n";
       }
	   //请求头部到这里完成
       $this->header .="\r\n";
      
       //echo $this->cmd_line.$this->header.$this->post_content; exit();
       //发送请求
		$len = strlen($this->cmd_line.$this->header.$this->post_content);
        if($len != @fwrite($this->conn, $this->cmd_line.$this->header.$this->post_content,$len))
        {
            $this->status = "发送请求失败";
			fclose($this->conn);
			flush();
			$this->cmd_line=null;
			$this->header=null;
			echo str_repeat("  ",2048);
            echo $this->status."<font color='red'>尝试重置链接</font><br />";
			$this->init($this->url);
        }
		
       //接受响应，每次读取一行内容，首先解析响应头
       while($response_header = fgets($this->conn, 1024))
       {
           if(preg_match("#^HTTP/#",$response_header))
            {
                //匹配状态数字,200表示请求成功
                if(preg_match("#^HTTP/[^\s]*\s(.*?)\s#",$response_header, $status))
                {
                        $this->response_num= $status[1];//返回代表数字的状态
                }
            }
            
            // 判断是否需要重定向
            if(preg_match("#^(Location:|URI:)#i",$response_header) && substr($this->response_num,0,1) == 3)
            {
                // 获取重定向地址
                preg_match("#^(Location:|URI:)\s+(.*)#",trim($response_header),$matches);

                //如果重定向字段不包含主机名，不是以以://开头的，则拼接王完整的请求地址，模式+主机+端口
                if(!preg_match("#\:\/\/#",$matches[2]))
                {
                    // 补全主机名，这里限制只允许同一个域名下的重定向，也可以修改成允许重定向到其他域名
                    $this->redirect = "http://".$this->host.":".$this->port;

                    //添加路径
                    if(!preg_match("|^/|",$matches[2]))
                           $this->redirect .= "/".$matches[2];
                    else
                           $this->redirect .= $matches[2];
                }
                else
                //包含完整的主机地址
                $this->redirect = $matches[2];
            }

        //判断返回的数据的压缩格式
		  if (preg_match("#^Content-Encoding: gzip#", $response_header) )
          {
                $this->is_gzip = true;
          }
		  
		  //根据返回的头部信息判断主体信息长度
          if(preg_match('#^Content-Length:\s*(\d+)#i', $response_header, $len))
          {
              $this->response_body_length = $len[1];
          }
		  
		  //根据返回的头部信息获取COOKIE，用于下一次发送请求时设置COOKIE
          if(preg_match('#^Set-Cookie:#i', $response_header))
          {
              $items = explode(':', $response_header);
              $this->cookie = explode(';', $items[1])[0];
          }

        //解析完响应头部则跳出循环
        if(preg_match("/^\r?\n$/", $response_header) )
            break;

        $this->response_header[]=$response_header;
       }
      
      
		//如果请求成功且响应码为200
        if($this->response_num==200)
        {
            $sub_dir;
            $dirname;
            $path;
            $filename;
            if(preg_match('#/(\d+)/#', $this->url, $sub_dir))
            {
                $dirname = "./download/".$sub_dir[1];
            }
            else
            {
                $dirname = "./download/".date("Ymd");
            }

            $len=0;
			
			//读取请求返回的主体信息
            while($items = fread($this->conn, $this->response_body_length))
            {
                if(!is_dir($dirname))
                {
                    $path = mkdir($dirname,0777,true);
                }
                $filename = $dirname.'/'.basename($this->url);
				$this->filename = $filename;
                $len = $len+strlen($items);
                $this->response_body = $items;
				
                //当读取完请求的主体信息后跳出循环，不这样做，貌似会被阻塞！！！
                if($len >= $this->response_body_length)
				{
					break;
				}
            }

            if($this->is_gzip)
            {
                $this->response_body = gzinflate ($this->response_body);
            }
            echo str_repeat("  ", 2048);
            echo "对链接".$this->url."发起请求<br />";
			$this->getRollLink($this->response_body);
			if($this->roll_link ===false )
			{
				file_put_contents($filename, $this->response_body, FILE_APPEND);
			}
			$this->getImage($this->response_body);
            
        }
		else if(substr($this->response_num, 0,1)==4)
		{
			if($this->response_num == 400)
			{
				die("服务器不支持的请求");
			}
			else if($this->response_num == 403)
			{
				die("请求被拒绝<br />");
			}
			else if($this->response_num == 404)
			{
				die("请求资源不存在<br />");
			}
			else if($this->response_num == 408)
			{
				die("请求超时<br />");
			}
			else if($this->response_num == 410)
			{
				die("请求的资源被永久移除 <br />");
			}
		}
		else if(substr($this->response_num, 0,1)==5)
		{
			if($this->response_num == 503)
			{
				die("服务器内部错误<br />");
			}
			else if($this->response_num == 502)
			{
				die("网关错误<br />");
			}
			else if($this->response_num == 503)
			{
				die("服务不可用 <br />");
			}
			else if($this->response_num == 504)
			{
				die("网关超时<br />");
			}
		}
		else
		{
			die("未知的错误状态<br />");
		}
   }
   
   //这个函数主要实现分析出下一页的链接
    private function getRollLink($content)
   {
		//匹配出image组的UL范围
       if(preg_match('#<ul\s+class="image"[^>]*?>.*?</ul>#is', $content, $match))
       {
			//匹配出所有的<a>超链接
           if(preg_match_all('#<a\s+[^>]+?>.*?</a>#is', $match[0], $items))
		   {
				//var_dump($items[0]);
				$len = count($items[0]);
				$next_page = $items[0][$len-1];
				//提取出下一页对应的链接地址
				if(preg_match('#<a\s+href="([^"]+)"\s*>#i', $next_page, $link))
				{
					$this->roll_link = $link[1];
				}
				else
				{
					$this->roll_link = false;
					$this->match_status="匹配下一页失败";
				}
		   }
		   else
		   {
				$this->roll_link = false;
				$this->match_status="匹配<a>组失败";
		   }
       }
       else
       {
          $this->roll_link = false;
		  $this->match_status="匹配image分页组失败";
       }
   }
   
   //这个函数主要实现分析出下一组的链接
   private  function getNextGroup($content)
   {
     //匹配下一组标签
	if(preg_match('#<ul\s+class="page"[^>]*?>.*?</ul>#is', $content, $match))
       {
			//echo $match[0]."<br />";
           if(preg_match_all('#<a\s+href="([^"]*?)">.*?</a>#si', $match[0], $next))
           {
                //var_dump($next[1]);
                $choice;
                if(count($next[1])==2)
                {
					//提取出翻租中的数字标签
                    $first = basename($next[1][0], ".html");
                    $second = basename($next[1][1], ".html");
					
                    //当存在上一组  和   下一组时，往前翻页,进入下一组（下一组中的数字标签必须小于上一组的数字标签）
                    if(intval($first) < intval($second))
                    {
                            $choice = $first;
                    }
                    else
                    {
                            $choice = $second;
                    }
                    //h获取下一组
                    foreach($next[1] as $item)
                    {
                        if(strripos($item, $choice) !=false )
                        {
							//根据想对路径补全请求信息
                            if(substr($item, 0,2) =='..')
                            {
                                    $link=  substr($item, 2);
                                    $sub_path = explode('/', $this->path);
                                    $url = $this->scheme.'://'.$this->host.'/'.$sub_path[1].$link;
                                    return $url;
                            }
                        }
                    }
                }
                //当请求的是第一页的信息时，是没有上一组标签的，此时只有一下组标签
                else if(count($next[1])==1)
                {
                      if(substr($next[1][0],0,2)=='..')
                      {
                        $link = substr($next[1][0],2);
                        $sub_path = explode('/', $this->path);
                        $url = $this->scheme.'://'.$this->host.'/'.$sub_path[1].$link;
                        return $url;
                      }
                }
           }
           else
           {
               $this->match_status = "下一组匹配超href失败";
			   return false;
           }
       }
       else
       {
            $this->match_status = "下一组匹配失败";
			return false;
       }
   }
   
   //提取图片超链接
   private function getImage($content)
	{
		if(preg_match('#<ul\s+class="file"\s*>.*?</ul>#is', $content, $match))
		{
			if(preg_match_all('#<img\s+src="([^"]+?)"[^>]+?>#i', $match[0], $items))
			{
				if(count($items[1])>1)
				{
					$link='';
					foreach ($items[1] as $item)
					{
						$link .= $item;
					}
					file_put_contents("links001.txt", $link, FILE_APPEND);
				}
				else
				{
					file_put_contents("links001.txt", $items[1][0], FILE_APPEND);
				}
			}
			else
			{
				echo "匹配IMG标签失败<br />";
			}
		}
		else
		{
			//$temp = file_get_contents($this->filename);
			//$this->getImage($temp);
		}
	}

}
//显式的刷新输出缓存
ob_implicit_flush(true);
//设置不使用超时
set_time_limit(0);

$url = "http://www.mmkao.com/Beautyleg/201412/7066.html";
$http = new HttpWrap();
$http->init($url);
?>
