<?php
global $BODY_ID;
$BODY_ID = "profile";
include_once("bootstrap.inc.php");

if (!$_SESSION["userID"])
{
  header("Location: ".ROOT_URL."login/");
  exit();
}

if ($_POST)
{
  $a = array();
  $a["wantsMail"] = $_POST["wantsEmail"] == "on";
  SQLLib::UpdateRow("users",$a,"sceneID=".(int)$_SESSION["userID"]);
  header("Location: ".ROOT_URL."profile#success");
  exit();
}

include_once("header.inc.php");
?>
<section id="content">
  <div>
    <div id='profile' class='box'>
      <h2>Your settings</h2>
      <form method='post'>
        <div>
          <input type='checkbox' name='wantsEmail'<?=($currentUser->wantsMail?" checked='checked'":"")?>>
          <label>Notify in email when you're sent a message</label>
        </div>
        <p>Your current email: <b><?=hideEmail($currentUser->email)?></b> - want to change it? <a href="https://id.scene.org/profile">Visit your SceneID profile</a></p>
        <input type='submit' name="submit" value='Apply changes'/>
      </form>
    </div>
  </div>
</section>
<?php
include_once("footer.inc.php");
?>