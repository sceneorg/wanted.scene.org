<?
global $BODY_ID;
$BODY_ID = "admin";
include_once("bootstrap.inc.php");

if (!$currentUser || !$currentUser->isAdmin)
{
  header("Location: ".ROOT_URL);
  exit();
}

if ($_POST["showcaseRemove"])
{
  SQLLib::Query(sprintf_esc("update posts set showcase = 0 where id = %d",$_POST["showcaseRemove"]));
}
if ($_POST["showcaseAdd"])
{
  SQLLib::Query(sprintf_esc("update posts set showcase = 1 where id = %d",$_POST["showcaseAdd"]));
}

include_once("header.inc.php");
?>

<section id="content">
  <div>
    <article id='admin-showcase' class='box'>

      <h2>Admin stuff</h2>

      <div class='body'>
        <dl>
<?
$posts = SQLLib::SelectRows(sprintf_esc("SELECT * from posts WHERE closureReason = '%s'",$_GET["reason"]));
foreach($posts as $p)
{
  printf("<dt>%s</dt>\n",_html($p->title));
  printf("<dd>%s\n",_html($p->closureDescription));
  if ($p->showcase)
  {
    printf("<form method='post'><input type='hidden' name='showcaseRemove' value='%d'/><input type='submit' value='Remove from showcase'/></form>",$p->id);
  }
  else
  {
    printf("<form method='post'><input type='hidden' name='showcaseAdd' value='%d'/><input type='submit' value='Add to showcase'/></form>",$p->id);
  }
  echo "</dd>";
}
?>
        </dl>
      </div>
    </article>
  </div>
</section>

<?
include_once("footer.inc.php");
?>