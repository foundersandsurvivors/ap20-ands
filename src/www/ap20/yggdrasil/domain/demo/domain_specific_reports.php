<!-- ===================================== domain/demo/domain_specific_reports.php start -->
<h2>Custom/special reports for "demo" domain</h2>

<h3>Example -- generate a chart with QGoogleVisualizationAPI v.0.2 by Tom Schaefer</h3>
<p>Copy of example at http://www.query4u.de/vizapi/. More examples at: https://developers.google.com/chart/interactive/docs/gallery/</p>

<div class="normal" id="barchart_e12e"></div>
<script type="text/javascript" src="http://www.google.com/jsapi"></script><script type="text/javascript">
google.load("visualization", "1", {packages:["barchart"]});
google.setOnLoadCallback(drawBarchart);

function drawBarchart() {
var data = new google.visualization.DataTable();
data.addColumn('string','Year');
data.addColumn('number','Males');
data.addColumn('number','Females');

data.addRows(4);
data.setValue(0,0,'2004');
data.setValue(0,1,1000);
data.setValue(0,2,400);
data.setValue(1,0,'2005');
data.setValue(1,1,1170);
data.setValue(1,2,460);
data.setValue(2,0,'2006');
data.setValue(2,1,260);
data.setValue(2,2,720);
data.setValue(3,0,'2007');
data.setValue(3,1,1030);
data.setValue(3,2,540);

var chart = new google.visualization.BarChart(document.getElementById('barchart_e12e'));
chart.draw(data, {backgroundColor:{stroke:'black', fill:'#eee', strokeSize: 1}, isStacked:true, title:'Live Population (NOT REAL DATA)', legend:'bottom', height:240, width:400});
}

</script>

<!-- ===================================== domain/demo/domain_specific_reports.php end -->
