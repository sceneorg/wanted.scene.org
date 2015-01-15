<!DOCTYPE html>
<html>
<head>
  <title>Wanted!<?=_html($TITLE?" :: ".$TITLE:"")?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="<?=ROOT_URL?>style.css" media="screen" />
  <link rel="shortcut icon" href="<?=ROOT_URL?>favicon.ico" type="image/x-icon"/>
  <link rel="alternate" type="application/rss+xml" title="RSS" href="<?=ROOT_URL?>rss/"/>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.2.0/prototype.js"></script>
  <script type="text/javascript" src="<?=ROOT_URL?>calendarview.js"></script>
<script type="text/javascript">
<!--
document.observe("dom:loaded",function(){
  // browsers should do this by default
  $$("time").each(function(item){ item.setAttribute("title",item.getAttribute("datetime")); });
});
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