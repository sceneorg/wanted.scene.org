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
    <article id='about'>

    <h2>Admin stuff</h2>

<?
$cntMessages = SQLLib::SelectRow("select count(*) as c from messages")->c;
$cntUnique = SQLLib::SelectRow("SELECT count(*) as c from (select * from messages group by concat(if(userSender<userRecipient,userSender,userRecipient),':',if(userSender>userRecipient,userSender,userRecipient)) ) as m")->c;
printf("<p><b>%d</b> messages so far, <b>%d</b> conversations</p>",$cntMessages,$cntUnique);
?>

    </article>
  </div>
</section>

<?
include_once("footer.inc.php");
?>
