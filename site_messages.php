<?
global $BODY_ID;
$BODY_ID = "messages";
include_once("bootstrap.inc.php");

if (!$_SESSION["userID"])
{
  header("Location: ".ROOT_URL."login/");
  exit();
}
if ($_GET["recipient"])
{
  $user = SQLLib::SelectRow(sprintf_esc("select * from users where sceneID = %d",$_GET["recipient"]));
  if (!$user)
  {
    header("Location: ".ROOT_URL);
    exit();
  }
}
if ($_POST)
{
  if ($_POST["message"] && $user)
  {
    $a = array();
    $a["userSender"] = $_SESSION["userID"];
    $a["userRecipient"] = $user->sceneID;
    $a["postDate"] = date("Y-m-d H:i:s");
    $a["message"] = $_POST["message"];
    $id = SQLLib::InsertRow("messages",$a);
    header("Location: ".ROOT_URL."messages/?recipient=".$user->sceneID."#c".$id);
  }
}

include_once("header.inc.php");
if ($_GET["recipient"])
{
?>
<section id="content">
  <div>
    <div id='messages'>
<?
printf("<h2>Conversation with %s</h2>",_html($user->displayName));

SQLLib::UpdateRow("messages",array("read"=>1),sprintf_esc("userSender = %d and userRecipient=%d",$user->sceneID,$_SESSION["userID"]));

$s = new SQLSelect();
$s->AddTable("messages");
$s->AddWhere(sprintf_esc("((userSender = %d and userRecipient = %d) or (userSender = %d and userRecipient = %d))",
  $_SESSION["userID"],$user->sceneID,$user->sceneID,$_SESSION["userID"]));
$s->AddJoin("left","users","messages.userSender = users.sceneID");
$s->AddOrder("postDate");
$messages = SQLLib::SelectRows( $s->GetQuery() );
echo "<ul id='conversation'>\n";
foreach($messages as $message)
{
  printf("<li class='%s' id='c%d'>",$message->userSender == $_SESSION["userID"]?"ours":"theirs",$message->id);
  printf("<span class='author'>%s - <time>%s</time></span>",_html($message->displayName),$message->postDate);
  echo parse_post($message->message);
  //printf("",$message->postDate);
  echo "</li>";
}
echo "</ul>\n";
?>    
    </div>
    
    <div id='sendmessage'>
      <h2>Send a message to <?=_html($user->displayName)?>:</h2>
      <form method='post'>
        <textarea name="message" required="yes"></textarea>
        <input type='submit' value='Send message!'/>
      </form>
    </div>
    
  </div>
</section>
<?
}
else
{
?>
<section id="content">
  <div>
    <div id='messagelist'>
<?
printf("<h2>Your conversations</h2>");

$s = new SQLSelect();
$s->AddTable(sprintf_esc("(select *, IF(userSender = %d,userRecipient,userSender) as otherUser from messages where userSender = %d or userRecipient = %d order by postDate desc) as myMsg",$_SESSION["userID"],$_SESSION["userID"],$_SESSION["userID"]));
$s->AddJoin("left","users","myMsg.otherUser = users.sceneID");
$s->AddGroup("myMsg.otherUser");
$s->AddOrder("postDate desc");
$threads = SQLLib::SelectRows( $s->GetQuery() );
echo "<ul id='conversationList'>\n";
foreach($threads as $thread)
{
  printf("<li class='%s'>",($thread->userSender == $thread->otherUser && !$thread->read)?"unread":"read");
  printf("<a href='%smessages/?recipient=%d'>",ROOT_URL,$thread->otherUser);
  printf("<h3>%s</h3>",_html($thread->displayName));
  echo shortify($thread->message,200);
  printf("<time>%s</time>",$thread->postDate);
  printf("</a>");
  printf("</li>\n");
}
echo "</ul>\n";
?>    
    </div>
  </div>
</section>
<?
}
include_once("footer.inc.php");
?>