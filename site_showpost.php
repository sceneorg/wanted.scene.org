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
        <?
        if ($post->expiry)
        {
          if ($post->expiry < date("Y-m-d"))
          {
            printf("<div class='expiry done'>This post has expired.</div>");
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
  </div>
</section>
<?
include_once("footer.inc.php");
?>
