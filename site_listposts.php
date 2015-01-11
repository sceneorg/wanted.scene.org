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

if ($_GET["intent"] == "supply") $sql->AddWhere( "intent='supply'" );
else 
if ($_GET["intent"] == "demand") $sql->AddWhere( "intent='demand'" );

$sql->SetLimit(10);
$posts = SQLLib::SelectRows( $sql->GetQuery() );
foreach($posts as $post)
{
?>
      <article>
        <div class='itemHeader area_<?=$post->area?>'>
          <h3><a href='<?=ROOT_URL?>post/<?=$post->id?>/<?=hashify($post->title)?>'><?=_html($post->title)?></a></h3>
          <span class="author">Posted by <?=_html($post->displayName)?> on <?=_html($post->postDate)?></span>
        </div>
        <div class='body'>
          <?=_html(shortify($post->contents,500))?>
          <a class='readmore' href='<?=ROOT_URL?>post/<?=$post->id?>/<?=hashify($post->title)?>'>Read more...</a>
        </div>
      </article>
<?
}
?>      
    </div>
    <div id='sidebar'>
      <aside id="addnewpost_bumper">
        <h2>Nothing fits your needs?</h2>
        <a href="<?=ROOT_URL?>add-post/">Click here to post your own request for help or offer of help!</a>
      </aside>
      <aside id="search_options">
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
          <input type='submit' value='Go!'>
        </form>
      </aside>
    </div>
  </div>
</section>

<?
include_once("footer.inc.php");
?>
