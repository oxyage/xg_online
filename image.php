<?php

class Image
{

public $resource;
public $size;
public $whitelist;
public $titlelist;
public $filename;
public $teams;

public $modelxG = 321;

public $threshold_xG = 0.1;

function k_xg($xg)
{
$K_xG = 40; // конст
 // экспоненциальная
#$K_xG = 40*(0.3 +		exp( - $xg/0.1)	);
#$K_xG = 28.889*$xg + 1.11;
$K_xG = -15*$xg + 40;
return $K_xG;
}

function __construct($filename)
	{
		$this->resource = imagecreatefrompng($filename);
		$this->size = getimagesize($filename);
		$this->putWhitelist();
		$this->putTitlelist();

	return $this;
	}
function putAuthor()//водяной знак
{
	$this->putText("Data by OPTA.  Created by oxyage",array($this->size[0]/2 - 100, $this->size[1]-110),10, "grey3");
#imagecopy($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
}
function putxG($events_array, $team_id, $side = "home")
{
	//put Shots
	foreach($events_array as $event_num => $event)
	{
		if($event["typeId"] != 16  and $team_id == $event["team"])
		{
			$xG = $event["qualifier"][$this->modelxG];
			if($xG < $this->threshold_xG)
			{
				$xG = $this->threshold_xG;
			}
			$xG *= $this->k_xg($xG);

			if(!strcmp($side,"home")) $main_color = "orange";
			elseif(!strcmp($side,"away")) $main_color = "blue";

			$this->putPoint($this->coord($event["x"],$event["y"],$side),$xG,$main_color,3,"grey4");

			#$c = $this->coord($event["x"],$event["y"],$side);			$this->putText($xG,array($c[1],$c[0]),10, "black");
		}

	}
	//putGoals
	$this->putGoals($events_array, $team_id, $side);


	}//end function putxG

function putGoals($events_array, $team_id, $side)
	{

	//typeId
		foreach($events_array as $event_num => $event)
		{
			if($event["typeId"] == 16 and $team_id == $event["team"] )
			{
				if(!isset($event["qualifier"][9]) and !isset($event["qualifier"][28]))
				{
					$xG = $event["qualifier"][$this->modelxG];
					if($xG < $this->threshold_xG)
					{
						$xG = $this->threshold_xG;
					}
					$xG *= $this->k_xg($xG);

					if(!strcmp($side,"home")) $stroke_color = "blue";
					elseif(!strcmp($side,"away")) $stroke_color = "orange";

					if(!strcmp($side,"home")) $main_color = "purple";
					elseif(!strcmp($side,"away")) $main_color = "purple";


					$this->putPoint($this->coord($event["x"],$event["y"],$side),$xG,$main_color,2,"black");

					$this->putPoint($this->coord($event["x"],$event["y"],$side),1,"grey1");//точка в центре

					#$c = $this->coord($event["x"],$event["y"],$side);					$this->putText($xG,array($c[1],$c[0]),10, "black");

					if($event["qualifier"][$q]["qualifierId"] == 102)	$goaly = $event["qualifier"][$q]["value"];
					if($event["qualifier"][$q]["qualifierId"] == 103)	$goalz = $event["qualifier"][$q]["value"];
					$this->putPoint($this->target($event["qualifier"][102],$event["qualifier"][103],$side), 3,"white",2, "black");
				}
			}

		}

	}

function coord($x,$y, $side="home")
	{
	//смещения координат
	// x,y E [0;100]
	#

	$shift = array("left"=>50, "right"=>50, "top"=>50, "bottom"=>50);

	$d = (!strcmp($side,"home")) ? 2 : 1;

	$full_x = 2*($this->size[1] - $shift["top"] - $shift["bottom"]); //2 * длина половины поля
	$full_y = ($this->size[0]/2 - $shift["left"] - $shift["right"]); //ширина половины поля

	$part_x = ( $full_x * $x ) / 100; // определить неизвестное из пропорции
	$part_y = ( $full_y * $y ) / 100;

	$x = $full_x - $part_x + $shift["top"];
	$y = $this->size[0]/$d - $part_y - $shift["right"];

	return array($x,$y);
	}

function target($y,$z,$side="home")//102 103 qual
	{
	//y E [0,100];   z E [0, 20] для ворот
	$shift = array("left"=>50, "right"=>50, "top"=>13, "bottom"=>730);

	$d = (!strcmp($side,"home")) ? 2 : 1;

	$full_y = ($this->size[0]/2 - $shift["left"] - $shift["right"]); //ширина поля = ширина картинки - смещение слева - смещение справа
	$full_z = ($this->size[1] - $shift["top"] - $shift["bottom"]);

	$part_z = ( $full_z * $z ) / 50; // 20 макс высота + 1 на штангу
	$part_y = ( $full_y * $y ) / 100;

	$y = $this->size[0]/$d - $part_y - $shift["left"];
	$z = $this->size[1] - $part_z - $shift["bottom"];

	return array($z,$y);
	}

function putTimeline($timeline)
{
	$sizex = 1000;	$sizey = 80;
	$shift = array("left"=>450, "bottom"=>30);//от whitelist
	#$shift["left"] -= 150;
	$labelshift = array("left"=>10);
	$whitelist = $this->whitelist;

	//рисуем ось Х ось Y
	$osX = array("x"=>array($whitelist["x"][0] + $shift["left"], $whitelist["x"][0] + $shift["left"] + $sizex + $labelshift["left"]), //после 100%
				 "y"=>array($whitelist["y"][0] - $shift["bottom"], $whitelist["y"][0] - $shift["bottom"]));

	$osY = array("x"=>array($whitelist["x"][0] + $shift["left"], $whitelist["x"][0] + $shift["left"]),
				 "y"=>array($whitelist["y"][0] - $shift["bottom"], $whitelist["y"][0] - $shift["bottom"] - $sizey));

	$this->putLine($osX, 2, "black");
	$this->putLine($osY, 2, "black");

	$last_sec = $timeline["last_sec"];

	$max_xG = ($timeline["max_xG"]["home"] > $timeline["max_xG"]["away"]) ?
	round($timeline["max_xG"]["home"],2) : round($timeline["max_xG"]["away"],2);

#	$timeline["home"][$last_sec] = $timeline["max_xG"]["home"];

#	$timeline["away"][$last_sec] = $timeline["max_xG"]["away"];
	//подписываем оси
	$labelY = $this->putText("xG",
			array($whitelist["x"][0] + $shift["left"]-5, $whitelist["y"][0] - $shift["bottom"]- $sizey - 5),
			12, "black");
	//время от 0 до 90
	for($t = 0; $t <=90; $t+=9)
	{
	$part_x = ($t*60 * ($sizex-10)) / $last_sec;
	$labelY = $this->putText($t,
					array($whitelist["x"][0] + $shift["left"] + $part_x, $whitelist["y"][0] - $shift["bottom"] + 15),
					10, "black");
	}

		$current_xg["home"]=0;
		$current_sec["home"]=0;

		foreach ($timeline["home"] as $sec => $xG)
		{
			$part_x0 =  ($current_sec["home"] * ($sizex-10)) / $last_sec;
			$part_x = (intval($sec) * ($sizex-10)) / $last_sec ;
			$part_y0 = ($current_xg["home"] * ($sizey-10))  / $max_xG;
	#		$part_y = ($current_xg["home"] + intval($xG*1000)/1000) * ($sizey-10)  / $max_xG;//$timeline["max_xG"]["home"];

			$coordLine_home = array(
				"x"=>array($whitelist["x"][0] + $shift["left"] + $part_x0,
							$whitelist["x"][0] + $shift["left"] + $part_x),
				"y"=>array($whitelist["y"][0] - $shift["bottom"]- $part_y0,
						$whitelist["y"][0] - $shift["bottom"] - $part_y0));

			$this->putLine($coordLine_home, 2, "orange");

			$this->putLine(
				array("x"=>array($coordLine_home["x"][0],$coordLine_home["x"][0]),
							"y"=>array($coordLine_home["y"][0],($coordLine_home["y"][0] + $part_y0)  )
						), 1, "orange");

			if(isset($timeline["goals"]["home"][$sec]))
			$this->putPoint(array($whitelist["y"][0] - $shift["bottom"],
														$whitelist["x"][0] + $shift["left"] + $part_x), 4, "grey4", 3, "orange");

				$current_xg["home"] += intval($xG*1000)/1000;
				$current_sec["home"] = intval($sec);
		}

			$this->putText(round($timeline["max_xG"]["home"],3),
		array($whitelist["x"][0] + $shift["left"] + $sizex + $labelshift["left"],
		$whitelist["y"][0] - $shift["bottom"] - intval($timeline["max_xG"]["home"]*1000)/1000 * ($sizey-10)  / $max_xG),
		10,"orange");

//линия гостей

		$current_xg["away"]=0;
		$current_sec["away"]=0;

		foreach ($timeline["away"] as $sec => $xG)
		{
			$part_x0 =  ($current_sec["away"] * ($sizex-10)) / $last_sec ;
			$part_x = (intval($sec) * ($sizex-10)) / $last_sec ;
			$part_y0 = ($current_xg["away"] * ($sizey-10))  / $max_xG;
	#		$part_y = ($current_xg["away"] + intval($xG*1000)/1000) * ($sizey-10)  / $max_xG;//$timeline["max_xG"]["away"];

			$coordLine_away = array(
				"x"=>array($whitelist["x"][0] + $shift["left"] + $part_x0,
							$whitelist["x"][0] + $shift["left"] + $part_x),
				"y"=>array($whitelist["y"][0] - $shift["bottom"]- $part_y0,
						$whitelist["y"][0] - $shift["bottom"] - $part_y0));

				$this->putLine($coordLine_away, 2, "blue");

				$this->putLine(
					array("x"=>array($coordLine_away["x"][0],$coordLine_away["x"][0]),
								"y"=>array($coordLine_away["y"][0],$coordLine_away["y"][0] + $part_y0 )
							), 1, "blue");

			if(isset($timeline["goals"]["away"][$sec]))
			$this->putPoint(array($whitelist["y"][0] - $shift["bottom"],
														$whitelist["x"][0] + $shift["left"] + $part_x), 4, "grey4", 3, "blue");

				$current_xg["away"] += intval($xG*1000)/1000;
				$current_sec["away"] = intval($sec);
		}


			$this->putText(round($timeline["max_xG"]["away"],3),
			array($whitelist["x"][0] + $shift["left"] + $sizex + $labelshift["left"],
			$whitelist["y"][0] - $shift["bottom"] - intval($timeline["max_xG"]["away"]*1000)/1000 * ($sizey-10)  / $max_xG),
			10,"blue");

}

function putHistogram($data, $side)
	{
#		print_r($data[$side]);
//   250!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	$sizex = 300;	$sizey = 80;

	if(!strcmp($side,"home"))
	{
			$shift = array("left"=>0, "bottom"=>30);//от whitelist
			$name_color = "orange";
	}
	elseif(!strcmp($side,"away"))
	{
			$shift = array("left"=>$this->size[0]-2*$sizex+125, "bottom"=>30);//от whitelist
			$name_color = "blue";
	}

	$shift["left"] += 25;

	$labelshift = array("left"=>10);

	$max_n = 0;
	$max_int = 0;
	foreach($data[$side] as $interval => $n)
	{
		if($n > $max_n) $max_n = $n;
		if($interval > $max_int) $max_int = $interval;
	}

	$whitelist = $this->whitelist;

	if(!strcmp($side,"home"))				$fill_color = array(228,120,50);//"orange";
	elseif(!strcmp($side,"away"))		$fill_color = array(0,200,255);// "blue";
	//блоки гистограммы
	$columns = $sizex / sizeof($data[$side]);
	foreach($data[$side] as $interval => $n)
		{

				//координаты для блоков
				$coordR = array(
					"x"=>array($whitelist["x"][0] + $shift["left"] + $interval*$sizex/$max_int - $columns,
								$whitelist["x"][0] + $shift["left"] + $interval*$sizex/$max_int ),
					"y"=>array($whitelist["y"][0] - $shift["bottom"],
							$whitelist["y"][0] - $shift["bottom"] - ($n*$sizey)/$max_n));
				$this->putFillRectangle($coordR, $fill_color, 1, "black");
		}
		//подпись на блоках N
	foreach($data[$side] as $interval => $n)
		{
		$legendN = $this->putText($n,
					array($whitelist["x"][0] + $shift["left"] + $interval*$sizex/$max_int -$columns/2,
						$whitelist["y"][0] - $shift["bottom"] - ($n*$sizey)/$max_n/2 - 5),
					10,"black");
		}

	//рисуем ось Х ось Y
	$osX = array("x"=>array($whitelist["x"][0] + $shift["left"], $whitelist["x"][0] + $shift["left"] + $sizex + $labelshift["left"]), //после 100%
				 "y"=>array($whitelist["y"][0] - $shift["bottom"], $whitelist["y"][0] - $shift["bottom"]));

	$osY = array("x"=>array($whitelist["x"][0] + $shift["left"], $whitelist["x"][0] + $shift["left"]),
				 "y"=>array($whitelist["y"][0] - $shift["bottom"], $whitelist["y"][0] - $shift["bottom"] - $sizey));

	$this->putLine($osX, 2, "black");
	$this->putLine($osY, 2, "black");

	//подписываем координаты
	$labelX = $this->putText("xG", 							//координаты сдвига
			array($whitelist["x"][0] + $shift["left"] + $sizex + $labelshift["left"]+5, $whitelist["y"][0] - $shift["bottom"]),
			12, "black");
	$labelY = $this->putText("Shots",
			array($whitelist["x"][0] + $shift["left"], $whitelist["y"][0] - $shift["bottom"]- $sizey - 5),
			12, "black");
	$labelY = $this->putText($this->teams[$side],
					array($whitelist["x"][0] + $shift["left"]+50, $whitelist["y"][0] - $shift["bottom"]- $sizey - 5),
					12, $name_color);

			//подпись снизу
	foreach($data[$side] as $interval => $n)
		{
			#	$interval /= 100;
				$legendxG = $this->putText(($interval/=100),
				array($whitelist["x"][0] + $shift["left"] + $interval*100*$sizex/$max_int - 15,
					$whitelist["y"][0] - $shift["bottom"] + 15),
				10,"black");
		}


	}//end function putHistogram

function textCentre()
{

}
function textTitle($title, $row, $size=15)
	{
		$titlelist = $this->titlelist;
	#	$size = 15;
		$shift = array("left"=>10, "up"=>20);
		$box = imagettfbbox($size, 0, "times_new_roman.ttf", $title);
		$dx = $titlelist["x"][1] - $titlelist["x"][0];
		$dx_box = $box[2] - $box[0];
		$dy_box = $size + 5;//($box[3]+5) - ($box[5]-5);


		$x = $titlelist["x"][0] + $dx/2 - $dx_box/2;
		$y = $titlelist["y"][0] + $shift["up"] + $dy_box*$row;
/*
		switch($row)
		{
			case 0: // pos 0
			{
				$x = $titlelist["x"][0] + $dx/2 - $dx_box/2;
				$y = $titlelist["y"][0] + $shift["up"] + $dy_box*0;
				break;
			}
			case 1: // pos 0
			{
				$x = $titlelist["x"][0] + $dx/2 - $dx_box/2;
				$y = $titlelist["y"][0] + $shift["up"] + $dy_box*1;
				break;
			}
			case 2: // pos 0
			{
				$x = $titlelist["x"][0] + $dx/2 - $dx_box/2;
				$y = $titlelist["y"][0] + $shift["up"] + $dy_box*2;
				break;
			}
				default:
			{
				$x = $titlelist["x"][0] + $shift["left"]+80;
				$y = $titlelist["y"][0] + $shift["up"]+80;
				break;
			}
		}
	*/
#		imagettftext($this->resource, $size, 0, $x-50, $y+50, $this->color("black"), "times_new_roman.ttf", $dy_box);
		imagettftext($this->resource, $size, 0, $x, $y, $this->color("black"), "times_new_roman.ttf", $title);
/*
	$titlelist = $this->titlelist;
	$shift = array("bottom"=>0, "left"=>10, "up"=>20);

	$size = 15;
	$box = imagettfbbox($size, 0, "times_new_roman.ttf", $title);
	//$x = (($titlelist["x"][1] - $titlelist["x"][0]) / 2);
	$x = $titlelist["x"][0] + $shift["left"];
	$y = $titlelist["y"][0] + $shift["up"];
	//$y = (($titlelist["y"][1] - $titlelist["y"][0]) / 2) + $shift["bottom"];
	imagettftext($this->resource, $size, 0, $x-50, $y+50, $this->color("black"), "times_new_roman.ttf", $box[2]);

	imagettftext($this->resource, $size, 0, $x, $y, $this->color("black"), "times_new_roman.ttf", $title);
*/
	}
function putTitlelist()//
	{
	$coord["x"] = array(850, 1150);
	$coord["y"] = array(0, 185);
//	$coord["y"] = array($this->whitelist["y"][1], $this->whitelist["y"][1]-100);
	$this->putFillRectangle($coord, "white", 1, "black");
	$this->titlelist = $coord;
	}

function textWhitelist($text, $team_id, $side = "home")
	{
	$whitelist = $this->whitelist;
	$shift = array("bottom"=>80);

	$size = 14;
	$box = imagettfbbox($size, 0, "times_new_roman.ttf", $text);
	$center = $this->size[0]/2 - round(($box[2]-$box[0])/2);
	imagettftext($this->resource, $size, 0, $center, 	$whitelist["y"][0] - $shift["bottom"] , $this->color("black"), "times_new_roman.ttf", $text);
	}
function putWhitelist()//
	{
	$coord["x"] = array(50, $this->size[0]-50);
	$coord["y"] = array($this->size[1], $this->size[1]-130);
	$this->putFillRectangle($coord, "white", 1, "black");
	$this->whitelist = $coord;
	}

function color($color)
	{
	$result = ImageColorAllocate($this->resource,0,0,0);
	if(is_array($color))	{    $result = ImageColorAllocate($this->resource,$color[0],$color[1],$color[2]);  }
	else
	{
		switch($color)
		{
			case "black": 	{$result=ImageColorAllocate( $this->resource, 0, 0, 0 ); 	break;}
			case "grey1": 	{$result=ImageColorAllocate( $this->resource, 50, 50, 50 ); 		break;}
			case "grey2": 	{$result=ImageColorAllocate( $this->resource, 100, 100, 100 ); 	break;}
			case "grey": 	{$result=ImageColorAllocate( $this->resource, 128, 128, 128 ); 	break;}
			case "grey3": 	{$result=ImageColorAllocate( $this->resource, 150, 150, 150 ); 	break;}
			case "grey4": 	{$result=ImageColorAllocate( $this->resource, 200, 200, 200 ); 	break;}
			case "grey5": 	{$result=ImageColorAllocate( $this->resource, 224, 224, 224 ); 	break;}
			case "white": 	{$result=ImageColorAllocate( $this->resource, 255, 255, 255 ); 	break;}

			case "red": 	{$result=ImageColorAllocate( $this->resource, 255, 0, 0 );			break;}
			case "orange": 	{$result=ImageColorAllocate( $this->resource, 228, 100, 0 ); 		break;}
			case "yellow": 	{$result=ImageColorAllocate( $this->resource, 255, 255, 0 ); 		break;}
			case "darkyellow": 	{$result=ImageColorAllocate( $this->resource, 220, 220, 0 );		break;}
			case "gold": 	{$result=ImageColorAllocate( $this->resource, 180, 180, 0 );		break;}
			case "green": 	{$result=ImageColorAllocate( $this->resource, 0, 255, 0 ); 		break;}
			case "green1": 	{$result=ImageColorAllocate( $this->resource, 95, 115, 45 ); 		break;}
			case "green2": 	{$result=ImageColorAllocate( $this->resource, 130, 145, 60 ); 		break;}
			case "darkgreen": 	{$result=ImageColorAllocate( $this->resource, 30, 160, 70 ); 		break;}
			case "lightblue": 	{$result=ImageColorAllocate( $this->resource, 0, 200, 255 ); 		break;}
			case "blue": 	{$result=ImageColorAllocate( $this->resource, 0, 100, 255 ); 		break;}
			#case "blue": 	{$result=ImageColorAllocate( $this->resource, 0, 0, 255 ); 		break;}
			case "purple": 	{$result=ImageColorAllocate( $this->resource, 163, 73, 164 ); 		break;}

			default: {break;}
		}
	}
	return $result;
	}

function putFillRectangle($coord, $fill, $stroke_px = 0, $stroke_color = 0)
	{
	//заливка
	imagefilledrectangle($this->resource,
	$coord["x"][0], $coord["y"][0],
	$coord["x"][1], $coord["y"][1],
	$this->color($fill));
	//обводка цвет
	if(!$stroke_color){$stroke_color = $this->color("black");}
	else $stroke_color = $this->color($stroke_color);
	//обводка px
	if($stroke_px > 0)
		{
		imageSetThickness($this->resource, $stroke_px);
		imagerectangle( $this->resource , $coord["x"][0], $coord["y"][0],
		$coord["x"][1], $coord["y"][1], $stroke_color );
		}

	}
function putLine($coord, $weight, $color)
	{
	if(!isset($coord["x"][1]))$coord["x"][1] = $coord["x"][0];
	if(!isset($coord["y"][1]))$coord["y"][1] = $coord["y"][0];
	imageSetThickness($this->resource, $weight);
	imageLine($this->resource,
	$coord["x"][0], $coord["y"][0],
	$coord["x"][1], $coord["y"][1], $this->color($color));
	}
function putPoint($coord, $radius, $main_color, $stroke_px=0, $stroke_color=0)
	{
	$coord_x = $coord[1];
	$coord_y = $coord[0];
	ImageFilledEllipse($this->resource, $coord_x, $coord_y, (2*$radius), (2*$radius), $this->color($stroke_color) );//обводка
	ImageFilledEllipse($this->resource, $coord_x, $coord_y, (2*$radius-$stroke_px), (2*$radius-$stroke_px), $this->color($main_color));//цвет
	}
function putText($text, $coord, $size, $main_color)
	{
	$coord_x = $coord[0];
	$coord_y = $coord[1];
	$font = "times_new_roman.ttf";
	$angle = 0;
	imagettftext($this->resource, $size, $angle, $coord_x, $coord_y,$this->color($main_color),$font, $text);
	}

}//end class Image


?>
