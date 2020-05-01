<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Файл индекс</title>
<script type="text/javascript">
//script подключения
var opta_settings = {subscription_id:'978d5d7e1501e56a5e0b49e800ca7d65',language:'en_GB',timezone:'Europe/Moscow'};
var competitions = [4,941,5,232,6,524,8,9,10,21,22,23,24,87,90,98,99,100,102,104,115,119,129,135,208,214,363,724]; //f27
</script>
<script type="text/javascript" src="v3.opta-widgets.js"></script>
<script type="text/javascript" src="script.js?<?=time()?>"></script>
<script>

function loadComps(season)
{
  var CompetitionsList = document.getElementById("CompetitionsList");
  CompetitionsList.innerHTML = "";
    Opta.Trans.loadComps({sport_id:1, season_id: season})
    .done(function(){
    		var data = Opta.Trans.data.comp[1][season];
        //console.log(data);
    		for(i in competitions)
    		{
				if(typeof(data[competitions[i]]) != "undefined")
				{
				 CompetitionsList.innerHTML +=
				 competitions[i]+" - <a href='javascript: void(0)' onclick='loadMatches("+competitions[i]+")'>"+
				 data[competitions[i]]["full"]
				 +"</a> <br>";
				}
	      
    		}
	  });
}

function loadMatches(competition)
{
  season = document.forms["form2"]["season"].value;

  var teams = Opta.Deferred();

  Opta.Trans.loadTeams({
      "competition_id":	competition,
      "season_id":		season,
      "sport_id":			1 })
  .done(function(){   teams.resolve()  });

  teams.done(function(){
    //когда получили команды получаем календарь
    Opta.FeedManager.getFeed({
  		  type: "opta::comp::f1_packed",
  		  ttl: 10,
  		  params: {
          			customer_id:  "default",
          			translation_id:  1,
          			lang_locale: "en_GB",
          			sport: 1,
          			season: season,
          			comp: competition
                }
		  })
		.done(function(result)
    {
			data = result.data;
    //  console.log(data);
      var filename = "calendar/Calendar-"+data.competition_id+"-"+data.season_id+".json";
      Opta.post("main.php?mode=file&action=create&name="+filename,{	data: JSON.stringify(data)	})
      .done(function(response){
        console.log(response);

        var choose_table = document.getElementById("choose_table");
        choose_table.deleteTFoot();
        var body = choose_table.createTFoot();

        for(i in data.matches)
        {
          var row = body.insertRow(body.rows.length);
          row.align = "center";

          var index = row.insertCell(0);
          index.innerHTML = i;

          var match = row.insertCell(1);
          match.innerHTML = data.matches[i].id;

          var date = new Date(data.matches[i].date._i);
          var datetime = row.insertCell(2);
          datetime.innerHTML =
    Less10(date.getDate())+"-"+Less10(date.getMonth()+1)+"-"+date.getFullYear()+" "+
          Less10(date.getHours())+":"+Less10(date.getMinutes());

          var home = row.insertCell(3);
          home.innerHTML = Opta.Trans.data.team[1][data.matches[i].team[0].id]["full"];

          var score = row.insertCell(4);
          score.innerHTML = data.matches[i].team[0].score+":"+data.matches[i].team[1].score;

          var away = row.insertCell(5);
          away.innerHTML = Opta.Trans.data.team[1][data.matches[i].team[1].id]["full"];

           var xG_button = row.insertCell(6);

           xG_button.innerHTML =
             "<input type='button' value='Скачать xG' onclick='makeImage("+
             data.matches[i].id+","+competition+","+season+")'>";

        }

      //  console.log(window.origin+"/main/calendar.php?comp="+data.competition_id);
      });
    });//done f1_packed
  });//done.teams

}

function makeImage(game_id, competition_id, season_id){
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
        console.log("xG opta length: "+JSON.stringify(loaded_xg.data).length);
  		//	console.log(loaded_xg);//выводим полученный xG в консоль
  			//отправляем его для построения изображения
  			Opta.post("main.php?mode=ajax&get=xg",{	xg: JSON.stringify(loaded_xg.data)	})
  			.done(function(response)
  			{
  				//получили ответ от main.php
  				//response = JSON.parse(response);
  				//ПОЛУЧИТЬ ССЫЛКУ НА ФАЙЛ!
  				//console.log("Response from main.php?xg");
          var xg_response = JSON.parse(response);
  				console.log(xg_response);
		xg_response.match_info.stage = xg_response.match_info.stage.replace(/\s/g, '') || "";
		xg_response.match_info.series = (xg_response.match_info.series != null) ? xg_response.match_info.series.replace(/\s/g, '') : "";
          console.log("#xG #xGplot"
          +" #"+xg_response.match_info.competition_name.replace(/\s/g, '')
          +" #"+(xg_response.match_info.stage.replace(/\s/g, '') || "")
          +" #"+(xg_response.match_info.series.replace(/\s/g, '') || "")
          +" #"+xg_response.match_info.teams[0].shortName
          +" #"+xg_response.match_info.teams[1].shortName);

  				console.log("http://beta.optasports.com/main/main.php?mode=ajax&get=image&file="+game_id);
        	Opta.get("main.php?mode=ajax&get=image&file="+game_id).done(function(image){
          //console.log(image.length);
          });
  				//Loaded.push(JSON.parse(response));
  			});
  		});
  //end get xG

}

</script>
</head>

<body>
<!--
<h3>1. Загрузить календарь (на текущий момент)</h3>
<br>

<form name="get">


  <select name="competition" id="menu_competition" size="5">  </select>
  <br><br>

  <input type="button" onClick="toggle('calendar_list')" value="Показать календарь-лист">
  <div id='calendar_list' style="display:none">Здесь выведем календарь-лист<br></div>
  <br><br>



  <input type="button" onClick="getCalendar('competition','season')" value="Загрузить">
  <input type="button" onClick="deleteRows('choose_table')" value="Очистить">
</form>

<h3>2. Выбрать матч</h3>
<input type="button" onClick="toggle('choose_table')" value="Показать\скрыть таблицу">
<table id="choose_table" width="800px" cellpadding="3" border="1" >
    <tr align="center">
      <td>Match ID</td>
      <td>Дата</td>
      <td>Хозяева</td>
      <td>Счет</td>
      <td>Гости</td>
	  <td>xG</td>
    </tr>
</table>
<input type="button" onClick="toggle('choose_table')" value="Показать\скрыть таблицу">
<h3>3. Информация о матче</h3>
<input type="button" onClick="toggle('info')" value="Показать\скрыть информацию">
<input type="button" onClick="refreshInfo('image')" value="Обновить картинку">

<div id="info">
	<table id="passes_table" width="800px" cellpadding="3" border="1" >
		<tr>
			<td>
			<img src="" id="image" width="80%">
			</td>
		</tr>
	</table>
</div>
-->
<hr>
<hr>

<form name="form2">
<input type="button" value="Загрузить лиги" onClick="loadComps(season.value)"><br>
Season: <input type="text" name="season" value="2018" size=4 placeholder="Season">
<a href="javascript: void(0);" onClick="toggle('CompetitionsList')">Показать\скрыть лиги</a>
<div id="CompetitionsList"></div>
<hr>
<table id="choose_table" width="800px" cellpadding="3" border="1" >
  <thead align="center">
    <td>#</td>
    <td>Match ID</td>
    <td>Дата</td>
    <td>Хозяева</td>
    <td>Счет</td>
    <td>Гости</td>
    <td>xG</td>
  </thead>
</table>
</form>




</body>
</html>
