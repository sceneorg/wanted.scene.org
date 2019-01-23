<?
include_once("bootstrap.inc.php");

header("Content-type: application/rss+xml; charset=utf-8");

echo "<"."?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n";

$sql = new SQLSelect();
$sql->AddTable("posts");
$sql->AddJoin("left","users","users.sceneID = posts.userID");
if ($_GET["random"]=="full")
  $sql->AddOrder("RAND()");
else if ($_GET["random"]=="weighted")
  $sql->AddOrder("IF(DATEDIFF(NOW(),postDate)<30,postDate,RAND()) DESC");
else
  $sql->AddOrder("postDate DESC");
$sql->AddWhere("closureReason IS NULL");

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
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:wanted="<?=ROOT_URL?>docs/rss-namespace/">
	<channel>
		<title>Wanted!</title>
		<link><?=ROOT_URL?></link>
		<atom:link href="<?=ROOT_URL?>rss" rel="self" type="application/rss+xml" />
		<description>The demoscene's most wanted!</description>
<? foreach ($posts as $post) { ?>
		<item>
			<title><?=_html($post->title)?></title>
			<link><?=ROOT_URL?>post/<?=$post->id?>/<?=hashify($post->title)?></link>
			<guid isPermaLink="false">wanted<?=$post->id?></guid>
			<pubDate><?=date("r",strtotime($post->postDate))?></pubDate>
			<description><![CDATA[<?=_html(shortify($post->contents,500))?>]]></description>
			<wanted:intent><?=_html($post->intent)?></wanted:intent>
			<wanted:area><?=_html($post->area)?></wanted:area>
		</item>
<? } ?>
  </channel>
</rss>