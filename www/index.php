<?php 
$o3 = $_GET["o3"];
$t = $_GET["t"];
$h = $_GET["h"];


if(isset($o3) && isset($t) && isset($t) && $o3 > 0 && $t > 0 && $h > 0){

    $line = time().";".$o3.";".$t.";".$h."\n";

    $file = 'data/o3.data';


    if(!is_file($file)) file_put_contents($file, $line);
    else{
        $fp = fopen($file, 'a');
        fwrite($fp, $line);  
        fclose($fp);  
    } 

    echo("OK");

}else{
?>
<html>
    <head>
        <title>O3 Data</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js"></script>

    </head>
    <body onload="getData(-1, true);">        
        <div id="content"  height="648" style="padding: 10px;">
            <h2 id="title">O3 Data</h2>
            <hr>
            Available Dates: 
            <select id="dates_select"> 
            </select>
            <input type="button" name="view" value="view" id="date_btn" onclick="processDay();"/>
            <div id="graph-container">
                <canvas id="rawChart" height="40vw" width="180vw"></canvas>
                <br>
                <canvas id="avgChart" height="40vw" width="180vw"></canvas>
            </div>
        </div>
        <script>

function getDays(){
    $.getJSON("getJson.php?action=getDays", function(result){
        
        var selectOptions = '';
        for (let i = 0; i < result.length; i++) {
            selectOptions = '<option value='+i+'>'+result[i]+'</option>\n' + selectOptions;
        }
        document.getElementById('dates_select').innerHTML = selectOptions;
        
    });

}

function processDay(){
    getData(document.getElementById('dates_select').value, false);
    var sel = document.getElementById('dates_select');
    var text= sel.options[sel.selectedIndex].text;
    document.getElementById('title').innerHTML = "O3 Data for "+text;
}
    
function getData(day, updateDates){

    $('#rawChart').remove();
    $('#avgChart').remove();
    $('#graph-container').empty();
    $('#graph-container').append('<canvas id="rawChart" height="40vw" width="180vw"></canvas><br><canvas id="avgChart" height="40vw" width="180vw"></canvas>');

    if(updateDates) getDays();
    if(day == undefined) day = -1;
    $.getJSON("getJson.php?action=getDay&day="+day, function(result){
        var ctx = document.getElementById('rawChart').getContext('2d');
        var chart = new Chart(ctx, {
          type: 'line',
          data: {
             labels: result.labels,
             datasets: [{
                label: "O3",
                //backgroundColor: 'rgb(129, 198, 2228)',
                borderColor: 'orange',
                data: result.data
             },
             {
                label: "Temperature",
                //backgroundColor: 'rgb(129, 108, 2228)',
                borderColor: 'blue',
                data: result.temp
             },
             {
                label: "Humitdity",
                //backgroundColor: 'rgb(109, 198, 2228)',
                borderColor: 'green',
                data: result.hum
             }]
          },
          options: {
             responsive: 'true',
             scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }],
                xAxes: [{
                    ticks: {
                        autoSkip: false,
                        maxRotation: 70,
                        minRotation: 70
                    }
                }]
             }
          }
       });

        var ctx = document.getElementById('avgChart').getContext('2d');
        var chart = new Chart(ctx, {
          type: 'line',
          data: {
             labels: result.avg_labels,
             datasets: [{
                label: "O3",
                //backgroundColor: 'rgb(129, 198, 2228)',
                borderColor: 'orange',
                data: result.avg_o3
             },
             {
                label: "Temperature",
                //backgroundColor: 'rgb(129, 108, 2228)',
                borderColor: 'blue',
                data: result.avg_t
             },
             {
                label: "Humitdity",
                //backgroundColor: 'rgb(109, 198, 2228)',
                borderColor: 'green',
                data: result.avg_h
             }]
          },
          options: {
             responsive: 'true',
             scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }],
                xAxes: [{
                    ticks: {
                        autoSkip: false,
                        maxRotation: 70,
                        minRotation: 70
                    }
                }]
             }
          }
       });
      });
}  
        </script>
    </body> 
</html>
<?php } ?>

