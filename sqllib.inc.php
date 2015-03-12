<?
require_once("credentials.inc.php");

global $SQLLIB_QUERIES;
$SQLLIB_QUERIES = array();

global $SQLLIB_ARRAYS_CLEANED;
$SQLLIB_ARRAYS_CLEANED = false;

class SQLLib {
  public static $link;
  public static $debugMode = false;
  public static $charset = "";

  static function Connect()
  {
    SQLLib::$link = mysqli_connect(SQL_HOST,SQL_USERNAME,SQL_PASSWORD,SQL_DATABASE);
    if (mysqli_connect_errno(SQLLib::$link))
      die("Unable to connect MySQL: ".mysqli_connect_error());

    $charsets = array("utf8mb4","utf8");
    SQLLib::$charset = "";
    foreach($charsets as $c)
    {
      if (mysqli_set_charset(SQLLib::$link,$c))
      {
        SQLLib::$charset = $c;
        break;
      }
    }
    if (!SQLLib::$charset)
    {
      die("Error loading any of the character sets:");
    }
  }

  static function Disconnect()
	{
    mysqli_close(SQLLib::$link);
  }

  static function Query($cmd)
  {
    global $SQLLIB_QUERIES;

    if (SQLLib::$debugMode)
    {
      $start = microtime(true);
      $r = @mysqli_query(SQLLib::$link,$cmd);
      if(!$r) throw new Exception("<pre>\nMySQL ERROR:\nError: ".mysqli_error(SQLLib::$link)."\nQuery: ".$cmd);
      $end = microtime(true);
      $SQLLIB_QUERIES[$cmd] = $end - $start;
    }
    else
    {
      $r = @mysqli_query(SQLLib::$link,$cmd);
      if(!$r) throw new Exception("<pre>\nMySQL ERROR:\nError: ".mysqli_error(SQLLib::$link)."\nQuery: ".$cmd);
      $SQLLIB_QUERIES[] = "*";
    }

    return $r;
  }

  static function Fetch($r)
  {
    return mysqli_fetch_object($r);
  }

  static function SelectRows($cmd)
  {
    $r = SQLLib::Query($cmd);
    $a = Array();
    while($o = SQLLib::Fetch($r)) $a[]=$o;
    return $a;
  }

  static function SelectRow($cmd)
  {
    if (stristr($cmd,"select ")!==false && stristr($cmd," limit ")===false) // not exactly nice but i'll help
      $cmd .= " LIMIT 1";
    $r = SQLLib::Query($cmd);
    $a = SQLLib::Fetch($r);
    return $a;
  }

  static function InsertRow($table,$o)
  {
    global $SQLLIB_ARRAYS_CLEANED;
    if (!$SQLLIB_ARRAYS_CLEANED)
      trigger_error("Arrays not cleaned before InsertRow!",E_USER_ERROR);

    if (is_object($o)) $a = get_object_vars($o);
    else if (is_array($o)) $a = $o;
    $keys = Array();
    $values = Array();
    foreach($a as $k=>$v) {
      $keys[]="`".mysqli_real_escape_string(SQLLib::$link,$k)."`";
      if ($v!==NULL) $values[]="'".mysqli_real_escape_string(SQLLib::$link,$v)."'";
      else           $values[]="null";
    }

    $cmd = sprintf("insert %s (%s) values (%s)",
      $table,implode(", ",$keys),implode(", ",$values));

    $r = SQLLib::Query($cmd);

    return mysqli_insert_id(SQLLib::$link);
  }

  static function UpdateRow($table,$o,$where)
  {
    global $SQLLIB_ARRAYS_CLEANED;
    if (!$SQLLIB_ARRAYS_CLEANED)
      trigger_error("Arrays not cleaned before UpdateRow!",E_USER_ERROR);

    if (is_object($o)) $a = get_object_vars($o);
    else if (is_array($o)) $a = $o;
    $set = Array();
    foreach($a as $k=>$v) {
      if ($v===NULL)
      {
        $set[] = sprintf("`%s`=null",mysqli_real_escape_string(SQLLib::$link,$k));
      }
      else
      {
        $set[] = sprintf("`%s`='%s'",mysqli_real_escape_string(SQLLib::$link,$k),mysqli_real_escape_string(SQLLib::$link,$v));
      }
    }
    $cmd = sprintf("update %s set %s where %s",
      $table,implode(", ",$set),$where);
    SQLLib::Query($cmd);
  }

  /*
  UpdateRowMulti allows batched updates on multiple rows at once.

  Syntax:
  $tuples = array(
    array( "keyColumn" => 1, "col1" => "abc", "col2" => "def" ),
    array( "keyColumn" => 2, "col1" => "ghi", "col2" => "jkl" ),
  );
  $key = "keyColumn";

  NOTE: the first tuple defines keys. If your tuples are uneven, you're on your own.
  */
  static function UpdateRowMulti( $table, $key, $tuples )
  {
    if (!count($tuples))
      return;
    if (!is_array($tuples[0]))
      throw new Exception("Has to be array!");

    $fields = array_keys( $tuples[0] );

    $sql = "UPDATE ".$table;
    $keys = array();
    foreach($fields as $field)
    {
      if ($field == $key) continue;
      foreach($tuples as $tuple)
        $cond .= sprintf_esc(" WHEN %d THEN '%s' ",$tuple[$key],$tuple[$field]);
      $sql .= " SET `".$field."` = (CASE `".$key."` ".$cond." END)";
    }
    foreach($tuples as $tuple)
      $keys[] = $tuple[$key];
    $sql .= " WHERE `".$key."` IN (".implode(",",$keys).")";

    //echo $sql."\n\n";
    SQLLib::Query($sql);
  }

  static function UpdateOrInsertRow($table,$o,$where)
  {
    if (SQLLib::SelectRow(sprintf("SELECT * FROM %s WHERE %s",$table,$where)))
      return SQLLib::UpdateRow($table,$o,$where);
    else
      return SQLLib::InsertRow($table,$o);
  }

  static function StartTransaction()
  {
    mysqli_autocommit(SQLLib::$link, FALSE);
  }
  static function FinishTransaction()
  {
    mysqli_commit(SQLLib::$link);
    mysqli_autocommit(SQLLib::$link, TRUE);
  }
  static function CancelTransaction()
  {
    mysqli_rollback(SQLLib::$link);
    mysqli_autocommit(SQLLib::$link, TRUE);
  }
}

class SQLTrans
{
  var $rollback;
  function __construct() {
    SQLLib::StartTransaction();
    $rollback = false;
  }
  function Rollback() {
    $this->rollback = true;
  }
  function __destruct() {
    if (!$rollback)
      SQLLib::FinishTransaction();
    else
      SQLLib::CancelTransaction();
  }
}

class SQLSelect
{
  var $fields;
  var $tables;
  var $conditions;
  var $joins;
  var $orders;
  var $groups;
  var $limit;
  var $offset;

  function SQLSelect()
	{
    $this->fields = array();
    $this->tables = array();
    $this->conditions = array();
    $this->joins = array();
    $this->orders = array();
    $this->groups = array();
    $this->limit = NULL;
    $this->offset = NULL;
  }
  function AddTable($s)
  {
    $this->tables[] = $s;
  }
  function AddField($s)
  {
    $this->fields[] = $s;
  }
  function AddJoin($type,$table,$condition)
  {
    $o = new stdClass();
    $o->type = $type;
    $o->table = $table;
    $o->condition = $condition;
    $this->joins[] = $o;
  }
  function AddWhere($s)
  {
    $this->conditions[] = "(".$s.")";
  }
  function AddOrder($s)
  {
    $this->orders[] = $s;
  }
  function AddGroup($s)
  {
    $this->groups[] = $s;
  }
  function SetLimit( $limit, $offset = NULL )
  {
    $this->limit = $limit;
    if ($offset !== NULL)
      $this->offset = $offset;
  }
  function GetQuery()
	{
    if (!count($this->tables))
      throw new Exception("[sqlselect] No tables specified!");

    $sql = "SELECT ";
    if ($this->fields) {
      $sql .= implode(", ",$this->fields);
    } else {
      $sql .= " * ";
    }
    $sql .= " FROM ".implode(", ",$this->tables);
    if ($this->joins) {
      foreach ($this->joins as $v) {
        $sql .= " ".$v->type." JOIN ".$v->table." ON ".$v->condition;
      }
    }
    if ($this->conditions)
      $sql .= " WHERE ".implode(" AND ",$this->conditions);
    if ($this->groups)
      $sql .= " GROUP BY ".implode(", ",$this->groups);
    if ($this->orders)
      $sql .= " ORDER BY ".implode(", ",$this->orders);
    if ($this->offset !== NULL)
    {
      $sql .= " LIMIT ".$this->offset . ", " . $this->limit;
    }
    else if ($this->limit !== NULL)
    {
      $sql .= " LIMIT ".$this->limit;
    }
    return $sql;
  }
}

function sprintf_esc()
{
  $args = func_get_args();
  reset($args);
  next($args);
  while (list($key, $value) = each($args))
    $args[$key] = mysqli_real_escape_string( SQLLib::$link, $args[$key] );

  return call_user_func_array("sprintf", $args);
}

function nop($s) { return $s; }
function clearArray($a)
{
  $ar = array();
  $qcb = get_magic_quotes_gpc() ? "stripslashes" : "nop";
  foreach ($a as $k=>$v)
    if (is_array($v))
      $ar[$k] = clearArray($v);
    else
      $ar[$k] = $qcb($v);
  return $ar;
}

$_POST = clearArray($_POST);
$_GET = clearArray($_GET);
$_REQUEST = clearArray($_REQUEST);
$SQLLIB_ARRAYS_CLEANED = true;
SQLLib::Connect();
?>