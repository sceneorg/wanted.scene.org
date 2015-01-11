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

?>