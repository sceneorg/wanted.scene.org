<?php
global $BODY_ID;
$BODY_ID = "about";
include_once("bootstrap.inc.php");
include_once("header.inc.php");
?>

<section id="content">
  <div>
    <article id='about'>

    <h2>Why this is useful for you</h2>

    <h3>Find the skills you need or offer what you got</h3>

    <p>Ever had a great idea for a demo but insufficient means to get it done? Tired of trying to find collaborators on IRC or pou&euml;t.net and getting no useful response?</p>

    <p>On <i>Wanted!</i> you can ask your peers for help and get them excited for your projects - no matter what kind.</p>

    <p>But even if you're a seasoned veteran - if you have skills in one particular field, a lot of time on your hands but nobody to work with or just looking for some side-projects, this is the place where you can offer help out others do cool stuff.</p>

    <h3>Easy, hassle free communication</h3>

    <p>Sign in, post your offer or need and wait for the responses to come in. Every discussion between you and others is private - free from negative and nonsensical public comments.</p>

    <h3>Built on <a href="https://id.scene.org/">SceneID</a></h3>

    <p>If you're already active in the Demoscene you probably have a SceneID - and if you do, you can start posting right away. If you don't, signup is really easy (and free)!</p>

    <h3>That's it?</h3>

    <p>That's it. Now go on, <a href="<?=ROOT_URL?>show-posts">collaborate already</a>!</p>


    </article>
  </div>
</section>

<?php
include_once("footer.inc.php");
?>
