<?
global $BODY_ID;
$BODY_ID = "admin";
include_once("bootstrap.inc.php");

if (!$currentUser || !$currentUser->isAdmin)
{
  header("Location: ".ROOT_URL);
  exit();
}

include_once("header.inc.php");
?>

<section id="content">
  <div>
    <article id='admin' class='box'>

      <h2>Admin stuff</h2>

      <div class='body'>
<?
echo "<h3>Message stats</h3>";
$cntMessages = SQLLib::SelectRow("select count(*) as c from messages")->c;
$cntUnique = SQLLib::SelectRow("SELECT count(*) as c from (select * from messages group by concat(if(userSender<userRecipient,userSender,userRecipient),':',if(userSender>userRecipient,userSender,userRecipient)) ) as m")->c;
printf("<p><b>%d</b> messages so far, <b>%d</b> conversations</p>",$cntMessages,$cntUnique);

?>
  <div id="downloadChart"></div>
  <script type="text/javascript" src="//www.google.com/jsapi"></script>
  <script type="text/javascript">
    function drawChart() 
    {
      // Create and populate the data table.
      var data = new google.visualization.DataTable();
      data.addColumn('date', 'Date');
      data.addColumn('number', 'Message count');
<?
$days = 90;
$_msgCount = SQLLib::SelectRows("SELECT DATE_FORMAT(postDate,'%Y-%m-%d') as d, count(*) as c from messages where DATEDIFF(now(),postDate) < ".$days." group by d order by d");
$msgCount = array();
for ($x=0,$t=time(); $x<$days; $x++,$t-=60*60*24) $msgCount[date("Y-m-d",$t)] = 0;
foreach($_msgCount as $m) $msgCount[$m->d] = $m->c;
foreach($msgCount as $d=>$c)
{
?>      data.addRow([new Date("<?=$d?>"), <?=$c?>]);
<?
}
?>          
      var isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      
      // Create and draw the visualization.
      var parent = document.getElementById('downloadChart');
      var options = {
        width: parent.width,
        height: 125,
        curveType: "function",
        backgroundColor: "transparent",
        vAxis: { textPosition: 'in', minValue: 0, viewWindow: { min: 0 }, format: 'short' },
        hAxis: { textPosition: 'in', viewWindowMode: 'maximized' },
        legend: { position: 'none' },
        chartArea: { top: 40, left: 0, width:"100%", height:"100%" },
        title: 'Messages in the last <?=$days?> days',
        series: { 0: { color:'#000000' } }
      };
      if (isDarkMode)
      {
        options.titleTextStyle = { color: '#ccc' };
        options.vAxis.textStyle = {color: '#ccc'};
        options.vAxis.gridlines = {color: '#333'}
        options.vAxis.minorGridlines = {color: '#333'};
        options.hAxis.textStyle = {color: '#ccc'};
        options.hAxis.gridlines = {color: '#333'};
        options.hAxis.minorGridlines = {color: '#333'};
        options.series[0].color = '#eeeeee';
      }
      new google.visualization.LineChart(parent).draw(data, options);
    }
    google.charts.load('current', {packages: ['corechart']});
    google.charts.setOnLoadCallback(drawChart);
  </script>  
<?

echo "<h3>Recent messages through a post</h3>";
echo "<ul>";
$s = new SQLSelect();
$s->AddField("posts.*");
$s->AddField("messages.postDate as messageDate");
$s->AddField("sender.displayName as senderName");
$s->AddField("recipient.displayName as recipientName");
$s->AddTable("messages");
$s->AddWhere("relatedPost is not null");
$s->AddJoin("left","posts","posts.id = messages.relatedPost");
$s->AddJoin("left","users as sender","sender.sceneID = messages.userSender");
$s->AddJoin("left","users as recipient","recipient.sceneID = messages.userRecipient");
$s->AddOrder("messages.postDate desc");
$s->SetLimit( 10 );

$data = SQLLib::SelectRows( $s->GetQuery() );
foreach($data as $c)
  printf("<li>%s - <a href='%s'>%s</a></li>",$c->messageDate,ROOT_URL."post/".$c->id."/".hashify($c->title),_html($c->title));
echo "</ul>";

echo "<h3>Closed posts</h3>";
echo "<ul>";
$closure = SQLLib::SelectRows("SELECT closureReason, count(*) as c from posts group by closureReason");
foreach($closure as $c)
  if ($c->closureReason)
    printf("<li><a href='".ROOT_URL."admin/list/?reason=%s'>%s</a>: <b>%d</b></li>",$c->closureReason,$c->closureReason,$c->c);
echo "</ul>";

?>
      </div>
    </article>
  </div>
</section>

<?
include_once("footer.inc.php");
?>
