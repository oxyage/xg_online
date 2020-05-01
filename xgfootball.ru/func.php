<?php

function pre($a){//для вывода массивов на экран
echo"<pre>";	print_r($a);	echo"</pre>";
}

function isValid($a)
{
	//проверка на валидность переменной - существует  и ( не пустая или равна 0)
	if(isset($a) and (!empty($a) or $a == 0) ) return true;
	else return false;
}


function createFile($file, $data)
{
	$OPEN = false; 
	$WRITE = false;
	
    if (!$handle = @fopen($file, 'w')) 	{         echo "Не удается открыть $file";    }	else $OPEN = true;
    if (fwrite($handle, stripslashes($data)) === FALSE) 	{        echo "Не удается произвести запись в $file";    } else $WRITE = true;
    
   # if($OPEN and $WRITE) echo "Файл $file записан";
    
    @fclose($handle);
	return true;
}



?>