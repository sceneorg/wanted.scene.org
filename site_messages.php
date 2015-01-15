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
    if(isset($_POST["relatedPost"]))
      $a["relatedPost"] = $_POST["relatedPost"];
    $id = SQLLib::InsertRow("messages",$a);

    if ($user->wantsMail)
    {
      $msg = "";
      $msg .= "Hi!\n";
      $msg .= "\n";
      $msg .= $currentUser->displayName." has responded to your message on Wanted!\n";
      $msg .= "\n";
      $msg .= "You can read their message and respond here:\n";
      $msg .= ROOT_URL."message/?recipient=".$currentUser->sceneID."#c".$id."\n";
      $msg .= "\n";
      $msg .= "Hugs,\n";
      $msg .= "the Wanted! mailing robot\n";
      myMail( $user->email, "You have received a message from ".$currentUser->displayName."!", $msg, "From: wanted@scene.org" );
    }
    header("Location: ".ROOT_URL."messages/?recipient=".$user->sceneID."#c".$id);
    exit();
  }
}

include_once("header.inc.php");
if ($_GET["recipient"])
{
?>
<section id="content">
  <div>
    <div id='messages' class='box'>
<?
printf("<h2>Conversation with %s</h2>",_html($user->displayName));

SQLLib::UpdateRow("messages",array("read"=>1),sprintf_esc("userSender = %d and userRecipient=%d",$user->sceneID,$_SESSION["userID"]));

$s = new SQLSelect();
$s->AddTable("messages");
$s->AddField("messages.*");
$s->AddField("users.*");
$s->AddField("posts.title");
$s->AddWhere(sprintf_esc("((userSender = %d and userRecipient = %d) or (userSender = %d and userRecipient = %d))",
  $_SESSION["userID"],$user->sceneID,$user->sceneID,$_SESSION["userID"]));
$s->AddJoin("left","users","messages.userSender = users.sceneID");
$s->AddJoin("left","posts","messages.relatedPost = posts.id");
$s->AddOrder("postDate");
$messages = SQLLib::SelectRows( $s->GetQuery() );
echo "<ul id='conversation'>\n";
foreach($messages as $message)
{
  printf("<li class='%s' id='c%d'>",$message->userSender == $_SESSION["userID"]?"ours":"theirs",$message->id);
  printf("<span class='author'>%s - <time>%s</time>",_html($message->displayName),$message->postDate);
  if ($message->relatedPost)
    echo " - referring to the post <a href='".ROOT_URL."post/".$message->relatedPost."/".hashify($message->title)."'>"._html($message->title)."</a>";
  echo "</span>";
  echo parse_post($message->message);
  //printf("",$message->postDate);
  echo "</li>";
}
echo "</ul>\n";
?>    
    </div>
    
    <div id='sendmessage' class='box'>
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
    <div id='messagelist' class='box'>
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
  printf("<time>%s</time>",$thread->postDate);
  echo shortify($thread->message,200);
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