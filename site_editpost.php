<?php
global $BODY_ID;
$BODY_ID = "editpost";
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

$prev = get_object_vars($post);
$prev["expiry"] = $post->expiry != null ? "concrete" : "indefinite";
global $error;
$error = "";
if ($_POST)
{
  $prev = $_POST;
  function validate()
  {
    global $error;
    if ($_POST["expiry"] == "concrete" && $_POST["expiryDate"] <= date("Y-m-d"))
    {
      $error = "This post has already expired?!";
      return false;
    }
    return true;
  }
  if ($_POST["delete"] && $currentUser->isAdmin)
  {
    SQLLib::Query("update messages set relatedPost=null where relatedPost=".(int)$_GET["id"]);
    SQLLib::Query("delete from posts where id=".(int)$_GET["id"]);
    header("Location: ".ROOT_URL."show-posts/?mine=true#success" );
  }
  if ($_POST["close"])
  {
    header("Location: ".ROOT_URL."close-post/?id=".(int)$_GET["id"] );
    exit();
  }
  if (validate())
  {
    $post = array();
    $post["intent"] = $_POST["intent"];
    $post["area"] = $_POST["area"];
    $post["title"] = $_POST["title"];
    $post["contents"] = $_POST["contents"];
    $post["expiry"] = $_POST["expiry"] == "indefinite" ? null : $_POST["expiryDate"];
    //$post["userID"] = $_SESSION["userID"];
    //$post["postDate"] = date("Y-m-d H:i:s");
    SQLLib::UpdateRow("posts",$post,"id=".(int)$_GET["id"]);
    header("Location: ".ROOT_URL."post/".(int)$_GET["id"]."/".hashify($_POST["title"]) );
  }
}

include_once("header.inc.php");
?>

<section id="content">
  <div>
    <article id='editpost' class='submitform box'>

      <h2>Post a post!</h2>

      <?php
      if ($error)
        printf("<div class='error'>%s</div>",$error);
      ?>

      <form method='post'>

        <label>What do you want to post about?</label>
        <ul>
          <li><input type='radio' name='intent' value='demand' id='intentDemand'<?=($prev["intent"]=="demand"?" checked='checked'":"")?> required='yes'/> <label for='intentDemand'>I'm looking for help!</label></li>
          <li><input type='radio' name='intent' value='supply' id='intentSupply'<?=($prev["intent"]=="supply"?" checked='checked'":"")?>/> <label for='intentSupply'>I'm offering help!</label></li>
        </ul>

        <label>What area are you <span class='supply'>offering</span><span class='neitherIntent'> / </span><span class='demand'>looking for</span> help in?</label>
        <ul>
          <li><input type='radio' name='area' value='code'    <?=($prev["area"]=="code"    ?" checked='checked'":"")?> id='areaCode' required='yes'/> <label for='areaCode'>Coding</label></li>
          <li><input type='radio' name='area' value='graphics'<?=($prev["area"]=="graphics"?" checked='checked'":"")?> id='areaGraphics'/> <label for='areaGraphics'>Graphics (3D or 2D art, web design, etc.)</label></li>
          <li><input type='radio' name='area' value='music'   <?=($prev["area"]=="music"   ?" checked='checked'":"")?> id='areaMusic'/> <label for='areaMusic'>Music / sound design</label></li>
          <li><input type='radio' name='area' value='other'   <?=($prev["area"]=="other"   ?" checked='checked'":"")?> id='areaOther'/> <label for='areaOther'>Other areas (party organizing)</label></li>
        </ul>

        <label>Sum up your <span class='supply'>offer</span><span class='neitherIntent'> / </span><span class='demand'>request</span> in one succinct sentence.</label>
        <small>(E.g.: <span class='demand'>"Looking for Amiga coder", </span><span class='supply'>"Graphics artist looking for group",</span> <span class='demand'>"Need audio for game", </span>...)</small>
        <input type='text' name='title' required='yes' maxlength='200' value='<?=_html($prev["title"])?>'/>

        <label>Describe what you're looking for.</label>
        <textarea name='contents' required='yes'><?=_html($prev["contents"])?></textarea>
        <small id='tips'>
          Here are a few tips on what to write:
          <ul>
            <li>What country / timezone are you in? If your partners are around the world, is that a problem? If necessary, what languages do you speak?</li>
            <li>Are you looking for a one-off project or a long-term partnership?
              <ul>
                <li class='demand'>If it's just for one project, what <u>is</u> the project? (You don't need to be specific if you want to keep it secret, but giving an idea is always useful.)</li>
                <li class='demand'>Is it a demo, intro, wild demo, website, ...?</li>
                <li class='demand'>What is the deadline, if there is one?</li>
              </ul>
            </li>
            <li>What tools do you use? What formats can your work in?</li>
            <li class='supply'>Do you have a portfolio? (E.g., Demozoo profile, Pou&euml;t, <span class='code other'>Github, </span><span class='music other'>Soundcloud, Modarchive, </span><span class='graphics other'>Artcity, DeviantART, </span>...)</li>
            <li>Whoever is interested will be able to contact you in email through this website; you don't need to provide an email address.</li>
          </ul>
        </small>

        <label>How long is the <span class='supply'>offer</span><span class='neitherIntent'> / </span><span class='demand'>request</span> valid for?</label>
        <ul>
          <li><input type='radio' name='expiry' value='indefinite'<?=($prev["expiry"]=="indefinite"?" checked='checked'":"")?> id='expiryIndefinite' required='yes'/> <label for='expiryIndefinite'>Indefinitely, the <span class='supply'>offer</span><span class='neitherIntent'> / </span><span class='demand'>request</span> stands until it's removed.</label></li>
          <li><input type='radio' name='expiry' value='concrete'  <?=($prev["expiry"]=="concrete"  ?" checked='checked'":"")?> id='expiryConcrete'/> <label for='expiryConcrete'>The <span class='supply'>offer</span><span class='neitherIntent'> / </span><span class='demand'>request</span> expires on</label> <input name='expiryDate' id='expiryDate' value="<?=date("Y-m-d",time() + 60 * 60 * 24 * 30)?>"/></li>
        </ul>

        <input type='submit' value='Apply changes'/>
        <div id="removeButtons">
          <input type='submit' name='close' id='closePost' value='Close post'/>
<?php if ($currentUser->isAdmin){?>
          <input type='submit' name='delete' id='deletePost' value='Delete post!'/>
<?php }?>
        </div>
      </form>

    </article>
  </div>
</section>

<script type="text/javascript">
<!--
function disableDate()
{
  $$("input[name=expiryDate]").first().disabled = !$$("input[name=expiry][value=concrete]").first().checked;
}
document.observe("dom:loaded",function(){
  $$("input[name=expiryDate]").first().disabled = true;
  $$("input[name=expiry]").invoke("observe","click",function(ev){
    disableDate();
  });
  disableDate();
  /*
  $$("input[name=intent]").invoke("observe","click",function(ev){
    $$(".neitherIntent").invoke("hide");
    $$(".supply,.demand").invoke("hide");
    $$("."+ev.element().value).invoke("show");
  });
  $$("input[name=area]").invoke("observe","click",function(ev){
    $$(".neitherArea").invoke("hide");
    $$(".code,.music,.graphics").invoke("hide");
    $$("."+ev.element().value).invoke("show");
  });
  */
  Calendar.setup(
    {
      dateField: 'expiryDate',
      triggerElement: 'expiryDate',
      firstDayOfTheWeek: 1,
    }
  );
  $("deletePost").observe("click",function(ev){
    if (!confirm("Are you sure you want to delete this post?"))
      ev.stop();
  });
});
//-->
</script>
<?php
include_once("footer.inc.php");
?>
