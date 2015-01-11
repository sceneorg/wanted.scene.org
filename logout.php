<?
require_once("bootstrap.inc.php");
require_once("functions.inc.php");

unset($_SESSION["userID"]);

$sceneID->Reset();

session_regenerate_id(true);

header("Location: ".ROOT_URL);
?>