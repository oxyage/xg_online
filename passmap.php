<?php



class Passmap
{
	public $json;

	public $game_id;
	public $game_date;
	public $competition_id;
	public $competition_name;
	public $season_id;

	public $home_team_id;
	public $home_team_name;
	public $away_team_id;
	public $away_team_name;

	public $team_id;
	public $team_name;

	public $side;

	public $Players;

	//public $Table;

	function getSide()	{	return $this->side;	}

	function __construct($json)
	{

		$attr = "@attributes";
		$val = "@value";

		//$json = file_get_contents($json);
		//$this->json = $json;
		$json = json_decode($json,true);
		$json = $json["SoccerFeed"];

		$this->game_id = $json[$attr]["game_id"];
		$this->game_date = $json[$attr]["game_date"];
		$this->competition_id = $json[$attr]["competition_id"];
		$this->competition_name = $json[$attr]["competition_name"];
		$this->season_id = $json[$attr]["season_id"];

		$this->home_team_id = $json[$attr]["home_team_id"];
		$this->home_team_name = $json[$attr]["home_team_name"];
		$this->away_team_id = $json[$attr]["away_team_id"];
		$this->away_team_name = $json[$attr]["away_team_name"];

		$this->team_id = $json[$attr]["team_id"];
		$this->team_name = $json[$attr]["team_name"];

			if($this->team_id == $this->away_team_id)			{				$this->side = "away";			}
			elseif($this->team_id == $this->home_team_id)		{				$this->side = "home";			}
			else												{				$this->side = "none";			}

			for($i=0; $i<sizeof($json["Player"]); $i++)
			{
				$player_id = $json["Player"][$i][$attr]["player_id"];
				$this->Players[$player_id] = $json["Player"][$i][$attr];

				for($k = 0; $k < sizeof($json["Player"][$i]["Player"]); $k++)
				{
					$player_id_k = $json["Player"][$i]["Player"][$k][$attr]["player_id"];
					$this->Players[$player_id]["passes"][$player_id_k]["count"] = $json["Player"][$i]["Player"][$k][$val];
					$this->Players[$player_id]["passes"][$player_id_k]["player_id"] = $player_id_k;
					$this->Players[$player_id]["passes"][$player_id_k]["player_name"] = $json["Player"][$i]["Player"][$k][$attr]["player_name"];
				}
			}
			//$this->players_id = array_keys($this->Players);
	}

	function addTable()
	{
		$attr = "@attributes";
		$val = "@value";




	}



}


?>
