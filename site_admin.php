<?php
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
<?php
echo "<h3>Message stats</h3>";
$cntMessages = SQLLib::SelectRow("select count(*) as c from messages")->c;
$cntUnique = SQLLib::SelectRow("SELECT count(*) as c from (select * from messages group by concat(if(userSender<userRecipient,userSender,userRecipient),':',if(userSender>userRecipient,userSender,userRecipient)) ) as m")->c;
printf("<p><b>%d</b> messages so far, <b>%d</b> conversations</p>",$cntMessages,$cntUnique);

?>
  <div style="width:100%;height:125px;"><canvas id="downloadChart" style="width:100%;height:125px;"></canvas></div>
  <script type="text/javascript" src="<?=ROOT_URL?>chart.js"></script>
  <script type="text/javascript">
    var chart = null;
    var data = null;
    function drawChart() 
    {
      // Create and populate the data table.
      data = [
<?php
$days = 90;
$_msgCount = SQLLib::SelectRows("SELECT DATE_FORMAT(postDate,'%Y-%m-%d') as d, count(*) as c from messages where DATEDIFF(now(),postDate) < ".$days." group by d order by d");
$msgCount = array();
for ($x=0,$t=time(); $x<$days; $x++,$t-=60*60*24) $msgCount[date("Y-m-d",$t)] = 0;
foreach($_msgCount as $m) $msgCount[$m->d] = $m->c;
foreach($msgCount as $d=>$c)
{
?>        { x: '<?=$d?>', y: <?=$c?> },
<?php
}
?>
      ];
      var options = {
        type: 'bar',
        data: {
          datasets: [{
            label: 'Messages',
            data: data,
            borderColor: '#000000',
            backgroundColor: '#000000',
            borderWidth: 2,
            pointRadius: 5,
            pointBackgroundColor: 'transparent',
            pointBorderColor: 'transparent',
            cubicInterpolationMode: 'monotone',
          }]
        },
        options: {
          plugins: {
            title: {
              display: true,
              text: 'Messages in the last <?=$days?> days',
              align: 'start',
              font: { size: '9px' },
              color: 'black',
            },
            legend: {
              display: false,
            }
          },
          scales: {
            x: {
              grid: { tickLength: 0 },
              ticks: {
                includeBounds: true,
                maxTicksLimit: 11,
                font: { size: '9px' },
                callback: function(value, index, ticks) { return value ? data[index].x : ""; }
              }
            },
            y: {
              beginAtZero: true,
              suggestedMax: 10,
              font: { size: '9px' },
              grid: { tickLength: 0 },
              ticks: {
                count: 6,
                precision: 0,
                mirror: true,
                stepSize: 1,
                labelOffset: 10,
                font: { size: '9px' },
                callback: function(value, index, ticks) { return value ? value : ""; }
              }
            }
          }
        }
      }
      
      var isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

      if (isDarkMode)
      {
        options.data.datasets[0].borderColor = '#ccc';
        options.data.datasets[0].backgroundColor = '#ccc';
      }

      chart = new Chart(document.getElementById('downloadChart'), options);
    }
    window.onload = drawChart;
  </script>
<?php

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

<?php
include_once("footer.inc.php");
?>
