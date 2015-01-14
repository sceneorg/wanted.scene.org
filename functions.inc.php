<?
function _html( $s )
{
  return htmlspecialchars( $s, ENT_QUOTES );
}
function _js( $s )
{
  return addcslashes( $s, "\x00..\x1f'\"\\/" );
}
function _like( $s )
{
  return addcslashes($s,"%_");
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
  $s = preg_replace("/([a-z]+:\/\/\S+)/","<a href='$1' target='_blank'>$1</a>",$s);
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

?>