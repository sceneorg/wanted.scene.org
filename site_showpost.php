<?
global $BODY_ID;
$BODY_ID = "singlepost";
include_once("bootstrap.inc.php");

$post = SQLLib::SelectRow(sprintf_esc("SELECT * FROM posts LEFT JOIN users ON users.sceneID = posts.userID WHERE id = %d",$_GET["id"]));
if (!$post)
{
  header("Location: ".ROOT_URL."show-posts/");
  exit();
}
include_once("header.inc.php");
?>
<section id="content">
  <div>
    <article>
      <div class='itemHeader'>
        <h3><?=_html($post->title)?></h3>
        <span class="author">Posted by <?=_html($post->displayName)?> on <?=_html($post->postDate)?></span>
      </div>
      <div class='body'>
        <?=_html($post->contents)?>
      </div>
    </article>
  </div>
</section>
<?
include_once("footer.inc.php");
?>
