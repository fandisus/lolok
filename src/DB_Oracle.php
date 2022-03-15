<?php
namespace Fandisus\Lolok;
class DB_Oracle extends DBEngineAbs {
  public function connect($host, $dbname, $username, $password, $port) {
    parent::connect($host, $dbname, $username, $password, $port);
    if ($host === 'localhost' && PHP_OS !== 'Linux') $host = '127.0.0.1';
    try {
      $conn = oci_connect($username, $password, "$host/$dbname");
    } catch (\Exception $ex) { throw $ex; }
    $this->conn = $conn;
  }
  public function nq($string) { return str_replace("'", "''", $string); }
  
  public function insert($sql, $bindings, $sequenceName = FALSE) { throw new \Exception('DB_Oracle insert not implemented yet'); }

  public function exec($sql, $bindings) {
    $stid = oci_parse($this->conn, $sql);
    if (!$stid) throw new \Exception(json_encode(oci_error($this->conn)));
    foreach ($bindings as $k=>&$v) oci_bind_by_name($stid, $k, $v);
    @$res = oci_execute($stid);
    if (!$res) throw new \Exception(json_encode(print_r(oci_error($stid), true)));
  }

  public function getOneVal($sql, $bindings = []) {
    $stid = oci_parse($this->conn, $sql);
    if (!$stid) throw new \Exception(json_encode(oci_error($this->conn)));
    foreach ($bindings as $k=>&$v) oci_bind_by_name($stid, $k, $v);
    oci_execute($stid);
    if ($row = oci_fetch_row($stid)) return $row[0];
    return null;
  }

  public function rowExists($sql, $bindings = []) {
    $stid = oci_parse($this->conn, $sql);
    if (!$stid) throw new \Exception(json_encode(oci_error($this->conn)));
    foreach ($bindings as $k=>&$v) oci_bind_by_name($stid, $k, $v);
    oci_execute($stid);
    if (oci_fetch_row($stid)) return true;
    return false;
  }

  public function get($sql, $bindings=[]) {
    $stid = oci_parse($this->conn, $sql);
    if (!$stid) throw new \Exception(json_encode(oci_error($this->conn)));
    foreach ($bindings as $k=>&$v) oci_bind_by_name($stid, $k, $v);
    oci_execute($stid);
    $result = array();
    while ($row = oci_fetch_assoc($stid)) $result[] = $row;
    return $result;
  }

  public function selectLimit($query, $offset, $len, $params=[]) {
    $end = $offset + $len;
    $query = "SELECT * FROM (SELECT t.*,rownum as recordnum from ($query) t where rownum<=$end) where recordnum>$offset";
    return $this->get($query, $params);
  }

  public function tableExists($tableName, $schema = '') {
    if ($schema === '') $schema = $this->user; //In oracle, default Schema = Username
    return self::rowExists(
      "SELECT * FROM ALL_ALL_TABLES WHERE OWNER=UPPER(:SNAME) AND TABLE_NAME=UPPER(:TNAME)",
      ['SNAME'=>$schema, 'TNAME'=>$tableName]
    );
  }

  public function getOneRow($sql, $bindings = []) {
    $stid = oci_parse($this->conn, $sql);
    if (!$stid) throw new \Exception(json_encode(oci_error($this->conn)));
    foreach ($bindings as $k=>&$v) oci_bind_by_name($stid, $k, $v);
    @$res = oci_execute($stid);
    if (!$res) throw new \Exception(json_encode(print_r(oci_error($stid), true)));
    if ($row = oci_fetch_assoc($stid)) return $row;
    return null;
  }

  public function transExecute($sqls, $bindings = []) { throw new \Exception('DB_Oracle transExecute has not been implemented yet.'); }

  public function backup($dbname, $backupInfo) { throw new \Exception('Backup has not been implemented for Oracle DB'); }
  public function restore($dbname, $file, $appName) { throw new \Exception('Restore has not been implemented for Oracle DB'); }
}