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

echo "<ul>";
$data = SQLLib::SelectRows("select count(*) as c, posts.id, posts.title from messages left join posts on posts.id = messages.relatedPost where posts.title is not null group by relatedPost order by postDate desc limit 10");
foreach($data as $c)
  printf("<li><a href='%s'>%s</a>: <b>%d</b></li>",ROOT_URL."post/".$c->id."/".hashify($c->title),_html($c->title),$c->c);
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
