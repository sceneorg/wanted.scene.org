<?
global $BODY_ID;
$BODY_ID = "closepost";
include_once("bootstrap.inc.php");

if (!$currentUser)
{
  header("Location: ".ROOT_URL."login/?return=".rawurlencode( ROOT_URL."add-post/" ));
  exit();
}
$post = SQLLib::SelectRow(sprintf_esc("SELECT * FROM posts LEFT JOIN users ON users.sceneID = posts.userID WHERE id = %d",$_GET["id"]));
if (!$post || (($post->userID != $_SESSION["userID"]) && !$currentUser->isAdmin))
{
  header("Location: ".ROOT_URL."show-posts/");
  exit();
}
if ($post->closureReason)
{
  header("Location: ".ROOT_URL."post/".(int)$post->id."/".hashify($post->title) );
  exit();
}

global $error;
$error = "";
if ($_POST)
{
  $prev = $_POST;
  function validate()
  {
    global $error;
    if (!$_POST["closureReason"])
    {
      $error = "You must select a reason!";
      return false;
    }
    return true;
  }
  if (validate())
  {
    $a = array();
    $a["closureReason"] = $_POST["closureReason"];
    $a["closureDescription"] = $_POST["closureDescription"];
    SQLLib::UpdateRow("posts",$a,"id=".(int)$_GET["id"]);
    header("Location: ".ROOT_URL."post/".(int)$_GET["id"]."/".hashify($post->title) );
  }
}

include_once("header.inc.php");
?>

<section id="content">
  <div>
    <article id='editpost' class='submitform box'>

      <h2>Want to close the post "<?=_html($post->title)?>"? Why?</h2>

      <?
      if ($error)
        printf("<div class='error'>%s</div>",$error);
      ?>

      <form method='post'>

        <label>Was your post ultimately successful?</label>
        <ul>
          <li><input type='radio' name='closureReason' value='success' id='closureReasonSuccess'<?=($prev["closureReason"]=="success"?" checked='checked'":"")?> required='yes'/> <label for='closureReasonSuccess'>Yes, I got what I wanted!</label></li>
          <li><input type='radio' name='closureReason' value='failure' id='closureReasonFailure'<?=($prev["closureReason"]=="failure"?" checked='checked'":"")?>/> <label for='closureReasonFailure'>No, I'm removing the post because I'm no longer interested.</label></li>
          <li><input type='radio' name='closureReason' value='other' id='closureReasonOther'<?=($prev["closureReason"]=="other"?" checked='checked'":"")?>/> <label for='closureReasonOther'>I want to close the post for some other reason.</label></li>
        </ul>

        <label class='success other'>Tell us more about <span class='success'>the successful cooperation</span><span class='neither'> / </span><span class='other'>why you want the post closed</span>!</label>
        <textarea class='success other' name='closureDescription'><?=_html($prev["closureDescription"])?></textarea>
        <small class='success other' id='tips'>
          <ul>
            <li class='success'>If you can, feel free to mention who helped you!</li>
            <li class='success'>Is there already a public result of the cooperation? Post a link!</li>
            <li class='other'>Is it because of a site bug? Or some other error? Is there something we can do to improve the site on that?</li>
          </ul>
        </small>

        <input type='submit' value='Close post'/>
      </form>

    </article>
  </div>
</section>

<script type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  $$("input[name=closureReason]").invoke("observe","click",function(ev){
    $$(".neither").invoke("hide");
    $$(".success,.failure,.other").invoke("hide");
    $$("."+ev.element().value).invoke("show");
  });
});
//-->
</script>
<?
include_once("footer.inc.php");
?>
