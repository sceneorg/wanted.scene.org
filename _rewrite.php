<?
include_once("rewriter.inc.php");

$rewriter = new Rewriter();
$rewriter->addRules(array(
  // site
  "^\/+login\/?$" => "login.php",
  "^\/+show-posts\/?$" => "site_showposts.php",
  "^\/+add-post\/?$" => "site_addpost.php",
  "^\/+post\/?$" => "site_showpost.php",
  "^\/+about\/?$" => "site_about.php",
));
$rewriter->rewrite();
?>