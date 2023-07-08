<?php
global $BODY_ID;
$BODY_ID = "docs";
include_once("bootstrap.inc.php");
include_once("header.inc.php");
?>

<section id="content">
  <div>
    <article id='about'>

    <h2>The "wanted" RSS Namespace</h2>

    <h3><code>wanted:intent</code></h3>

    <p><code>supply</code> if the post is offering something, <code>demand</code> if requesting</p>

    <h3><code>wanted:area</code></h3>

    <p>Possible values: <code>code</code>, <code>graphics</code>, <code>music</code> or <code>other</code></p>

    </article>
  </div>
</section>

<?php
include_once("footer.inc.php");
?>
