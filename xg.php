<?php

if($_GET["test"])
{
$file = (empty($_GET["test"])) ? "temp.json" : $_GET["test"].".json";
$file = file_get_contents($file);
$xg = new xG($file);
echo "<pre>";
print_r($xg);
echo "</pre>";
}

class xG
{
public $match_info;
public $events;
public $stats;

public $modelxG = 321;

//public $temp;

function __construct($xg_json)
	{
	$this->team = $team;
	$xg_array = json_decode($xg_json,true);

	$this->match_info["id"] = $xg_array["matchInfo"]["opId"];
	$date = str_replace("Z","",$xg_array["matchInfo"]["date"]);
	$date = explode("-",$date);
	$this->match_info["date"] = $date[2]."-".$date[1]."-".$date[0];
	$this->match_info["dateZ"] = $xg_array["matchInfo"]["date"];
	$this->match_info["time"] = $xg_array["matchInfo"]["time"];
	$this->match_info["week"] = $xg_array["matchInfo"]["week"];
	$this->match_info["description"] = $xg_array["matchInfo"]["description"];
	$this->match_info["shortDescription"] = $xg_array["matchInfo"]["contestant"][0]["shortName"]." - ".$xg_array["matchInfo"]["contestant"][1]["shortName"];
	$this->match_info["competition_id"] = $xg_array["matchInfo"]["competition"]["opId"];
	$this->match_info["competition_name"] = $xg_array["matchInfo"]["competition"]["name"];
	$this->match_info["competition_code"] = $xg_array["matchInfo"]["competition"]["competitionCode"];
	$this->match_info["competition_country"] = $xg_array["matchInfo"]["competition"]["country"]["name"];

	$this->match_info["series"] = $xg_array["matchInfo"]["series"]["name"];// Group

	$this->match_info["stage"] = $xg_array["matchInfo"]["stage"]["name"]; // League

	$this->match_info["teams"][0] = array(
	"id"=>$xg_array["matchInfo"]["contestant"][0]["opId"],
	"side"=>$xg_array["matchInfo"]["contestant"][0]["position"],
	"code"=>$xg_array["matchInfo"]["contestant"][0]["code"],
	"name"=>$xg_array["matchInfo"]["contestant"][0]["name"],
	"shortName"=>$xg_array["matchInfo"]["contestant"][0]["shortName"],
	"country"=>$xg_array["matchInfo"]["contestant"][0]["country"]["name"]
	//,""=>$xg_array["matchInfo"]["contestant"][0][""]
	);
	$this->match_info["teams"][1] = array(
	"id"=>$xg_array["matchInfo"]["contestant"][1]["opId"],
	"side"=>$xg_array["matchInfo"]["contestant"][1]["position"],
	"code"=>$xg_array["matchInfo"]["contestant"][1]["code"],
	"name"=>$xg_array["matchInfo"]["contestant"][1]["name"],
	"shortName"=>$xg_array["matchInfo"]["contestant"][1]["shortName"],
	"country"=>$xg_array["matchInfo"]["contestant"][1]["country"]["name"]
	//,""=>$xg_array["matchInfo"]["contestant"][0][""]
	);
	$this->match_info["venue"] = array(
	"neutral"=>$xg_array["matchInfo"]["venue"]["neutral"],
	"longName"=>$xg_array["matchInfo"]["venue"]["longName"],
	"shortName"=>$xg_array["matchInfo"]["venue"]["shortName"],
		//,""=>$xg_array["matchInfo"]["contestant"][0][""]
	);
	$this->match_info["scores"] = $xg_array["liveData"]["matchDetails"]["scores"];
	$this->match_info["scores"]["penaltyGoal"] = array("home"=>0, "away"=>0);
	$this->match_info["scores"]["ownGoal"] = array("home"=>0, "away"=>0);
	$this->match_info["xG"] = array(
	"ht"=>array("home"=>0,"away"=>0),
	"ft"=>array("home"=>0,"away"=>0));

	$this->stats[ $xg_array["liveData"]["lineUp"][0]["opContestantId"] ] = $xg_array["liveData"]["lineUp"][0]["stat"];
	$this->stats[ $xg_array["liveData"]["lineUp"][1]["opContestantId"] ] = $xg_array["liveData"]["lineUp"][1]["stat"];

		foreach($xg_array["liveData"]["event"] as $event_num => $event)
		{
		#	$this->temp .= $event["opContestantId"]." == ".$this->team."\n\r";
				//если та команда
				#if($this->team == $event["opContestantId"])				{
			  $this->events[$event_num] = array(
				"id" => $event["id"],
				"eventId" => $event["eventId"],
				"typeId" => $event["typeId"],
				"team" => $event["opContestantId"],
				"period" => $event["periodId"],
				"min" => $event["timeMin"],
				"sec" => $event["timeSec"],
				"playerName" => $event["playerName"],
				"playerId" => $event["opPlayerId"],
				"outcome" => $event["outcome"],
				"x" => $event["x"],
				"y" => $event["y"],
				"timeStamp" => $event["timeStamp"]);

					//теперь пробегаем по квалификации события
			  $this->events[$event_num]["qualifier"] = array();
				foreach($event["qualifier"] as $qual_num => $qual)
				  {
					$this->events[$event_num]["qualifier"][$qual["qualifierId"]] = isset($qual["value"]) ? $qual["value"] : "null";
				  }

			  	if($event["typeId"] == 16)//если гол
				  {
					//координаты мяча
					$this->events[$event_num]["goal_y"] = $this->events[$event_num]["qualifier"][102];
					$this->events[$event_num]["goal_z"] = $this->events[$event_num]["qualifier"][103];
					//координаты голкипера
					$this->events[$event_num]["gk_x"] = $this->events[$event_num]["qualifier"][230];
					$this->events[$event_num]["gk_y"] = $this->events[$event_num]["qualifier"][231];

					//если пенальтии
					if(isset($this->events[$event_num]["qualifier"][9]))
						{
						$this->events[$event_num]["penalty"] = 1;

							if($this->events[$event_num]["team"] == $this->match_info["teams"][0]["id"])//то home
							{
							$this->match_info["scores"]["penaltyGoal"]["home"] += 1;
							}
							elseif($this->events[$event_num]["team"] == $this->match_info["teams"][1]["id"])
							{
							$this->match_info["scores"]["penaltyGoal"]["away"] += 1;
							}
						}
				  }

					//если автогол
					if(isset( $this->events[$event_num]["qualifier"][28]))
						{
						$this->events[$event_num]["owngoal"] = 1;

							if($this->events[$event_num]["team"] == $this->match_info["teams"][0]["id"])//то home
							{
							$this->match_info["scores"]["ownGoal"]["home"] += 1;
							}
							elseif($this->events[$event_num]["team"] == $this->match_info["teams"][1]["id"])
							{
							$this->match_info["scores"]["ownGoal"]["away"] += 1;
							}
						}


				if(isset( $this->events[$event_num]["qualifier"][321]))
					{
					$this->events[$event_num]["xG"] = $this->events[$event_num]["qualifier"][321];
					}
				if(isset( $this->events[$event_num]["qualifier"][322]))
					{
					$this->events[$event_num]["xG_other"] = $this->events[$event_num]["qualifier"][322];
					}
			#}//end если та команда
		}//end foreach events
	#	$this->getHistogram();
	$this->sumxG();
	$this->makeHistogram(6);
	$this->makeTimeline();

	}//end construct

function sumxG()
	{
		$max_home = 0;	$min_home = 1;
		$max_away = 0;  $min_away = 1;
		foreach($this->events as $event_num => $event)
		{
			if($event["penalty"] == 0)
			{
				if($event["team"] == $this->match_info["teams"][0]["id"])//если хозяева
				{
					if($event["period"] == 1)
					{
						$this->match_info["xG"]["ht"]["home"] += $event["xG"];
					}
					$this->match_info["xG"]["ft"]["home"] += $event["xG"];
					$this->match_info["xG"]["total"]["home"][] = $event["xG"];
					if($event["xG"] > $max_home) $max_home = $event["xG"];
					if($event["xG"] < $min_home) $min_home = $event["xG"];
				}
				elseif($event["team"] == $this->match_info["teams"][1]["id"])//если гости
				{
					if($event["period"] == 1)
					{
						$this->match_info["xG"]["ht"]["away"] += $event["xG"];
					}
					$this->match_info["xG"]["ft"]["away"] += $event["xG"];
					$this->match_info["xG"]["total"]["away"][] = $event["xG"];
					if($event["xG"] > $max_away) $max_away = $event["xG"];
					if($event["xG"] < $min_away) $min_away = $event["xG"];
				}
			}
		}
			$this->match_info["xG"]["ft"]["max_home"] = $max_home;
			$this->match_info["xG"]["ft"]["min_home"] = $min_home;
			$this->match_info["xG"]["ft"]["delta_home"] = ($max_home - $min_home);
			$this->match_info["xG"]["ft"]["max_away"] = $max_away;
			$this->match_info["xG"]["ft"]["min_away"] = $min_away;
			$this->match_info["xG"]["ft"]["delta_away"] = ($max_away - $min_away);

	}

function makeHistogram($interval = 5)//home
	{
	$result = array("home"=>array(),"away"=>array());

	$h_home = ceil(ceil($this->match_info["xG"]["ft"]["max_home"]*100) / $interval);
	$h_away = ceil(ceil($this->match_info["xG"]["ft"]["max_away"]*100) / $interval);

	$h_max = ($h_home > $h_away) ? $h_home : $h_away;
	$xG_max = ($this->match_info["xG"]["ft"]["max_home"] > $this->match_info["xG"]["ft"]["max_away"]) ? $this->match_info["xG"]["ft"]["max_home"]  : $this->match_info["xG"]["ft"]["max_away"]  ;

//делим на интервалы
/*	$int_home = 0;
	$int_away = 0;
	while($int_home <= $xG_max*100)//$this->match_info["xG"]["ft"]["max_home"]*100)
	{
		$int_home += $h_max;
		$result["home"][$int_home] = array();
	}
	while($int_away <= $xG_max*100)// $this->match_info["xG"]["ft"]["max_away"]*100)
	{
		$int_away += $h_max;
		$result["away"][$int_away] = array();
	}*/
	$int = 0;
	while($int <= $xG_max*100)// $this->match_info["xG"]["ft"]["max_away"]*100)
	{
		$int += $h_max;
		$result["home"][$int] = array();
		$result["away"][$int] = array();
	}
//оставить эту здесь легче реализовывается проверка на команду хозяев и гостей
//или потом переделать
		foreach($this->events as $event_num => $event)
		{
			if(isset($event["qualifier"][$this->modelxG]))//если находим xG
			{
				$current_xg = $event["qualifier"][$this->modelxG]*100;
				//для хозяев
				foreach($result["home"] as $int => $array)
				{
					if($current_xg >= ($int - $h_max) and $current_xg < $int)
					{
						if($event["team"] == $this->match_info["teams"][0]["id"])
						{
							$result["home"][$int][] = $current_xg;
						}

					}
					else {
					#	$this->match_info["xG"]["hist"]["else"][] = $event;
					}
				}
				//для гостей
				foreach($result["away"] as $int => $array)
				{
					if($int > $current_xg  and  $current_xg >= ($int - $h_max) )
					{
						if($event["team"] == $this->match_info["teams"][1]["id"])
						{
							$result["away"][$int][] = $current_xg;
						}
						else {
						#	$this->match_info["xG"]["hist"]["else"][] = $event;
						}
					}
				}

			}
		}
		$this->match_info["xG"]["hist"]["intervals"] = $result;

		foreach($result["home"] as $int => $array)
		{
			$result["home"][$int] = sizeof($result["home"][$int]);
		}
		foreach($result["away"] as $int => $array)
		{
			$result["away"][$int] = sizeof($result["away"][$int]);
		}

		$this->match_info["xG"]["histrogram"] = $result;
		return $result;
	}

function makeTimeline()
{
	$this->match_info["xG"]["timeline"] = array("home"=>array(),"away"=>array());
	$max_xG = ($this->match_info["xG"]["ft"]["home"] > $this->match_info["xG"]["ft"]["away"]) ? $this->match_info["xG"]["ft"]["home"] : $this->match_info["xG"]["ft"]["away"];

	$max_time = 0;
	foreach($this->events as $event_num => $event)
	{
		if(isset( $event["qualifier"][$this->modelxG]))
		{
			$current_xg = isset($event["penalty"]) ? 0 : $event["qualifier"][$this->modelxG];
			$sec = $event["min"]*60 + $event["sec"];

			if($sec > $max_time) $max_time = $sec;

			if($event["team"] == $this->match_info["teams"][0]["id"]) $side = "home";
			else if($event["team"] == $this->match_info["teams"][1]["id"]) $side = "away";

			$this->match_info["xG"]["timeline"][$side][$sec] = $current_xg;

		}
/*		if()//isset( $event["owngoal"]) or
		{
			$current_xg = 0;//$event["qualifier"][$this->modelxG];
			$sec = $event["min"]*60 + $event["sec"];

			if($sec > $max_time) $max_time = $sec;

			if($event["team"] == $this->match_info["teams"][0]["id"]) $side = "home";
			else if($event["team"] == $this->match_info["teams"][1]["id"]) $side = "away";

			$this->match_info["xG"]["timeline"][$side][$sec] = $current_xg;
		}*/
		if(isset($event["owngoal"]))//isset( $event["owngoal"]) or
		{
			$current_xg = 0;
			$sec = $event["min"]*60 + $event["sec"];

			if($sec > $max_time) $max_time = $sec;

			if($event["team"] == $this->match_info["teams"][0]["id"]) $side = "away";
			else if($event["team"] == $this->match_info["teams"][1]["id"]) $side = "home";

			$this->match_info["xG"]["timeline"][$side][$sec] = $current_xg;
		}

		if($event["typeId"] == 16)
		{
				$plus = 1;
				if(isset($event["penalty"]))						$plus = 0;
				if(isset( $event["owngoal"]))
				{
						$side = !strcmp($side,"home") ? "home" : "away";
				  	$plus = -1;
				}
				$this->match_info["xG"]["timeline"]["goals"][$side][$sec] = $plus; // -1 - значит автогол 0 - пенальти  1 обычный гол

		}

	}
	$this->match_info["xG"]["timeline"]["last_sec"] = $max_time;
	$this->match_info["xG"]["timeline"]["max_xG"]["home"] = $this->match_info["xG"]["ft"]["home"];
	$this->match_info["xG"]["timeline"]["max_xG"]["away"] = $this->match_info["xG"]["ft"]["away"];

	$this->match_info["xG"]["ft"]["last_sec"] = $max_time;

	if(!$this->match_info["xG"]["timeline"]["home"][$max_time]) $this->match_info["xG"]["timeline"]["home"][$max_time] = 0;
	if(!$this->match_info["xG"]["timeline"]["away"][$max_time]) $this->match_info["xG"]["timeline"]["away"][$max_time] = 0;

	$this->match_info["xG"]["ft"]["last_time"] = ($max_time - $max_time%60)/60 .":".$max_time%60;



#	$this->match_info["xG"]["timeline"] = $result;
	return $result;

}





}//end Class xG





 ?>
