<?
include_once("rewriter.inc.php");

$rewriter = new Rewriter();
$rewriter->addRules(array(
  // site
  "^\/+login\/?$" => "login.php",
  "^\/+logout\/?$" => "logout.php",

  "^\/+messages\/?$" => "site_messages.php",
  "^\/+show-posts\/?$" => "site_listposts.php",
  "^\/+add-post\/?$" => "site_addpost.php",
  "^\/+edit-post\/?$" => "site_editpost.php",
  "^\/+post\/([0-9]+).*$" => "site_viewpost.php?id=$1",
  "^\/+profile\/?$" => "site_profile.php",
  "^\/+about\/?$" => "site_about.php",

  "^\/+admin\/?$" => "site_admin.php",

  "^\/+docs\/rss\-namespace\/?$" => "site_docs_rssns.php",

  "^\/+rss\/?$" => "rss.php",
));
$rewriter->rewrite();
?>