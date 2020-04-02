<?php

class SQLLibException extends Exception 
{ 
  public function __construct($message = null, $code = 0, $query = "")
  {
    parent::__construct($message, $code);
    $this->query = $query;
  }
  public function __toString()
  {
    $e .= date("Y-m-d H:i:s");
    $e .= "\nError: ".$this->getMessage()."\n";
    $e .= "\nQuery: ".$this->query."\n";
    $e .= "\nTrace: ".$this->getTraceAsString();
    return $e;
  }
}

class SQLLib
{
  public static $debugMode = false;
  public static $arraysCleaned = false;
  public static $queries = array();

  protected static $link;
  protected static $charset = "";
  
  public static function Connect()
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

  public static function Disconnect()
  {
    mysqli_close(SQLLib::$link);
  }
  
  public static function Escape( $str )
  {
    return mysqli_real_escape_string(SQLLib::$link, $str);
  }
  
  public static function Query($cmd)
  {
    if (SQLLib::$debugMode)
    {
      $start = microtime(true);
      $r = @mysqli_query(SQLLib::$link,$cmd);
      if(!$r) throw new SQLLibException(mysqli_error(SQLLib::$link),0,$cmd);
      $end = microtime(true);
      SQLLib::$queries[$cmd] = $end - $start;
    }
    else
    {
      $r = @mysqli_query(SQLLib::$link,$cmd);
      if(!$r) throw new SQLLibException(mysqli_error(SQLLib::$link),0,$cmd);
      SQLLib::$queries[] = "*";
    }

    return $r;
  }

  public static function FormatQuery()
  {
    $args = func_get_args();
    for ($key = 1; $key < count($args); $key++) {
      $args[$key] = mysqli_real_escape_string(SQLLib::$link, $args[$key]);
    }
    return call_user_func_array("sprintf", $args);
  }

  public static function Fetch($r)
  {
    return mysqli_fetch_object($r);
  }

  public static function SelectRows($cmd)
  {
    $r = SQLLib::Query($cmd);
    $a = Array();
    while($o = SQLLib::Fetch($r)) $a[]=$o;
    return $a;
  }

  public static function SelectRow($cmd)
  {
    if (stristr($cmd,"select ")!==false && stristr($cmd," limit ")===false) // not exactly nice but it'll help
      $cmd .= " LIMIT 1";
    $r = SQLLib::Query($cmd);
    $a = SQLLib::Fetch($r);
    return $a;
  }

  public static function InsertRow($table,$o,$onDup = array())
  {
    if (!SQLLib::$arraysCleaned)
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
    if ($onDup)
    {
      $cmd .= " ON DUPLICATE KEY UPDATE ";
      $set = array();
      if ($onDup)
      {
        foreach($onDup as $k=>$v)
        {
          if ($v===NULL)
          {
            $set[] = sprintf("`%s`=null",mysqli_real_escape_string(SQLLib::$link,$k));
          }
          else if ($k[0]=="@")
          {
            $set[] = sprintf("`%s`=%s",mysqli_real_escape_string(SQLLib::$link,substr($k,1)),mysqli_real_escape_string(SQLLib::$link,$v));
          }
          else
          {
            $set[] = sprintf("`%s`='%s'",mysqli_real_escape_string(SQLLib::$link,$k),mysqli_real_escape_string(SQLLib::$link,$v));
          }
        }
      }
      else
      {
        $key = reset(array_keys($o));
        $set[] = $key . "=" . $key;
      }
      $cmd .= implode(", ",$set);
    }

    $r = SQLLib::Query($cmd);

    return mysqli_insert_id(SQLLib::$link);
  }

  public static function InsertMultiRow($table,$arr)
  {
    if (!SQLLib::$arraysCleaned)
      trigger_error("Arrays not cleaned before InsertMultiRow!",E_USER_ERROR);

    $keys = Array();
    $allValues = Array();
    foreach($arr as $o)
    {
      if (is_object($o)) $a = get_object_vars($o);
      else if (is_array($o)) $a = $o;
      $keys = Array();
      $values = Array();
      foreach($a as $k=>$v) {
        $keys[]="`".mysqli_real_escape_string(SQLLib::$link,$k)."`";
        if ($v!==NULL) $values[]="'".mysqli_real_escape_string(SQLLib::$link,$v)."'";
        else           $values[]="null";
      }
      $allValues[] = "(".implode(", ",$values).")";
    }

    $cmd = sprintf("insert %s (%s) values %s",
      $table,implode(", ",$keys),implode(", ",$allValues));
    $r = SQLLib::Query($cmd);
  }

  public static function UpdateRow($table,$o,$where)
  {
    if (!SQLLib::$arraysCleaned)
      trigger_error("Arrays not cleaned before UpdateRow!",E_USER_ERROR);

    if (is_object($o)) $a = get_object_vars($o);
    else if (is_array($o)) $a = $o;
    $set = Array();
    foreach($a as $k=>$v) {
      if ($v===NULL) {
        $set[] = sprintf("`%s`=null",mysqli_real_escape_string(SQLLib::$link,$k));
      } else {
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
  public static function UpdateRowMulti( $table, $key, $tuples )
  {
    if (!count($tuples))
      return;
    if (!is_array($tuples[0]))
      throw new Exception("Has to be array!");

    $fields = array_keys( $tuples[0] );

    $sql = "UPDATE ".$table;
    $keys = array();
    $cond = "";
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

  public static function UpdateOrInsertRow($table,$o,$where)
  {
    if (SQLLib::SelectRow(sprintf("SELECT * FROM %s WHERE %s",$table,$where)))
      return SQLLib::UpdateRow($table,$o,$where);
    else
      return SQLLib::InsertRow($table,$o);
  }

  public static function StartTransaction()
  {
    mysqli_autocommit(SQLLib::$link, FALSE);
  }
  public static function FinishTransaction()
  {
    mysqli_commit(SQLLib::$link);
    mysqli_autocommit(SQLLib::$link, TRUE);
  }
  public static function CancelTransaction()
  {
    mysqli_rollback(SQLLib::$link);
    mysqli_autocommit(SQLLib::$link, TRUE);
  }
}

class SQLTrans
{
  protected $rollback;
  
  public function __construct() {
    SQLLib::StartTransaction();
    $rollback = false;
  }
  public function Rollback() {
    $this->rollback = true;
  }
  public function __destruct() {
    if (!$rollback)
      SQLLib::FinishTransaction();
    else
      SQLLib::CancelTransaction();
  }
}

class SQLSelect
{
  protected $fields;
  protected $tables;
  protected $conditions;
  protected $joins;
  protected $orders;
  protected $groups;
  protected $limit;
  protected $offset;

  public function __construct()
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
  public function AddTable($s)
  {
    $this->tables[] = $s;
  }
  public function AddField($s)
  {
    $this->fields[] = $s;
  }
  public function AddJoin($type,$table,$condition)
  {
    $o = new stdClass();
    $o->type = $type;
    $o->table = $table;
    $o->condition = $condition;
    $this->joins[] = $o;
  }
  public function AddWhere($s)
  {
    $this->conditions[] = "(".$s.")";
  }
  public function AddOrder($s)
  {
    $this->orders[] = $s;
  }
  public function AddGroup($s)
  {
    $this->groups[] = $s;
  }
  public function SetLimit( $limit, $offset = NULL )
  {
    $this->limit = $limit;
    if ($offset !== NULL)
      $this->offset = $offset;
  }
  public function GetQuery()
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
  for ($key = 1; $key < count($args); $key++) {
    $args[$key] = SQLLib::Escape($args[$key]);
  }
  return call_user_func_array("sprintf", $args);
}

// DEPRECATED, ONLY FOR BACKWARDS COMPATIBILITY
function nop($s) { return $s; }
function clearArray($a)
{
  $ar = array();
  foreach ($a as $k=>$v)
    if (is_array($v))
      $ar[$k] = clearArray($v);
    else
      $ar[$k] = stripslashes($v);
  return $ar;
}

if (function_exists("get_magic_quotes_gpc") && version_compare(phpversion(), '7.0.0', '<'))
{
  $_POST = clearArray($_POST);
  $_GET = clearArray($_GET);
  $_REQUEST = clearArray($_REQUEST);
}

SQLLib::$arraysCleaned = true;
if (!defined("SQLLIB_SUPPRESSCONNECT"))
{
  SQLLib::Connect();
}
?>