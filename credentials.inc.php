<?
// MySQL
define("SQL_HOST","localhost");
define("SQL_USERNAME","wanted");
define("SQL_PASSWORD","wanted123");
define("SQL_DATABASE","wanted");

// SceneID
define('SCENEID_USER', 'wanted');
define('SCENEID_PASS', 'wanted');
define('SCENEID_URL', 'https://fakocka.hopto.org/wanted/login/');

define('ROOT_URL','http://fakocka.hopto.org/wanted/');

define('TEST_MODE',true);

require_once( "sceneid3lib-php/sceneid3.inc.php");

class MySceneID extends SceneID3
{
  const ENDPOINT_TOKEN = "http://fakocka.hopto.org/sceneid/3/oauth/token/";
  const ENDPOINT_AUTH = "http://fakocka.hopto.org/sceneid/3/oauth/authorize/";
  const ENDPOINT_RESOURCE ="http://fakocka.hopto.org/sceneid/3/api/3.0";
}

?>