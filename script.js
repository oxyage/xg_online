function ResponseAJAX(XHR){	console.log(XHR);}

function refreshInfo(image)
{
	var image = document.getElementById(image);
	Opta.post("main.php?mode=ajax&get=image",{	xg: JSON.stringify(loaded_xg.data)	})
			.done(function(response){
			//получили ответ от main.php
			response = JSON.parse(response);
			//в консоль
			console.log("Response from main.php?xg");			console.log(response);
		});

}



function getxG(game_id,competition_id,season_id)
{
var dataForxG = {
"type":"sdapi::soccerdata::matchexpectedgoals",
"params":{
	"sport":"football",
	"comp":		competition_id,
	"season":	season_id,
	"match": 	game_id,
	"trn":	{	"teams":true, "players": true},
	"sport_id":1
	},
"ttl":30
};

//создаем объект для xG
objectForxG = new Opta.m.FeedRequest(dataForxG);
//делаем запрос на xG
Opta.FeedManager.getFeed(objectForxG)
.done(function(loaded_xg)
		{
			//console.log(loaded_xg.data);//выводим полученный xG в консоль
			//отправляем его для построения изображения
			Opta.post("main.php?mode=ajax&get=xg",{	xg: JSON.stringify(loaded_xg.data)	})
			.done(function(response)
			{
				//получили ответ от main.php
				//response = JSON.parse(response);
				//ПОЛУЧИТЬ ССЫЛКУ НА ФАЙЛ!
				console.log("Response from main.php?xg");
				console.log(JSON.parse(response));
				console.log("beta.optasports.com/main/main.php?mode=ajax&get=image&file="+game_id);
				//Loaded.push(JSON.parse(response));
			});
		});
//end get xG

}


function getPassmap(info)
{
	toggle('choose_table');
	info = info.split("-");
	Opta.FeedManager.getFeed({
	  type: "opta::teamgame::f27",
	  ttl: 10,
	  params: 	{
		customer_id:  "default",
		translation_id:  1,
		lang_locale: "en_GB",
		sport: 1,
		match: info[0],
		team: info[1]
		}
	  })
	.done(function(loaded){
		//при удачном получении карты пасов от опты - отправляем её по ajax на main.php
		//получить игроков!

		//console.log("Passmap loaded");		console.log(loaded);

		//подгружаем игроков известного матча
		Opta.Trans.loadPlayers({
		"competition_id":	loaded.data.SoccerFeed["@attributes"].competition_id,
		"season_id":		loaded.data.SoccerFeed["@attributes"].season_id,
		"team_id": 			loaded.data.SoccerFeed["@attributes"].team_id,
		"sport_id":			1});

		//прочая информация о матче
		//f9_packed
		Opta.FeedManager.getFeed({
		  type: "opta::match::f9_packed",
		  ttl: 10,
		  params: 	{
			customer_id:  "default",
			translation_id:  1,
			lang_locale: "en_GB",
			sport: 1,
			match: loaded.data.SoccerFeed["@attributes"].game_id
			}
		  })
		.done(function(loaded){
		   //console.log("info f9_packed");		   console.log(loaded);
	   });


		//get xG

		var dataForxG = {
		"type":"sdapi::soccerdata::matchexpectedgoals",
		"params":{
			"sport":"football",
			"comp":		loaded.data.SoccerFeed["@attributes"].competition_id,
			"season":	loaded.data.SoccerFeed["@attributes"].season_id,
			"match": 	loaded.data.SoccerFeed["@attributes"].game_id,
			"trn":	{	"teams":true, "players": true},
			"sport_id":1
			},
		"ttl":30
		};

		//создаем объект для xG
		objectForxG = new Opta.m.FeedRequest(dataForxG);
		//делаем запрос на xG
		Opta.FeedManager.getFeed(objectForxG)
		.done(function(loaded_xg)
				{
					console.log(loaded_xg.data);//выводим полученный xG в консоль
					//отправляем его для построения изображения
					Opta.post("main.php?mode=ajax&get=xg",{	xg: JSON.stringify(loaded_xg.data)	})
					.done(function(response)
					{
						//получили ответ от main.php
						//response = JSON.parse(response);
						//ПОЛУЧИТЬ ССЫЛКУ НА ФАЙЛ!
						console.log("Response from main.php?xg");
						console.log(JSON.parse(response));
						Loaded.push(JSON.parse(response));
					});
				});
		//end get xG


		Opta.post("main.php?mode=ajax&get=passmap",{	passmap: JSON.stringify(loaded.data)	})
		.done(function(response){
		//получили ответ от main.php
		response = JSON.parse(response);
		//в консоль
		//console.log("Response from main.php?getpassmap");		console.log(response);

		//парсим json и делаем таблицу
			var passes_table = document.getElementById("passes_table");

            for(i in response.Table)
            {
				var row = passes_table.insertRow(passes_table.rows.length);
				row.align = "center";

			}



		});
	});

}

function loadCalendar(comp,season)
{

Opta.FeedManager.getFeed({
  type: "opta::comp::f1_packed",
  ttl: 10,
  params: 	{
	customer_id:  "default",
	translation_id:  1,
	lang_locale: "en_GB",
	sport: 1,
	season: season,
	comp: comp
	}
  })
.done(function(result){
	data = result.data;

	var filename = "calendar/Calendar-"+data.competition_id+"-"+data.season_id+".json";
	Opta.post("main.php?mode=file&action=create&name="+filename,{	data: JSON.stringify(data)	})
	.done(function(response){console.log(response);});
});

}

function getCalendar(comp, season)
{
	season = document.forms["get"][season].value;
	comp = document.forms["get"][comp].value;

	var t = Opta.Deferred();

  	Opta.Trans.loadTeams({
	"competition_id":	comp,
	"season_id":		season,
	"sport_id":			1})
	.done(function(){
		//console.log(result);
		t.resolve();
	});

	//когда получим команды, можно грузить календарь
	//команды получены - грузим календарь
	t.done(function(){
		Opta.FeedManager.getFeed({
		  type: "opta::comp::f1_packed",
		  ttl: 10,
		  params: 	{
			customer_id:  "default",
			translation_id:  1,
			lang_locale: "en_GB",
			sport: 1,
			season: season,
			comp: comp
			}
		  })
		.done(function(result){
			data = result.data;

			var filename = "calendar/Calendar-"+data.competition_id+"-"+data.season_id+".json";
			Opta.post("main.php?mode=file&action=create&name="+filename,{	data: JSON.stringify(data)	})
			.done(function(response){console.log(response);});


		 	var choose_table = document.getElementById("choose_table");

            for(i in data.matches)
            {
              var row = choose_table.insertRow(choose_table.rows.length);
			  row.align = "center";
              var match = row.insertCell(0);
			  match.innerHTML = data.matches[i].id;

              var date = new Date(data.matches[i].date._i);
              var datetime = row.insertCell(1);
              datetime.innerHTML =
			  Less10(date.getDate())+"-"+Less10(date.getMonth()+1)+"-"+date.getFullYear()+" "+
              Less10(date.getHours())+":"+Less10(date.getMinutes());

              var home = row.insertCell(2);
			  home.innerHTML = "<a onclick=\"getPassmap('"+data.matches[i].id+"-"+data.matches[i].team[0].id+"')\" href='javascript:void(0);'>"+
			  Opta.Trans.data.team[1][data.matches[i].team[0].id]["full"]+
			  "</a>";

              var score = row.insertCell(3);
              score.innerHTML =   data.matches[i].team[0].score+":"+data.matches[i].team[1].score;

              var away = row.insertCell(4);
			  var xG_button = row.insertCell(5);
			  xG_button.innerHTML = "<input type='button' value='Скачать xG' disabled onclick='return false;'>";
			  away.innerHTML = "<a onclick=\"getPassmap('"+data.matches[i].id+"-"+data.matches[i].team[1].id+"')\" href='javascript:void(0);'>"+Opta.Trans.data.team[1][data.matches[i].team[1].id]["full"]+"</a>";

            }
			console.log("Get calendar");			console.log(result);

		});
	});




}

function toggle(id)
{
	var element = document.getElementById(id);
	element.style.display = ("" == (element.style.display)) ? "none" : "";
}

function deleteRows(table)
{
table = document.getElementById(table);
	for((k=table.rows.length-1); k>0; k--)
	  {
	  table.deleteRow(k);
	  }
}

function Less10(num)
{
	if(num < 10) return "0"+num;
	else	return num;
}

function LoadCompetition(button)
{
	now_date = new Date();
	season = document.forms["get"]["season"].value;
	Opta.Trans.loadComps({sport_id:1, season_id:season}).done(function(){

		var loaded = Opta.Trans.data.comp[1][season];
		for(i in competitions)
		{
		var menu_competition = document.getElementById("menu_competition");
		var new_option = new Option(competitions[i]+" - "+loaded[competitions[i]]["full"],competitions[i]);
		menu_competition.appendChild(new_option);
			if(competitions[i] == 8) new_option.selected = true;

		var calendar_list = document.getElementById("calendar_list");
		calendar_list.innerHTML += "<input type='button' onclick='loadCalendar("+competitions[i]+",2018)' value='comp "+competitions[i]+"'><br>";
		}
		button.disabled = true;
	});
}
