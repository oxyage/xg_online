<?php
$mysql = new MySQL;
$mysql->connect();
$mysql->select_db();

class MySQL
{

  private $host = "localhost"; // Имя сервера
  private $dbuser = "046771424_local";          // Имя пользователя
  private $dbpasswd = "efdd38a60ad486aa5ac9618185185f9c";            // Пароль
  public $database = "oxyage95_football";

  public $db;

  function connect()
  {
    $connect = mysql_connect($this->host,$this->dbuser,$this->dbpasswd);
    if (!$connect)     return mysql_errno().":".mysql_error();
    else $this->db = $connect;
    return true;
  }
  function get_link()
  {
    return $this->db;
  }
  function select_db($name = null)
  {
      $name = (is_null($name)) ? $this->database : $name;
      if(!mysql_select_db($name,$this->db)) return mysql_errno().":".mysql_error();
      return true;
  }

  function insert($table, $data)
  {
    //data = array('columns'=>'data','column2'=>'data')
    $columns = "";
    $values = "";
    $count = 0;
    foreach($data as $c => $content)
    {
      if($count>0){
        $columns .= ", ";
        $values .= ", ";
      }
      $columns .= "`".$c."`";
      $values .= "`".$content."`";
      $count ++;
    }
    $query = "INSERT INTO `".$this->database."`.`".$table."` (".$columns.") VALUES (".$values.");";
    echo $query;
  }




  #ini_set('max_execution_time', 1900);
#  ini_set('memory_limit', '-1');

}


?>
