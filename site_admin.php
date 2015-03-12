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
    printf("<li>%s: <b>%d</b></li>",$c->closureReason,$c->c);
echo "</ul>";

?>
      </div>
    </article>
  </div>
</section>

<?
include_once("footer.inc.php");
?>
