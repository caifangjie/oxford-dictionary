<html>
    <head>
        <style text="text/css">
            *{margin:0px; padding: 0px;}

div#layout{margin-left: 410px; width:700px;}

span#title{font-family: arial;
                    font-size: 18px;
                    color:#eebb00;
                    margin-top: 2px;
                    margin-left: 200px; }

div#trans{			
                    margin-left: 410px;
					margin-top:1px;
					width:715px;
                    font-family: arial;
                    font-size: 16px;
                    color:#ee00ab;
                    background-color: #666;
                    border:1px solid #eebcbc;
                    
					
            }

#content{width:720px;
		height:540px;
		border:0px ; 
		overflow-y:scroll;
		
		margin-top:1px;
        font-family: arial;
		font-size: 18px;
		color:#666;
        line-height: 25px;
		padding:10px;}

            </style>

            <script type="text/javascript">
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

function query(word)
{
    var url="./trans.process.php?word="+word;
    var xhr = getXMLHttpRequest();
     if(xhr)
    {
        xhr.open("get", url ,true);
        xhr.onreadystatechange = function()
        {
            if(xhr.readyState ==4 && xhr.status==200)
                {
                    var trans = xhr.responseText;
					$("trans").innerHTML =word+":  "+ trans;
                }
        }
        xhr.send(null);
    }
}

function getSelectText(event){
    //捕捉选取的文本
    var txt = $("content").value.substring($("content").selectionStart, $("content").selectionEnd);
    var regx = /[a-z]+/i;
    //截取首个单词
    var word = txt.match(regx);
   query(word);
}

function $(id)
{
        return document.getElementById(id);
}
</script>
        </head>
        <body bgcolor="#RGB(67,65,65)">
            <img src="../image/speech.jpg" style="margin-left:410px; margin-top:2px;" /><br />
		<div id='trans'></div>
<div id="layout">
﻿<?php
require_once 'readSpeech.class.php';
        if(!empty($_GET['id']))
        {
            $id = $_GET['id'];
            $readSpeech = new ReadSpeech();
            $result = $readSpeech->getSpeechById($id);

            $content = file_get_contents($result[1]);
            echo "<span id='title'>{$result[0]}</span><br />";

            echo "<textarea id='content' onselect='getSelectText();'>{$content}</textarea>";

        }
?>
</div>
</body>
</html>