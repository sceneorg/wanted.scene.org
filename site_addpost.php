<?php
global $BODY_ID;
$BODY_ID = "addpost";
include_once("bootstrap.inc.php");

if (!$_SESSION["userID"])
{
  header("Location: ".ROOT_URL."login/?return=".rawurlencode( ROOT_URL."add-post/" ));
  exit();
}
global $error;
$error = "";
if ($_POST)
{
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
  if (validate())
  {
    $post = array();
    $post["intent"] = $_POST["intent"];
    $post["area"] = $_POST["area"];
    $post["title"] = $_POST["title"];
    $post["contents"] = $_POST["contents"];
    $post["expiry"] = $_POST["expiry"] == "indefinite" ? null : $_POST["expiryDate"];
    $post["userID"] = $_SESSION["userID"];
    $post["postDate"] = date("Y-m-d H:i:s");
    $id = SQLLib::InsertRow("posts",$post);
    header("Location: ".ROOT_URL."post/".$id."/".hashify($_POST["title"]) );
  }
}

include_once("header.inc.php");
?>

<section id="content">
  <div>
    <article id='addpost' class='submitform box'>

      <h2>Post a post!</h2>

      <?php
      if ($error)
        printf("<div class='error'>%s</div>",$error);
      ?>

      <form method='post'>

        <label>What do you want to post about?</label>
        <ul>
          <li><input type='radio' name='intent' value='demand' id='intentDemand'<?=(@$_POST["intent"]=="demand"?" checked='checked'":"")?> required='yes'/> <label for='intentDemand'>I'm looking for help!</label></li>
          <li><input type='radio' name='intent' value='supply' id='intentSupply'<?=(@$_POST["intent"]=="supply"?" checked='checked'":"")?>/> <label for='intentSupply'>I'm offering help!</label></li>
        </ul>

        <label>What area are you <span class='supply'>offering</span><span class='neitherIntent'> / </span><span class='demand'>looking for</span> help in?</label>
        <ul>
          <li><input type='radio' name='area' value='code'    <?=(@$_POST["area"]=="code"    ?" checked='checked'":"")?> id='areaCode' required='yes'/> <label for='areaCode'>Coding</label></li>
          <li><input type='radio' name='area' value='graphics'<?=(@$_POST["area"]=="graphics"?" checked='checked'":"")?> id='areaGraphics'/> <label for='areaGraphics'>Graphics (3D or 2D art, web design, etc.)</label></li>
          <li><input type='radio' name='area' value='music'   <?=(@$_POST["area"]=="music"   ?" checked='checked'":"")?> id='areaMusic'/> <label for='areaMusic'>Music / sound design</label></li>
          <li><input type='radio' name='area' value='other'   <?=(@$_POST["area"]=="other"   ?" checked='checked'":"")?> id='areaOther'/> <label for='areaOther'>Other areas (party organizing)</label></li>
        </ul>

        <label>Sum up your <span class='supply'>offer</span><span class='neitherIntent'> / </span><span class='demand'>request</span> in one succinct sentence.</label>
        <small>(E.g.: <span class='demand'>"Looking for Amiga coder", </span><span class='supply'>"Graphics artist looking for group",</span> <span class='demand'>"Need audio for game", </span>...)</small>
        <input type='text' name='title' required='yes' maxlength='200' value='<?=_html(@$_POST["title"])?>'/>

        <label>Describe what you're looking for.</label>
        <textarea name='contents' required='yes'><?=_html(@$_POST["contents"])?></textarea>
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
            <li class='demand'>Currently we do not allow commercial job offers. If you still wish to post one, <a href="mailto:staff@scene.org">send us a mail</a>!</li>
          </ul>
        </small>

        <label>How long is the <span class='supply'>offer</span><span class='neitherIntent'> / </span><span class='demand'>request</span> valid for?</label>
        <ul>
          <li><input type='radio' name='expiry' value='indefinite'<?=(@$_POST["expiry"]=="indefinite"?" checked='checked'":"")?> id='expiryIndefinite' required='yes'/> <label for='expiryIndefinite'>Indefinitely, the <span class='supply'>offer</span><span class='neitherIntent'> / </span><span class='demand'>request</span> stands until it's removed.</label></li>
          <li><input type='radio' name='expiry' value='concrete'  <?=(@$_POST["expiry"]=="concrete"  ?" checked='checked'":"")?> id='expiryConcrete'/> <label for='expiryConcrete'>The <span class='supply'>offer</span><span class='neitherIntent'> / </span><span class='demand'>request</span> expires on</label> <input name='expiryDate' id='expiryDate' value="<?=date("Y-m-d",time() + 60 * 60 * 24 * 30)?>"/></li>
        </ul>

        <input type='submit' value='Submit post!'/>
      </form>

    </article>
  </div>
</section>

<script type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  $$("input[name=expiryDate]").first().disabled = true;
  $$("input[name=expiry]").invoke("observe","click",function(ev){
    $$("input[name=expiryDate]").first().disabled = (ev.element().value == "indefinite");
  });
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
  Calendar.setup(
    {
      dateField: 'expiryDate',
      triggerElement: 'expiryDate',
      firstDayOfTheWeek: 1,
    }
  );

});
//-->
</script>
<?php
include_once("footer.inc.php");
?>
