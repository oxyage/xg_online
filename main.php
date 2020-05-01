<?php

//подключаем файл с функциями
include("func.php");

//определить режим
if(isValid($_GET["mode"]) and !strcmp($_GET["mode"],"ajax"))
{

	if(isValid($_GET["get"]) and !strcmp($_GET["get"],"passmap"))
	{

		include("passmap.php");
	//	echo var_dump($_POST["passmap"]);
		$passmap = new Passmap($_POST["passmap"]);
		$passmap->addTable();
		echo json_encode($passmap);

	}
	elseif(isValid($_GET["get"]) and !strcmp($_GET["get"],"xg"))
	{
		include("xg.php");
		$xg = new xG($_POST["xg"]);
		echo json_encode($xg);
		createFile("xg/".$xg->match_info["id"].".json", $_POST["xg"]);
	}
	elseif(isValid($_GET["get"]) and !strcmp($_GET["get"],"image"))
	{
		include("image.php");
		include("xg.php");
		#include("db.php");

		if(isValid($_GET["file"]))
		{
			$file = "xg/".$_GET["file"].".json";
		}

		$file = file_get_contents($file);
		$xg = new xG($file);
		$home_team_id = $xg->match_info["teams"][0]["id"];
		$away_team_id = $xg->match_info["teams"][1]["id"];

		$png = "football_double.png";
		$image = new Image($png);
		#$image->modelxG = 321;
		$xg->modelxG = 321;

		$image->filename = $xg->match_info["dateZ"].$xg->match_info["shortDescription"].".png";

		$image->putAuthor();
		$image->teams = array("home"=>$xg->match_info["teams"][0]["shortName"],"away"=>$xg->match_info["teams"][1]["shortName"]);
		$image->putHistogram($xg->match_info["xG"]["histrogram"],"home");
		$image->putHistogram($xg->match_info["xG"]["histrogram"],"away");

		$image->putTimeline($xg->match_info["xG"]["timeline"]);

		$image->putxG($xg->events,$home_team_id, "home");
		$image->putxG($xg->events,$away_team_id, "away");

		$isPenaltyHome = ($xg->match_info["scores"]["penaltyGoal"]["home"] > 0 )?
	"(+".$xg->match_info["scores"]["penaltyGoal"]["home"]." pen) " : "";

		$isPenaltyAway = ($xg->match_info["scores"]["penaltyGoal"]["away"] > 0 )?
	" (+".$xg->match_info["scores"]["penaltyGoal"]["away"]." pen) " : "";

		$total_score = $xg->match_info["scores"]["total"]["home"]." - ".$xg->match_info["scores"]["total"]["away"];
		$ht_score = $xg->match_info["scores"]["ht"]["home"]." : ".$xg->match_info["scores"]["ht"]["away"];

		$total_xG = $isPenaltyHome.round($xg->match_info["xG"]["ft"]["home"],2)." - ".round($xg->match_info["xG"]["ft"]["away"],2).$isPenaltyAway;
		$ht_xG = round($xg->match_info["xG"]["ht"]["home"],2)." : ".round($xg->match_info["xG"]["ht"]["away"],2);


		$title = $xg->match_info["date"]."\n".
		$xg->match_info["competition_country"].". ".$xg->match_info["competition_name"] ."\n".
		$xg->match_info["shortDescription"]."\nScore: ".$total_score//." (".$ht_score
		."\nxG: ".$total_xG."\n".
		"2 penalty 3\n".
		"1 owngoal 2";//." (".$ht_xG.")";

		$r = 0;
		$image->textTitle($xg->match_info["date"],$r);
		$r = 1;
		$image->textTitle($xg->match_info["competition_country"].". ".$xg->match_info["competition_name"],$r, 13);
		$r = 2;
		$image->textTitle($xg->match_info["shortDescription"],$r,18);
		$r = 4;
		$image->textTitle($xg->match_info["scores"]["total"]["home"]."    goals    ".$xg->match_info["scores"]["total"]["away"],$r,14);
		$r = 5;
		$image->textTitle(round($xg->match_info["xG"]["ft"]["home"],3)."    xG    ".round($xg->match_info["xG"]["ft"]["away"],3),$r,14);
		$r = 6;
		if($xg->match_info["scores"]["penaltyGoal"]["home"] > 0 or $xg->match_info["scores"]["penaltyGoal"]["away"] > 0)
		{
		$image->textTitle($xg->match_info["scores"]["penaltyGoal"]["home"]."    penalty    ".$xg->match_info["scores"]["penaltyGoal"]["away"],$r,14);
		$r = 7;
		}
		$r = ($r == 7) ? 7 : 6;
		if($xg->match_info["scores"]["ownGoal"]["home"] > 0 or $xg->match_info["scores"]["ownGoal"]["away"] > 0)
		$image->textTitle($xg->match_info["scores"]["ownGoal"]["home"]."    owngoal    ".$xg->match_info["scores"]["ownGoal"]["away"],$r,14);

		#$image->textTitle($title);

		header("Content-Type: image/png");
		header("Content-Disposition: inline; filename=".$image->filename);
		imagepng($image->resource,	"images/".$image->filename);
		readfile("images/".$image->filename);
		imagedestroy($image->resource);

	}
	else
	{
		echo "Это не режим получения passmap";
	}

}
elseif(isValid($_GET["mode"]) and !strcmp($_GET["mode"],"file"))
{
	if(isValid($_GET["action"]) and !strcmp($_GET["action"],"create"))
	{
		$filename = $_GET["name"];
		echo createFile($filename, @$_POST["data"]) ? "Успешная запись файла ".$filename : "Не удается записать ".$filename;
	}
	elseif(isValid($_GET["action"]) and !strcmp($_GET["action"],"read"))
	{
		echo @file_get_contents(@$_GET["name"]);
	}
	elseif(isValid($_GET["action"]) and !strcmp($_GET["action"],"exist"))
	{
		$answer = array("filename"=>$_GET["name"], "exist"=>0);
		$check = file_exists($answer["filename"]);
		if($check)			$answer["exist"] = 1;
		echo json_encode($answer);

	}
}
else
{
echo "другой режим";
}




?>
