<?php

include("func.php");

//определить режим
if(isValid($_GET["comp"]))
{
$comp = $_GET["comp"];
$season = isValid($_GET["season"]) ? $_GET["season"] : 2018;
$calendar_format = "calendar/Calendar-%comp%-%season%.json";
$calendar_format = str_replace("%comp%",$comp,$calendar_format);
$calendar_format = str_replace("%season%",$season,$calendar_format);

if(!$get = file_get_contents($calendar_format)) exit("Error 1");
$get = json_decode($get,true);

#echo "<pre>"; print_r($get); echo "</pre>";
  for($i=0; $i<sizeof($get["matches"]); $i++)
  {
    ?>
    <a href="main.php?mode=ajax&get=image&&file=<?=$get["matches"][$i]?>"><?=$get["matches"][$i]["id"]?></a><br>

    <?

  }


}


 ?>
