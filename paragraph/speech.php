<html>
    <head>
        <style text="text/css">
            *{margin:0px; padding:0px;}
            div#link{margin-left:510px; margin-top:50px; width:700px;}
            a#speech{
                              font-family: arial;  font-size:22px; color: #888888;
                              margin-bottom: 15px;
                             }
            </style>
        </head>
        <body bgcolor="#RGB(67,65,65)">
            <img src="../image/speech.jpg" style="margin-left:410px; margin-top:2px;" /><br />
    <div id="link">

<?php
require_once 'readSpeech.class.php';
$readSpeech = new ReadSpeech();
$items = $readSpeech->getAllSpeech();
foreach ($items as $item)
{
    echo "<a id='speech' href='speechContent.php?id={$item[0]}' target='_blank'>{$item[1]} </a><br /><br />";
}
?>
        </div>
</body>
</html>