<?php
global $BODY_ID;
$BODY_ID = "testimonials";
include_once("bootstrap.inc.php");
include_once("header.inc.php");
?>

<section id="content">
  <div>
    <div class='box'>
      <h2>Testimonials</h2>
      <p class='body'>Here is a selection of projects where the authors found each other with the help of Wanted. Want to request or offer help yourself? <a href='<?=ROOT_URL?>add-post'>Click here!</a></p>
    </div>
<?php
$sql = new SQLSelect();
$sql->AddField("posts.*");
$sql->AddField("users.displayName");
$sql->AddTable("posts");
$sql->AddOrder("postDate DESC");
$sql->AddWhere("showcase = 1");
$sql->AddJoin("LEFT","users","users.sceneID = posts.userID");
$sql->SetLimit(10);
$sql = $sql->GetQuery();
//$sql = preg_replace("/^SELECT/i","SELECT SQL_CALC_FOUND_ROWS",$sql);
$posts = SQLLib::SelectRows( $sql );
//$total = SQLLib::SelectRow( "SELECT FOUND_ROWS() AS cnt" )->cnt;
foreach($posts as $post)
{
?>
      <article class='showcaseitem'>
        <div class='body'>
          <?php
          $c = parse_post($post->closureDescription);
          echo $c;
          ?>
        </div>
        <div class='itemFooter'>
          <span class="author">Original post: <a href='<?=ROOT_URL?>post/<?=$post->id?>/<?=hashify($post->title)?>'><?=_html($post->title)?></a> by <?=$post->displayName?></span>
        </div>
      </article>
<?php
}
?>
  </div>
</section>

<?php
include_once("footer.inc.php");
?>
