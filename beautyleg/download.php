<?php
header("content-type: text/html; charset=gb2312");
class HttpWrap
{
	//���峬ʱʱ��
    public $timeout=10;
	
	//��������״̬
    public $status='';

	//������
    public $host;
	
	//�˿ں�
    public $port=80;
	
	//��һ������ʱ��������������IP
    private $ip;
	
	//������Դ
    private $conn;
	
	//���ӵĵ�ַ
	private $url;
	
	//����URL�е�·��
    private $path;
    
	//URL�а�����ģʽ������FTP,HTTPS
    private $scheme;

	//����ʽ������GET,POST,PUT
    public $http_method='GET';
	
	//HTTP�İ汾��Ϣ
    public $http_version="HTTP/1.1";
	
	//���������Ϣ
    public $agent="Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0";
	
	//����ɽ��յ�MIME��Ϣ
    public $accept="image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*";
	
	//ѹ����ʽ
    public $gzip="gzip";
	
	//�ϼ������������ӵ�ַ
    public $referer;
	
	//����COOKIE
    public $cookie;
	
	//�ύ����
    public $submit_type="application/x-www-form-urlencoded";
	
	//�ɽ��յ���������
    public $accept_language="zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3";
	
	//���ֳ�����
    public $connection="keep-alive";

	//HTTP������
    private $cmd_line;
	
	//HTP����ͷ��
    private $header;
	
	//HTTP�����������������Ϣ
    public $post_content;

	//�ض����ַ
    private $redirect;
	
	//�Ƿ�֧��GZIPѹ��
    private $is_gzip;
	
	//HTTP��Ӧ״̬��,����200,404��
    public $response_num;
	
	//HTTP��Ӧͷ����Ϣ
    public $response_header;
	
	//HTTP��Ӧ����
	public $response_body;
	
	//HTTP��Ӧ������Ϣ�ĳ���
    public $response_body_length=0;
    
	//���ڱ�����һҳ��ַ
    public $roll_link;
	
	//���ڱ�����һ���ַ
    public $roll_group;
	
	//�����ж��Ƿ�ƥ�䵽����һҳ������һ��
	public $match_status=0;
	
	//��Ӧ������Ϣ�������Ǹ�·���µ��ĸ��ļ���
    public $filename;
	
	//��Ӧ�ı�����Ϣ
    public $encoding;
	

   public  function init($url)
    {
        $this->url=$url;
		//����url��Ϣ
        $url_pair = parse_url($url);
		
		//����������
        $this->host = $url_pair['host'];
		//����url�а�����·����Ϣ
        $this->path = $url_pair['path'];
		//����ʹ�õ�ģʽ��Ϣ
        $this->scheme = $url_pair['scheme'];

		//ͨ��������������Ӧ��IP��ַ,���ڼ��������ӳ�ʱ���������ӱ�����ʱ������
        if(empty($this->ip))
        {
            $this->ip = gethostbyname($this->host);
        }

        if(!empty($url_pair['port']))
        {
            $this->port = $url_pair['port'];
        }
     
		//������ӵ�Զ�������ɹ�����������
		if($this->connect())
		{
			$this->sendRequest();
		}
		else
		{	
			//�������ʧ�ܣ������߼��룬��������,����������粻�ȶ�ʱ
			echo str_repeat("  ", 2048);
			echo $this->status.",  <font color='red'>�����쳣������������....</font></br />";
			$this->conn=null;
			sleep(mt_rand(3,5));
			$this->init($this->url);
		}
		
        //�����Ӧͷ�������ض�������ض���������
        if($this->redirect)
        {
			//Ĭ������ֻ����Ե�ǰ�����µ��������ض��򣬱���ҳ������ת
            if(preg_match("#^http://".preg_quote($this->host)."#i",$this->redirect))
            {
                $this->referer=$this->host."/".parse_url($this->redirect)['path'];
                $this->init($this->redirect);
            }
        }
        if($this->roll_link)
        {
            $next_url = substr($this->url,0,strripos($this->url, '/')+1).$this->roll_link;
			
            //�����һҳ���ڵ�ǰҳ,Ҳ�������һҳʱ������һ����ҳ��������
            if(strtolower(trim(basename($this->url,'.html'))) == strtolower(trim(basename($next_url,'.html'))))
            {
               $next_group = $this->getNextGroup($this->response_body);
               echo "<font color='color'>�����ɼ���һ��</font><br />";
			   if($next_group)
			   {
					$this->init($next_group);
			   }
			   else
			   {	
					//���������һ��ĵ�ַʧ��ʱ�����浽��־,,,�����������ԣ�������ⲻ����
					file_put_contents("log.txt", $this->url, FILE_APPEND);
					die($this->match_status);
			   }
               
            }
            else
            {
				//����һҳ��������
                $this->init($next_url);
            }

        }
        else
        {
			//����һ����ֵ����⣬�ӷ��ص���Ӧ������Ϣ�н�����һҳ�ĵ�ַʱ��ż�����������
			//��ʱͨ���ӱ��ر������ҳ�н����������������⣬��������IO����
			echo str_repeat("  ",2048);
            echo "<font color='red'>û����һҳ,ԭ����</font>��".$this->match_status.",���Դ��ļ��ж�ȡƥ��ģʽ<br />";
			sleep(mt_rand(1,2));
			$this->getRollLink(file_get_contents($this->filename));
			
			//�������������ʱ��ͨ�����ļ��ж�ȡ��100%�Ľ����������⣬ͨ����������ڿ�ʼ���ֵıȽ϶�
			//Խ����Խ�ȶ����������Ҳ�ͻ�����������
			if($this->roll_link)
			{
				$next_url = substr($this->url,0,strripos($this->url, '/')+1).$this->roll_link;
				$this->init($next_url);
			}
			else
			{
				die("<font color='red'>���ļ���ƥ��Ҳʧ����......</font><br />");
			}
			
        }

    }

   private function connect()
   {
       $this->conn = fsockopen($this->ip,$this->port,$errno,$errstr,$this->timeout);
       if($this->conn)
       {
           $this->status = '���ӳɹ�';
           return true;
       }
       else
       {
            switch($errno)
            {
                case -3:
                        $this->status="����socket����ʧ��";
                case -4:
                        $this->status="dns��ѯʧ��";
                case -5:
                        $this->status="���ӱ��ܾ���ʱ";
                default:
                        $this->status="��������ʧ��";
            }
            return false;
       }
   }
   
   private function sendRequest()
   {
		//�������url�в�����·��ʱ��Ĭ������Ϊ /  ��Ҳ����ѭHTTPЭ���
       if(empty($this->path))
       {
           $this->path="/";
       }
	   //������: ���󷽷� ����·��  HTTP�汾��Ϣ
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
                $this->status = "��֧��ѹ��";
            }
       }
	   //��һ������ʱ,urlָ��ǰҳ����������ʱ��referer����ָ����һ��ҳ��
       if(empty($this->referer))
       {
           $this->header .= "Referer: ".$this->url."\r\n";
		   $this->referer = $this->url;
       }
	   else
	   {
			$this->header .= "Referer: ".$this->referer."\r\n";
	   }
	   
	   //�ͻ��˿��Խ��ܵ���������
       if(!empty($this->accept_language))
       {
           $this->header .= "Accept-Language: ".$this->accept_language."\r\n";
       }
	   //����cookie����һ������ʱΪ�ա��ڶ�������ʱ�����ݵ�һ���������ʱ��ͷ��SET-COOKIE��Ϣ������
       if(!empty($this->cookie))
       {
           if(!is_array($this->cookie))
           {
               $this->header .="Cookie: ".$this->cookie;
           }
           else
           {
				//����������ѭ����ÿһ��
               if(count($this->cookie) >0)
               {
                   $cookie = "Cookie: ";
                   foreach($this->cookie as $key => $val)
                   {
                       $cookie.=$key."=".urlencode($val).";";
                   }
				   //ȥ�����Ļ��з�
                  $cookie = substr($cookie, 0, strlen($cookie)-1)."\r\n";
               }
               $this->header .= $cookie;
           }
       }
	   
		//����������Ϣ�ύʱ����Ҫ����content-typeΪapplication/x-www-form-urlencoded
       if(!empty($this->post_content))
       {
           $this->header .= "Content-length: ".strlen($this->post_content)."\r\n";
		   if(!empty($this->submit_type))
		   {
			   $this->header .="Content-Type: ".$this->submit_type."\r\n";
		   }
       }
	   
	   //���ֳ�����
       if(!empty($this->connection))
       {
           $this->header .= "Connection: ".$this->connection."\r\n";
       }
	   //����ͷ�����������
       $this->header .="\r\n";
      
       //echo $this->cmd_line.$this->header.$this->post_content; exit();
       //��������
		$len = strlen($this->cmd_line.$this->header.$this->post_content);
        if($len != @fwrite($this->conn, $this->cmd_line.$this->header.$this->post_content,$len))
        {
            $this->status = "��������ʧ��";
			fclose($this->conn);
			flush();
			$this->cmd_line=null;
			$this->header=null;
			echo str_repeat("  ",2048);
            echo $this->status."<font color='red'>������������</font><br />";
			$this->init($this->url);
        }
		
       //������Ӧ��ÿ�ζ�ȡһ�����ݣ����Ƚ�����Ӧͷ
       while($response_header = fgets($this->conn, 1024))
       {
           if(preg_match("#^HTTP/#",$response_header))
            {
                //ƥ��״̬����,200��ʾ����ɹ�
                if(preg_match("#^HTTP/[^\s]*\s(.*?)\s#",$response_header, $status))
                {
                        $this->response_num= $status[1];//���ش������ֵ�״̬
                }
            }
            
            // �ж��Ƿ���Ҫ�ض���
            if(preg_match("#^(Location:|URI:)#i",$response_header) && substr($this->response_num,0,1) == 3)
            {
                // ��ȡ�ض����ַ
                preg_match("#^(Location:|URI:)\s+(.*)#",trim($response_header),$matches);

                //����ض����ֶβ���������������������://��ͷ�ģ���ƴ���������������ַ��ģʽ+����+�˿�
                if(!preg_match("#\:\/\/#",$matches[2]))
                {
                    // ��ȫ����������������ֻ����ͬһ�������µ��ض���Ҳ�����޸ĳ������ض�����������
                    $this->redirect = "http://".$this->host.":".$this->port;

                    //���·��
                    if(!preg_match("|^/|",$matches[2]))
                           $this->redirect .= "/".$matches[2];
                    else
                           $this->redirect .= $matches[2];
                }
                else
                //����������������ַ
                $this->redirect = $matches[2];
            }

        //�жϷ��ص����ݵ�ѹ����ʽ
		  if (preg_match("#^Content-Encoding: gzip#", $response_header) )
          {
                $this->is_gzip = true;
          }
		  
		  //���ݷ��ص�ͷ����Ϣ�ж�������Ϣ����
          if(preg_match('#^Content-Length:\s*(\d+)#i', $response_header, $len))
          {
              $this->response_body_length = $len[1];
          }
		  
		  //���ݷ��ص�ͷ����Ϣ��ȡCOOKIE��������һ�η�������ʱ����COOKIE
          if(preg_match('#^Set-Cookie:#i', $response_header))
          {
              $items = explode(':', $response_header);
              $this->cookie = explode(';', $items[1])[0];
          }

        //��������Ӧͷ��������ѭ��
        if(preg_match("/^\r?\n$/", $response_header) )
            break;

        $this->response_header[]=$response_header;
       }
      
      
		//�������ɹ�����Ӧ��Ϊ200
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
			
			//��ȡ���󷵻ص�������Ϣ
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
				
                //����ȡ�������������Ϣ������ѭ��������������ò�ƻᱻ����������
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
            echo "������".$this->url."��������<br />";
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
				die("��������֧�ֵ�����");
			}
			else if($this->response_num == 403)
			{
				die("���󱻾ܾ�<br />");
			}
			else if($this->response_num == 404)
			{
				die("������Դ������<br />");
			}
			else if($this->response_num == 408)
			{
				die("����ʱ<br />");
			}
			else if($this->response_num == 410)
			{
				die("�������Դ�������Ƴ� <br />");
			}
		}
		else if(substr($this->response_num, 0,1)==5)
		{
			if($this->response_num == 503)
			{
				die("�������ڲ�����<br />");
			}
			else if($this->response_num == 502)
			{
				die("���ش���<br />");
			}
			else if($this->response_num == 503)
			{
				die("���񲻿��� <br />");
			}
			else if($this->response_num == 504)
			{
				die("���س�ʱ<br />");
			}
		}
		else
		{
			die("δ֪�Ĵ���״̬<br />");
		}
   }
   
   //���������Ҫʵ�ַ�������һҳ������
    private function getRollLink($content)
   {
		//ƥ���image���UL��Χ
       if(preg_match('#<ul\s+class="image"[^>]*?>.*?</ul>#is', $content, $match))
       {
			//ƥ������е�<a>������
           if(preg_match_all('#<a\s+[^>]+?>.*?</a>#is', $match[0], $items))
		   {
				//var_dump($items[0]);
				$len = count($items[0]);
				$next_page = $items[0][$len-1];
				//��ȡ����һҳ��Ӧ�����ӵ�ַ
				if(preg_match('#<a\s+href="([^"]+)"\s*>#i', $next_page, $link))
				{
					$this->roll_link = $link[1];
				}
				else
				{
					$this->roll_link = false;
					$this->match_status="ƥ����һҳʧ��";
				}
		   }
		   else
		   {
				$this->roll_link = false;
				$this->match_status="ƥ��<a>��ʧ��";
		   }
       }
       else
       {
          $this->roll_link = false;
		  $this->match_status="ƥ��image��ҳ��ʧ��";
       }
   }
   
   //���������Ҫʵ�ַ�������һ�������
   private  function getNextGroup($content)
   {
     //ƥ����һ���ǩ
	if(preg_match('#<ul\s+class="page"[^>]*?>.*?</ul>#is', $content, $match))
       {
			//echo $match[0]."<br />";
           if(preg_match_all('#<a\s+href="([^"]*?)">.*?</a>#si', $match[0], $next))
           {
                //var_dump($next[1]);
                $choice;
                if(count($next[1])==2)
                {
					//��ȡ�������е����ֱ�ǩ
                    $first = basename($next[1][0], ".html");
                    $second = basename($next[1][1], ".html");
					
                    //��������һ��  ��   ��һ��ʱ����ǰ��ҳ,������һ�飨��һ���е����ֱ�ǩ����С����һ������ֱ�ǩ��
                    if(intval($first) < intval($second))
                    {
                            $choice = $first;
                    }
                    else
                    {
                            $choice = $second;
                    }
                    //h��ȡ��һ��
                    foreach($next[1] as $item)
                    {
                        if(strripos($item, $choice) !=false )
                        {
							//�������·����ȫ������Ϣ
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
                //��������ǵ�һҳ����Ϣʱ����û����һ���ǩ�ģ���ʱֻ��һ�����ǩ
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
               $this->match_status = "��һ��ƥ�䳬hrefʧ��";
			   return false;
           }
       }
       else
       {
            $this->match_status = "��һ��ƥ��ʧ��";
			return false;
       }
   }
   
   //��ȡͼƬ������
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
				echo "ƥ��IMG��ǩʧ��<br />";
			}
		}
		else
		{
			//$temp = file_get_contents($this->filename);
			//$this->getImage($temp);
		}
	}

}
//��ʽ��ˢ���������
ob_implicit_flush(true);
//���ò�ʹ�ó�ʱ
set_time_limit(0);

$url = "http://www.mmkao.com/Beautyleg/201412/7066.html";
$http = new HttpWrap();
$http->init($url);
?>
