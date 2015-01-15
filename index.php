<?
global $BODY_ID;
$BODY_ID = "frontpage";
include_once("bootstrap.inc.php");
include_once("header.inc.php");
?>
<section id="bumper">
  <div>
    <article id="demand">
      <h2>I want</h2>
<? $count = SQLLib::SelectRow("SELECT count(*) AS c FROM posts WHERE intent='demand' AND (expiry IS NULL OR expiry > NOW())")->c; ?>
      <p>Currently, <?=$count?> people are looking for somebody to help them with their projects!</p>
      <a href="<?=ROOT_URL?>show-posts/?intent=demand">I can help!</a>
    </article>
    <article id="supply">
      <h2>I offer</h2>
<? $count = SQLLib::SelectRow("SELECT count(*) AS c FROM posts WHERE intent='supply' AND (expiry IS NULL OR expiry > NOW())")->c; ?>
      <p>At the same time, <?=$count?> people are offering their skills to help out others!</p>
      <a href="<?=ROOT_URL?>show-posts/?intent=supply">I need help!</a>
    </article>
    <article id="about">
      <h2>I... wat?</h2>
      <p>You don't know what this is about? No problem, we're all about sharing!</p>
      <a href="<?=ROOT_URL?>about/">Tell me!</a>
    </article>
  </div>
</section>

<section id="content">
  <div>
    <div id='news'>
<?
$posts = SQLLib::SelectRows("SELECT * FROM posts LEFT JOIN users ON users.sceneID = posts.userID ORDER BY postDate DESC LIMIT 5");
foreach($posts as $post)
{
?>
      <article>
        <div class='itemHeader area_<?=$post->area?>'>
          <h3><a href='<?=ROOT_URL?>post/<?=$post->id?>/<?=hashify($post->title)?>'><?=_html($post->title)?></a></h3>
          <span class="author">Posted by <?=_html($post->displayName)?> <?=sprintf("<time datetime='%s'>%s</time>",$post->postDate,dateDiffReadable(time(),$post->postDate))?></span>
        </div>
        <div class='body'>
          <?=_html(shortify($post->contents,500))?>
          <a class='readmore' href='<?=ROOT_URL?>post/<?=$post->id?>/<?=hashify($post->title)?>'>Read more...</a>
        </div>
      </article>
<?
}
?>
  <div id='pagination'>
    <a href="<?=ROOT_URL?>show-posts/">Read more posts &raquo;</a>
  </div>

    </div>
    <div id='sidebar'>
<?
if ($_SESSION["userID"]) {
$unread = SQLLib::SelectRow( sprintf_esc("select count(*) as cnt from messages where userRecipient = %d and `read` = 0",$_SESSION["userID"]) )->cnt;
?>
      <aside id="profile" class="box">
        <h2>Your info</h2>
        <ul>
          <li><a href="<?=ROOT_URL?>profile/">Your settings</a></li>
          <li><a href="<?=ROOT_URL?>show-posts/?mine=true">Your current posts</a></li>
          <li><a href="<?=ROOT_URL?>messages/">Your messages<?=($unread?sprintf(" - <span class='unread'>%d new!</span>",$unread):"")?></a></li>
          <li><a href="<?=ROOT_URL?>add-post/">Add a new post!</a></li>
          <li><a href="<?=ROOT_URL?>logout/">Log out</a></li>
        </ul>
      </aside>
<? } else { ?>
      <aside id="login" class="box">
        <h2>Log in!</h2>
        <a href="<?=ROOT_URL?>login/">Log in via SceneID!</a>
      </aside>
<? } ?>
      <aside id="categories" class="box">
        <h2>Categories</h2>
        <ul>
<?
$posts = SQLLib::SelectRows("SELECT area, count(*) AS c FROM posts WHERE (expiry IS NULL OR expiry > NOW()) GROUP BY area ORDER BY area");
foreach($posts as $post)
{
  $names = array(
    'code'    =>"Code",
    'graphics'=>"Graphics",
    'music'   =>"Music",
    'other'   =>"Other",
  );
?>
        <li><a href='<?=ROOT_URL?>show-posts/?area[<?=_html($post->area)?>]=on'><?=_html($names[$post->area])?> <span><?=$post->c?></span></a></li>
<?
}
?>
        </ul>

      </aside>
    </div>
  </div>
</section>
<?
include_once("footer.inc.php");
?>
