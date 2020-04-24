<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conf= json_decode(file_get_contents("config.json"),True);

?>

<!DOCTYPE html>
<html>
<head>
<title>GlobalPinger</title>

<script src="https://cdn.jsdelivr.net/npm/moment@2/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2/dist/Chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@0.5.7/chartjs-plugin-annotation.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/papaparse@5/papaparse.min.js"></script>

<style>
.region{
font-weight: bold;
font-size:x-large;
}
.hostname{
font-family:monospace;
}
.instutution{}
.code{
font-family:monospace;
background:#DDD;
}

#graphs{
    display:flex;
    flex-direction: row;
    flex-wrap: wrap;
}
#graphs > div{
    border: 1px solid #000;
    padding:8px;
}
</style>
</head>

<body>

<h1>Global Internet Congestion Monitor</h1>

<p>
This server pings some servers around the globe to test how reliable the international internet connection is.<br>
The servers were chosen manually from <a href="https://launchpad.net/ubuntu/+archivemirrors">Ubuntu's Archive mirrors.</a>
</p>
<p>
<?php echo $conf["PageHeaderMessage"]; ?>
</p>

<div id="graphs">

<?php
foreach($conf["TargetList"] as $i => $val){
    echo '<div class="graphdiv">';
    echo '<span class="region">'.$val["Region"].'</span> <br>';
    echo '<span class="institution">'.$val["Name"].'</span> <br>';
    echo '<span class="hostname">'.$val["Hostname"].'</span>';
    echo '<canvas class="chartcanvas" id="cc_'.$val["Filename"].'" width="600" height="300"></canvas>';
    echo '</div>';
}
?>




</div>

<script>

function setupChart(chart_id, csv_path){
    var ctx=document.getElementById(chart_id).getContext("2d");

    var darray_rtt=[];
    var darray_lrate=[];

    var now_d=new Date();

    var tomm_d=new Date(new Date().setHours(0,0,0,0)+24*60*60*1000);
    var tomm_s=(tomm_d.getMonth()+1)+"/"+tomm_d.getDate();

    var today_d=new Date(new Date().setHours(0,0,0,0));
    var today_s=(today_d.getMonth()+1)+"/"+today_d.getDate();
    var today_center_d=new Date(new Date().setHours(0,0,0,0)+24*60*60*1000*0.5);

    var yesterday_d=new Date(new Date().setHours(0,0,0,0)-24*60*60*1000);
    var yesterday_s=(yesterday_d.getMonth()+1)+"/"+yesterday_d.getDate();
    var yesterday_center_d=new Date(new Date().setHours(0,0,0,0)-24*60*60*1000*0.5);

    var tda_d=new Date(new Date().setHours(0,0,0,0)-24*60*60*1000*2);
    var tda_s=(tda_d.getMonth()+1)+"/"+tda_d.getDate();
    var tda_center_d=new Date(new Date().setHours(0,0,0,0)-24*60*60*1000*1.5);

    var chart = new Chart(ctx, {
        type: 'scatter',


        data: {
            datasets: [{
                label: 'avg RTT',
                backgroundColor: '#22F',
                borderColor: '#22F',
                data: darray_rtt,
                xAsixID:"xax_time",
                yAxisID:"yax_rtt"
            },
            {
                label: 'Loss %',
                backgroundColor: '#F22',
                borderColor: '#F22',
                data: darray_lrate,
                xAsixID:"xax_time",
                yAxisID:"yax_lrate"
            }]
        },

        options: {
            responsive:false,
            scales: {
                xAxes: [{
                    id:"xax_time",
                    type: 'time',
                    position: 'bottom',
                    bounds:"ticks",
                    ticks:{
                      min:tda_d,
                      max:tomm_d
                    },
                    time: {
                        unit:"hour",
                        stepSize:6,
                        displayFormats:{
                            hour:"HH"//"M/D HH:mm"
                        }
                    }
                }],
                yAxes: [
                    {
                        id:"yax_rtt",
                        type:"linear",
                        position:"left",
                        ticks:{
                            suggestedMin:0,
                            suggestedMax:400,
                            stepSize:100,
                            fontColor:"#22F",
                            callback: function(value, index, values) {
                                return value+"ms";
                            }
                        }
                    },
                    {
                        id:"yax_lrate",
                        type:"linear",
                        position:"right",
                        ticks:{
                            suggestedMin:0,
                            suggestedMax:40,
                            stepSize:10,
                            fontColor:"#F22",
                            callback: function(value, index, values) {
                                return value+"%";
                            }
                        }
                    }
                ]
            },
            annotation:{
                annotations:[
                    {
                        type:"line",
                        mode:"vertical",
                        scaleID:"xax_time",
                        borderColor:"#000",
                        drawTime:"beforeDatasetsDraw",
                        value:today_d
                    },{
                        type:"line",
                        mode:"vertical",
                        scaleID:"xax_time",
                        borderColor:"#000",
                        drawTime:"beforeDatasetsDraw",
                        value:yesterday_d
                    },
                    {
                        type:"line",
                        mode:"vertical",
                        scaleID:"xax_time",
                        borderColor:"#0000",
                        drawTime:"beforeDatasetsDraw",
                        value:today_center_d,
                        label:{
                            content:today_s,
                            enabled:true,
                            position:"center",
                            backgroundColor:"#0000",
                            fontColor:"#000"
                        }
                    },
                    {
                        type:"line",
                        mode:"vertical",
                        scaleID:"xax_time",
                        borderColor:"#0000",
                        drawTime:"beforeDatasetsDraw",
                        value:tda_center_d,
                        label:{
                            content:tda_s,
                            enabled:true,
                            position:"center",
                            backgroundColor:"#0000",
                            fontColor:"#000"
                        }
                    },
                    {
                        type:"line",
                        mode:"vertical",
                        scaleID:"xax_time",
                        borderColor:"#0000",
                        drawTime:"beforeDatasetsDraw",
                        value:yesterday_center_d,
                        label:{
                            content:yesterday_s,
                            enabled:true,
                            position:"center",
                            backgroundColor:"#0000",
                            fontColor:"#000"
                        }
                    },
                    {
                        type:"line",
                        mode:"vertical",
                        scaleID:"xax_time",
                        borderColor:"#2F2",
                        drawTime:"beforeDatasetsDraw",
                        value:now_d,
                        label:{
                            content:"now",
                            enabled:true,
                            position:"top"
                        }
                    }
                ]
            }
        },
    });

    function dataToChart(datapoints){
            for (var i=0;i<datapoints.length;i++){
                //console.log(datapoints[i]);
                var t=new Date(datapoints[i].timestamp*1000)
                if (t<tda_d) continue;
                darray_rtt.push({
                    'x':t,
                    'y':datapoints[i].rtt_avg
                });
                darray_lrate.push({
                    'x':t,
                    'y':datapoints[i].lossrate*100
                });
                chart.update();
            }
        }

    Papa.parse(csv_path, {
        download: true,
        header:true,
        skipEmptyLines:true,
        complete: function(results) {
                //console.log(results);

                datapoints=results.data;

                dataToChart(datapoints);
            }
    });
}

<?php
foreach($conf["TargetList"] as $i => $val){
    echo 'setupChart("cc_'.$val["Filename"].'", "adata/'.$val["Filename"].'.csv");';
}
?>



</script>
</body>

</html>
