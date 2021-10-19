<?php
header('Content-Type: application/json;charset=utf-8');
$action = $_GET['action'];
$day =    $_GET['day'];
$debug =  $_GET['debug'];
if(!isset($action)) $action = '';
if(!isset($day)) $day = 0;
if(!isset($debug)) $debug = 0;

$file = 'data/o3.data';
$lines = (file($file));
$counter = count($lines);
$start = $counter - 300;
$time_zone = -3*3600;
$time_init = 0;
$avg_counter = 0;
$avg_o3 = 0;
$avg_h = 0;
$avg_t = 0;
$avg_array_o3 = array();
$avg_array_h = array();
$avg_array_t = array();
$avg_array_time = array();
$date_array = array();
$epoch_array = array();
$date_init = 0;

for($i=0; $i<$counter; $i++) {
   $line = $lines[$i];
   $parts = explode(";", $line);
   $parts[0] = $parts[0] + $time_zone;
   if($date_init == 0){
        $date_init = getMidnight($parts[0], $time_zone);
        array_push($date_array, $parts[0]);
        //if($debug == 1) echo($str_date_init."\n");
   }else if($parts[0] > $date_init){
        $date_init = getMidnight($parts[0], $time_zone);
        array_push($date_array, $parts[0]);
        if($debug == 1) echo(gmdate("Y-m-d", $date_init)."\n");
   }
   array_push($epoch_array, $parts[0]);
   $last = $parts[0];
}


if($action == 'getDays'){
    echo("[");
    for($i=0; $i<count($date_array); $i++) {
        $comma = ",";
        if($i+1 == count($date_array)) $comma = "";
        echo("\"".gmdate("Y-m-d", $date_array[$i])."\"".$comma);
    }
    echo("]");

}else if($action == 'getDay'){
    if($day == -1) $day = count($date_array)-1;
    $start = array_search($date_array[$day], $epoch_array);
    $counter = (array_search($date_array[$day+1], $epoch_array))-1;
    if($counter == -1) $counter = count($epoch_array)-1;
    
    prepareJson($debug, $lines, $start, $counter, $time_zone, $time_init, $avg_counter, $avg_o3, $avg_h, $avg_t,$avg_array_o3, $avg_array_h, $avg_array_t, $avg_array_time, $date_array);

}else{

    prepareJson($debug, $lines, $start, $counter, $time_zone, $time_init, $avg_counter, $avg_o3, $avg_h, $avg_t,$avg_array_o3, $avg_array_h, $avg_array_t, $avg_array_time, $date_array);
} 

function prepareJson($debug, $lines, $start, $counter, $time_zone, $time_init, $avg_counter, $avg_o3, $avg_h, $avg_t,$avg_array_o3, $avg_array_h, $avg_array_t, $avg_array_time, $date_array){
    for($i=$start; $i<$counter; $i++) {
     
        $line = $lines[$i];
        $parts = explode(";", $line);
        $parts[0] = $parts[0] + $time_zone;
        $comma = ",";
        if($i+1 == $counter) $comma = "";
        if($parts[1] < 500){
            if($avg_counter ==0){
                $time_init = $parts[0];
                //echo("time init set: ".gmdate("Y-m-d H:i:s", $time_init)."\n");
            }
            if($parts[0] <= $time_init + 3600){
                $avg_o3 = $avg_o3 + $parts[1];
                $avg_h = $avg_h + $parts[3];
                $avg_t = $avg_t + $parts[2];
                $avg_counter++;
                if($debug == 1) echo("avg_counter: ".$avg_counter." | ".$parts[3]." | avg_h: ".$avg_h."\n");
            }else{
                if($avg_counter >0){
                    $avg_o3 = $avg_o3 / $avg_counter;
                    $avg_h = $avg_h / $avg_counter;
                    $avg_t = $avg_t / $avg_counter;
                    if($debug == 1) echo("avg_counter: ".$avg_counter." | avg_h: ".$avg_h."\n");
                }else{
                    $avg_o3 = $parts[1];
                    $avg_h = $parts[3];
                    $avg_t = $parts[2];
                }
                $avg_counter = 0;
                array_push($avg_array_o3, $avg_o3);
                array_push($avg_array_h, $avg_h);
                array_push($avg_array_t, $avg_t);
                array_push($avg_array_time, gmdate("Y-m-d H:i:s", $time_init));
                $avg_o3 = 0;
                $avg_h = 0;
                $avg_t = 0;
            }
        }
    }


    $jsonResponse = "{\"data\": [\n";


    for($i=$start; $i<$counter; $i++) {
        $line = $lines[$i];
        $parts = explode(";", $line);
        $parts[0] = $parts[0] + $time_zone;
        $comma = ",";
        if($i+1 == $counter) $comma = "";
        if($parts[1] < 500){
            $jsonResponse = $jsonResponse . $parts[1].$comma."\n";
        }
    }

    $jsonResponse = $jsonResponse .$comma. "\n], \"labels\": [";

    for($i=$start; $i<$counter; $i++) {
        $line = $lines[$i];
        $parts = explode(";", $line);
        $parts[0] = $parts[0] + $time_zone;
        $comma = ",";
        if($i+1 == $counter) $comma = "";
        if($parts[1] < 500){
            $jsonResponse = $jsonResponse ."\"".gmdate("Y-m-d H:i:s", $parts[0])."\"".$comma."\n";
        }
    }

    $jsonResponse = $jsonResponse .$comma. "\n], \"temp\": [";

    for($i=$start; $i<$counter; $i++) {
        $line = $lines[$i];
        $parts = explode(";", $line);
        $parts[0] = $parts[0] + $time_zone;
        $comma = ",";
        if($i+1 == $counter) $comma = "";
        if($parts[1] < 500){
            $jsonResponse = $jsonResponse .$parts[2].$comma."\n";
        }
    }

    $jsonResponse = $jsonResponse .$comma. "\n], \"hum\": [";

    for($i=$start; $i<$counter; $i++) {
        $line = $lines[$i];
        $parts = explode(";", $line);
        $parts[0] = $parts[0] + $time_zone;
        $comma = ",";
        if($i+1 == $counter) $comma = "";
        if($parts[1] < 500){
            $jsonResponse = $jsonResponse .$parts[3].$comma."\n";
        }
    }

    $jsonResponse = $jsonResponse . $comma ."\n], \"avg_labels\": [ ";

    $counter = count($avg_array_time);
    for($i=0; $i<$counter; $i++) {
        $comma = ",";
        if($i+1 == $counter) $comma = "";
        $jsonResponse = $jsonResponse . "\"" . $avg_array_time[$i] . "\"".$comma."\n";
    }

    $jsonResponse = $jsonResponse . $comma ."\n], \"avg_o3\": [ ";

    $counter = count($avg_array_time);
    for($i=0; $i<$counter; $i++) {
        $comma = ",";
        if($i+1 == $counter) $comma = "";
        $jsonResponse = $jsonResponse . "\"" . $avg_array_o3[$i] . "\"".$comma."\n";
    }

    $jsonResponse = $jsonResponse . $comma ."\n], \"avg_t\": [ ";

    $counter = count($avg_array_time);
    for($i=0; $i<$counter; $i++) {
        $comma = ",";
        if($i+1 == $counter) $comma = "";
        $jsonResponse = $jsonResponse . "\"" . $avg_array_t[$i] . "\"".$comma."\n";
    }

    $jsonResponse = $jsonResponse . $comma ."\n], \"avg_h\": [ ";

    $counter = count($avg_array_time);
    for($i=0; $i<$counter; $i++) {
        $comma = ",";
        if($i+1 == $counter) $comma = "";
        $jsonResponse = $jsonResponse . "\"" . $avg_array_h[$i] . "\"".$comma."\n";
    }

    $jsonResponse = $jsonResponse . $comma ."\n]} ";

    echo($jsonResponse);
}

function getMidnight($source, $time_zone){
    $str_date_init = gmdate("Y-m-d", $source);
    $date_init = strtotime($str_date_init . " 24:00");
    $date_init = $date_init + $time_zone;
    return $date_init;
}

?>
