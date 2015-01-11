<!DOCTYPE html>
<html>
<head>
  <title>Wanted!</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="<?=ROOT_URL?>style.css" media="screen" />
</head>
<body<?=($BODY_ID?" id='"._html($BODY_ID)."'":"")?>>

<header>
  <h1><a href="<?=ROOT_URL?>">Demoscene's most wanted!</a></h1>
  <small>Yes we did use coder colors for that logo!</small>
  <form id="search" action="<?=ROOT_URL?>show-posts/" method='GET'>
    <input type='text' name='q' placeholder='Find something...'>
    <input type='submit' value='Go!'>
  </form>
</header>