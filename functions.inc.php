<?php
function _html( $s )
{
  return htmlspecialchars( $s ?: "", ENT_QUOTES );
}
function _js( $s )
{
  return addcslashes( $s ?: "", "\x00..\x1f'\"\\/" );
}
function _like( $s )
{
  return addcslashes( $s ?: "", "%_" );
}

function shortify( $text, $length = 100 )
{
  if (mb_strlen($text,"utf-8") <= $length) return $text;
  $z = mb_stripos($text," ",$length,"utf-8");
  return mb_substr($text,0,$z?$z:$length,"utf-8")."...";
}

function split_search_terms( $str )
{
  preg_match_all('/([^\s"]+)|"([^"]*)"/',$str,$m);
  $terms = array();
  foreach($m[0] as $k=>$v)
    $terms[] = $m[1][$k] ? $m[1][$k] : $m[2][$k];
  return $terms;
}

function hashify($s) {
  $hash = strtolower($s);
  $hash = preg_replace("/[^\w]+/","-",$hash);
  $hash = preg_replace("/^[_]+/","",$hash);
  $hash = preg_replace("/[_]+$/","",$hash);
  $hash = trim($hash,"-");
  return $hash;
}

function parse_post($s) {
  $s = _html($s);
  $s = preg_replace_callback("/([a-z]+:\/\/\S+)/",function($m){
    $url = parse_url($m[1]);
    return "<a href='".$m[1]."' target='_blank'>".$url["host"]."</a>";
  },$s);
  $s = nl2br($s);
  return $s;
}

function myMail( $to, $subject, $message = "", $additional_headers = "", $additional_parameters = "" )
{
  if (TEST_MODE === true)
  {
    file_put_contents("out.email.txt",$to . "\n\n" . $subject . "\n\n" . $message );
    return true;
  }

  return mail( $to, $subject, $message, $additional_headers, $additional_parameters );
}

function hideEmail( $email )
{
  return preg_replace_callback("/([a-zA-Z0-9]*)/",function($m){
    return str_pad(substr($m[1],0,2),strlen($m[1]),"*");
  },$email);
}

function dateDiffReadable( $a, $b )
{
  if (is_string($a)) $a = strtotime($a);
  if (is_string($b)) $b = strtotime($b);
  $dif = $a - $b;
  if ($dif < 60) return "a few moments ago"; $dif /= 60;
  if ($dif < 60) return (int)$dif." minutes ago"; $dif /= 60;
  if ($dif < 24) return (int)$dif." hours ago"; $dif /= 24;
  if ($dif < 30) return (int)$dif." days ago"; $dif /= 30;
  if ($dif < 12) return (int)$dif." months ago"; $dif /= 12;
  return (int)$dif." years ago";
}
?>