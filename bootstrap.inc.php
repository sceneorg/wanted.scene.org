<?
include_once("sqllib.inc.php");
include_once("functions.inc.php");
include_once("sceneid3lib-php/sceneid3.inc.php");

$sceneID = null;
if (POUET_TEST && class_exists("MySceneID"))
{
  $sceneID = new MySceneID( array(
    "clientID" => SCENEID_USER,
    "clientSecret" => SCENEID_PASS,
    "redirectURI" => ROOT_URL . "login/",
  ) );
}
else if (class_exists("SceneID3"))
{
  $sceneID = new SceneID3( array(
    "clientID" => SCENEID_USER,
    "clientSecret" => SCENEID_PASS,
    "redirectURI" => ROOT_URL . "login/",
  ) );
}
$sceneID->SetScope(array("basic","user:email"));
?>