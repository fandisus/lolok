<?php
//Already updated to all (2019-01-15). If any problem, might be caused by below.
//static::$host, static::setHost, init($force = false) *sebelumnya $force=true belum diupdate ke ICFM. takut karena ubah method init.
namespace Fandisus\Lolok;
class DB {
  public static $adapter;
  public static $engine = 'pgsql';
  public static function setConnection(string $engine, string $host, string $dbname, string $username, string $password, int $port) {
    if ($engine === 'mysql') self::$adapter = new DB_PDO_MySQL();
    elseif ($engine === 'pgsql') self::$adapter = new DB_PDO_PostgreSQL();
    elseif ($engine === 'oracle') self::$adapter = new DB_PDO_Oracle();
    elseif ($engine === 'oci') self::$adapter = new DB_Oracle();
    else throw new \Exception('Unknown DB engine');
    self::$engine = $engine;
    self::$adapter->connect($host, $dbname, $username, $password, $port);
  }
  public static function setConnectionWithObject($obj){
    self::setConnection($obj->engine, $obj->host, $obj->dbname, $obj->username, $obj->password, $obj->port);
  }
  public static function nq($string) { return self::$adapter->nq($string); }
  public static function nqq($string) { return self::$adapter->nqq($string); }
  public static function exec($sql, $bindings) { return self::$adapter->exec($sql, $bindings); }
  //Todo: tambah method update, insertMulti dan updateMulti
  public static function insert($sql, $bindings, $sequenceName=FALSE) { self::$adapter->insert($sql, $bindings, $sequenceName); }
  public static function getOneVal($sql,$bindings=[]) { return self::$adapter->getOneVal($sql, $bindings); }
  public static function rowExists($sql,$bindings=[]) { return self::$adapter->rowExists($sql, $bindings); }
  public static function get($sql, $bindings=[]) { return self::$adapter->get($sql, $bindings); }
  public static function selectLimit($query, $offset, $len, $params=array()) { return self::$adapter->selectLimit($query, $offset, $len, $params); }
  public static function tableExists($tableName, $schema='') { return self::$adapter->tableExists($tableName, $schema); }
  public static function getOneRow($sql, $bindings=[]) { return self::$adapter->getOneRow($sql, $bindings); }
  public static function transExecute($sqls, $bindings=[]) { return self::$adapter->transExecute($sqls, $bindings); }
  //Backup and restore might need to be done later
  public static function backup($dbname, $backupInfo) { self::$adapter->backup($dbname, $backupInfo); }
  public static function restore($dbname, $file, $appName) { self::$adapter->restore($dbname, $file, $appName); }
}
