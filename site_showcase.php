<?
global $BODY_ID;
$BODY_ID = "testimonials";
include_once("bootstrap.inc.php");
include_once("header.inc.php");
?>

<section id="content">
  <div>
<?
$sql = new SQLSelect();
$sql->AddTable("posts");
$sql->AddOrder("postDate DESC");
$sql->AddWhere("showcase = 1");
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
          <?
          $c = parse_post($post->closureDescription);
          echo $c;
          ?>
        </div>
        <div class='itemFooter'>
          <span class="author">Original post: <a href='<?=ROOT_URL?>post/<?=$post->id?>/<?=hashify($post->title)?>'><?=_html($post->title)?></a><?=($post->expiry && ($post->expiry < date("Y-m-d"))?" <span class='expired'>expired</span>":"")?></span>
        </div>
      </article>
<?
}
?>
  </div>
</section>

<?
include_once("footer.inc.php");
?>
