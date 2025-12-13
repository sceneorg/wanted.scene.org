<!DOCTYPE html>
<html>
<head>
  <title>Wanted!<?=_html($TITLE?" :: ".$TITLE:"")?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="<?=ROOT_URL?>style.css?<?=filemtime("style.css")?>" media="screen" />
  <link rel="shortcut icon" href="<?=ROOT_URL?>favicon.ico" type="image/x-icon"/>
  <link rel="alternate" type="application/rss+xml" title="RSS" href="<?=ROOT_URL?>rss/"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0;" />
<?php
  if ($metaValues) foreach ($metaValues as $k=>$v)
  {
    printf("  <meta property=\"%s\" content=\"%s\"/>\n",$k,_html($v));
  }
?>
  <!--[if lt IE 9]><script src="//ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script><![endif]-->
  <!--[if IE]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
  
<script type="text/javascript">
<!--
window.load = function(){
  // browsers should do this by default
  document.querySelectorAll("time").forEach(function(item){ 
    item.setAttribute("title",item.getAttribute("datetime"));
  });
};
//-->
</script>  
</head>
<body<?=($BODY_ID?" id='"._html($BODY_ID)."'":"")?>>

<header>
  <h1><a href="<?=ROOT_URL?>">The demoscene's most wanted!</a></h1>
  <small>Yes we did use coder colors for the logo!</small>
  <form id="search" action="<?=ROOT_URL?>show-posts/" method='GET'>
    <input type='text' name='q' placeholder='Find something...' required='yes'>
    <input type='submit' value='Go!'>
  </form>
</header>