<?
include_once("sqllib.inc.php");
include_once("functions.inc.php");
include_once("sceneid3lib-php/sceneid3.inc.php");

@session_start();

$sceneID = null;
if (TEST_MODE && class_exists("MySceneID"))
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

$currentUser = null;
if ($_SESSION["userID"])
  $currentUser = SQLLib::SelectRow(sprintf_esc("select * from users where sceneID = %d",$_SESSION["userID"]));
?>