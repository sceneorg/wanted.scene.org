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
printf("<p><b>%d</b> messages so far</p>",$cntMessages);
?>

    </article>
  </div>
</section>

<?
include_once("footer.inc.php");
?>
