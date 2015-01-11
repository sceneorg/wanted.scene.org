<?
include_once("rewriter.inc.php");

$rewriter = new Rewriter();
$rewriter->addRules(array(
  // site
  "^\/+login\/?$" => "login.php",
  "^\/+show-posts\/?$" => "site_showposts.php",
  "^\/+about\/?$" => "site_about.php",
));
$rewriter->rewrite();
?>