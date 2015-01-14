<?
include_once("rewriter.inc.php");

$rewriter = new Rewriter();
$rewriter->addRules(array(
  // site
  "^\/+login\/?$" => "login.php",
  "^\/+logout\/?$" => "logout.php",
  
  "^\/+show-posts\/?$" => "site_listposts.php",
  "^\/+add-post\/?$" => "site_addpost.php",
  "^\/+post\/([0-9]+).*$" => "site_showpost.php?id=$1",
  "^\/+about\/?$" => "site_about.php",
  
  "^\/+rss\/?$" => "rss.php",
));
$rewriter->rewrite();
?>