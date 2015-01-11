<?
include_once("rewriter.inc.php");

$rewriter = new Rewriter();
$rewriter->addRules(array(
  // site
  "^\/+login\/?$" => "login.php",
  "^\/+show-posts\/?$" => "site_listposts.php",
  "^\/+add-post\/?$" => "site_addpost.php",
  "^\/+post\/(\d+)\/?" => "site_showpost.php?id=$1",
  "^\/+about\/?$" => "site_about.php",
));
$rewriter->rewrite();
?>