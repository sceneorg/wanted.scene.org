<?
global $BODY_ID;
$BODY_ID = "showposts";
include_once("bootstrap.inc.php");
include_once("header.inc.php");
?>

<section id="content">
  <div>
    <div id='news'>
<?
$sql = new SQLSelect();
$sql->AddTable("posts");
$sql->AddJoin("left","users","users.sceneID = posts.userID");
$sql->AddOrder("postDate DESC");
if ($_GET["q"])
{
  $terms = split_search_terms($_GET["q"]);
  foreach($terms as $term)
  {
    $sql->AddWhere( sprintf_esc("title LIKE '%%%s%%' OR contents LIKE '%%%s%%'",_like($term),_like($term)) );
  }
}
if ($_GET["area"])
{
  $cond = array();
  foreach($_GET["area"] as $area=>$v)
  {
    $cond[] = sprintf_esc("'%s'",$area);
  }
  $sql->AddWhere("area IN (".implode(",",$cond).")");
}
if ($_GET["expired"] != "on") $sql->AddWhere( "expiry IS NULL OR expiry > NOW()" );
if ($_GET["closed"] != "on") $sql->AddWhere( "closureReason IS NULL" );

if ($_GET["intent"] == "supply") $sql->AddWhere( "intent='supply'" );
else
if ($_GET["intent"] == "demand") $sql->AddWhere( "intent='demand'" );

if ($_GET["mine"] && $_SESSION["userID"])
  $sql->AddWhere( sprintf_esc( "userID = %d", $_SESSION["userID"] ) );

$perPage = 10;
$curPage = isset($_GET["page"]) ? ($_GET["page"] - 1) : 0;
$sql->SetLimit($perPage,$curPage * $perPage);
$sql = $sql->GetQuery();
$sql = preg_replace("/^SELECT/i","SELECT SQL_CALC_FOUND_ROWS",$sql);
$posts = SQLLib::SelectRows( $sql );
$total = SQLLib::SelectRow( "SELECT FOUND_ROWS() AS cnt" )->cnt;
foreach($posts as $post)
{
?>
      <article class='postlist<?=($post->closureReason?" closed":"")?>'>
        <div class='itemHeader area_<?=$post->area?> intent_<?=$post->intent?>'>
          <h3><a href='<?=ROOT_URL?>post/<?=$post->id?>/<?=hashify($post->title)?>'><?=_html($post->title)?></a><?=($post->expiry && ($post->expiry < date("Y-m-d"))?" <span class='expired'>expired</span>":"")?></h3>
          <span class="author">Posted by <?=_html($post->displayName)?> <?=sprintf("<time datetime='%s'>%s</time>",$post->postDate,dateDiffReadable(time(),$post->postDate))?></span>
        </div>
        <div class='body'>
          <?=_html(shortify($post->contents,500))?>
          <a class='readmore' href='<?=ROOT_URL?>post/<?=$post->id?>/<?=hashify($post->title)?>'>Read more...</a>
        </div>
      </article>
<?
}
if ($total > count($posts))
{
  printf("<div id='pagination'>\n");
  printf("  <ul>\n");
  $pageCount = (int)ceil($total / (float)$perPage);
  $str = $_GET;
  for ($x = 0; $x < $pageCount; $x++)
  {
    $str["page"] = $x + 1;
    printf("    <li%s><a href='%s'>%d</a></li>\n",($x == $curPage) ? " class='current'" : "",ROOT_URL."show-posts/?".http_build_query($str),$x+1);
  }
  printf("  </ul>\n");
  printf("</div>\n");
}
?>
    </div>
    <div id='sidebar'>
      <aside id="addnewpost_bumper" class="box">
        <h2>Nothing fits your needs?</h2>
        <a href="<?=ROOT_URL?>add-post/">Click here to post your own request for help or offer of help!</a>
      </aside>
      <aside id="search_options" class="box">
        <h2>Search options</h2>
        <form method='get'>
          <label>Search terms:</label>
          <input type='text' name='q' value='<?=_html($_GET["q"])?>'>
          <label>Are you looking for...</label>
          <ul>
            <li><input type='radio' name='intent' id='intentNone' value=''<?=($_GET["intent"]==""?" checked='checked'":"")?>/> <label for='intentNone'>anything</label></li>
            <li><input type='radio' name='intent' id='intentSupply' value='supply'<?=($_GET["intent"]=="supply"?" checked='checked'":"")?>/> <label for='intentSupply'>offers</label></li>
            <li><input type='radio' name='intent' id='intentDemand' value='demand'<?=($_GET["intent"]=="demand"?" checked='checked'":"")?>/> <label for='intentDemand'>requests</label></li>
          </ul>
          <label>...in the area of...</label>
          <ul>
            <li><input type='checkbox' id='areaCode' name='area[code]'<?=($_GET["area"]["code"]!=""?" checked='checked'":"")?>/> <label for='areaCode'>code</label></li>
            <li><input type='checkbox' id='areaGraphics' name='area[graphics]'<?=($_GET["area"]["graphics"]!=""?" checked='checked'":"")?>/> <label for='areaGraphics'>graphics</label></li>
            <li><input type='checkbox' id='areaMusic' name='area[music]'<?=($_GET["area"]["music"]!=""?" checked='checked'":"")?>/> <label for='areaMusic'>music</label></li>
            <li><input type='checkbox' id='areaOther' name='area[other]'<?=($_GET["area"]["other"]!=""?" checked='checked'":"")?>/> <label for='areaOther'>other</label></li>
          </ul>
          <div>
            <input type='checkbox' id='expired' name='expired'<?=($_GET["expired"]!=""?" checked='checked'":"")?>/> <label for='expired'>Include expired posts</label></li>
          </div>
          <div>
            <input type='checkbox' id='closed' name='closed'<?=($_GET["closed"]!=""?" checked='checked'":"")?>/> <label for='closed'>Include closed posts</label></li>
          </div>
          <? if ($_SESSION["userID"]) { ?>
          <div>
            <input type='checkbox' id='mine' name='mine'<?=($_GET["mine"]!=""?" checked='checked'":"")?>/> <label for='mine'>My posts only</label></li>
          </div>
          <? } ?>
          <input type='submit' value='Go!'>
        </form>
      </aside>
    </div>
  </div>
</section>

<?
include_once("footer.inc.php");
?>
