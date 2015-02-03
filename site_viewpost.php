<?
global $BODY_ID;
$BODY_ID = "viewpost";
include_once("bootstrap.inc.php");

$post = SQLLib::SelectRow(sprintf_esc("SELECT * FROM posts LEFT JOIN users ON users.sceneID = posts.userID WHERE id = %d",$_GET["id"]));
if (!$post)
{
  header("Location: ".ROOT_URL."show-posts/");
  exit();
}
$delivered = false;
if ($_POST && $_SESSION["userID"])
{
  if ($_POST["message"])
  {
    $a = array();
    $a["userSender"] = $_SESSION["userID"];
    $a["userRecipient"] = $post->userID;
    $a["postDate"] = date("Y-m-d H:i:s");
    $a["message"] = $_POST["message"];
    SQLLib::InsertRow("messages",$a);
    $delivered = true;
  }
}

$TITLE = shortify($post->title);
include_once("header.inc.php");
?>
<section id="content">
  <div>
    <article id='singlepost'>
      <div class='itemHeader area_<?=$post->area?> intent_<?=$post->intent?>'>
<?
if ($post->userID == $_SESSION["userID"] || ($currentUser && $currentUser->isAdmin))
{
  printf("<a href='".ROOT_URL."edit-post/?id=%d' class='editlink'>Edit</a>",$post->id);
}
?>
        <h3><?=_html($post->title)?></h3>
        <span class="author">Posted by <?=_html($post->displayName)?> <?=sprintf("<time datetime='%s'>%s</time>",$post->postDate,dateDiffReadable(time(),$post->postDate))?></span>
      </div>
      <div class='body'>
        <?
        if ($post->expiry)
        {
          if ($post->expiry < date("Y-m-d"))
          {
            printf("<div class='expiry done'>This post has expired on %s.</div>",$post->expiry);
          }
          else
          {
            printf("<div class='expiry soon'>This %s will expire on %s.</div>",$post->intent=="demand"?"request":"offer",$post->expiry);
          }
        }
        echo parse_post($post->contents);
        ?>
      </div>
    </article>
<?
if ($post->closureReason)
{
  $reason = "";
  switch($post->closureReason)
  {
    case "success": $reason = "This post was closed after being successful!"; break;
    default: $reason = "This post was closed"; break;
  }
?>
    <div class="box">
      <h2><?=$reason?></h2>
      <div class='body'>
      <?=parse_post($post->closureDescription)?>
      </div>
    </div>
<?  
}
else
{
  if ($_SESSION["userID"])
  {
    if ($post->userID != $_SESSION["userID"])
    {
?>
    <div id='sendmessage' class="box">
      <h2>Interested? Get in touch with <?=_html($post->displayName)?>!</h2>
      <form method='post' action='<?=ROOT_URL?>messages/?recipient=<?=$post->userID?>'>
        <textarea name="message" required="yes"></textarea>
        <input type='hidden' name='relatedPost' value='<?=$post->id?>'/>
        <input type='submit' value='Send message!'/>
      </form>
    </div>
<?
    }
  }
  else
  {
?>
    <div id='loginbox' class='box'>
      <h2>Interested? Log in to get in touch with <?=_html($post->displayName)?>!</h2>
      <a href="<?=ROOT_URL?>login/">Log in via SceneID!</a>
    </div>
<?
  }
}
?>
  </div>
</section>
<?
include_once("footer.inc.php");
?>