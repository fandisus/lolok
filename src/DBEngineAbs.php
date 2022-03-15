<?php
//Already updated to all (2019-01-15). If any problem, might be caused by below.
//static::$host, static::setHost, init($force = false) *sebelumnya $force=true belum diupdate ke ICFM. takut karena ubah method init.
namespace Fandisus\Lolok;
abstract class DBEngineAbs {
  public $dbname, $username, $password, $port;
  public $appName;
  public $conn;
  public function connect($host, $dbname, $username, $password, $port) {
    list($this->dbname, $this->username, $this->password, $this->port) = [$dbname, $username, $password, $port];
  }
  public abstract function nq($string);
  public function nqq($string) { return "'".static::nq($string)."'"; }
  public function exec($sql, $bindings) {
    try {
      $sth = $this->conn->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      return $sth->rowCount();
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  //Todo: tambah method update, insertMulti dan updateMulti
  public abstract function insert($sql, $bindings, $sequenceName=FALSE);
  public function getOneVal($sql,$bindings=[]) {
    try {
      $sth = $this->conn->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      $baris = $sth->fetch(\PDO::FETCH_NUM);
      if (!$baris) return null;
      return $baris[0];
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public function rowExists($sql,$bindings=[]) {
    try {
      $sth = $this->conn->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      $baris = $sth->fetch(\PDO::FETCH_NUM);
      if (!$baris) return false;
      return true;
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public function get($sql, $bindings=[]) {
    try {
      $sth = $this->conn->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      return $sth->fetchAll();
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public function selectLimit($query, $offset, $len, $params=array()) { throw new \Exception('DB selectLimit not implemented for MySQL and PostgreSQL yet'); }
  public abstract function tableExists($tableName, $schema='');

  public function getOneRow($sql, $bindings=[]) {
    try {
      /* @var $sth \PDOStatement */
      $sth = $this->conn->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      $result = $sth->fetch();
      if ($result === false) return null;
      else return $result;
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public function transExecute($sqls, $bindings=[]) { //$bindings is 2 level nested array
    try {
      $this->conn->beginTransaction();
      foreach ($sqls as $k=>$v) {
        $sth = $this->conn->prepare($sqls[$k]);
        if (count($bindings)) foreach ($bindings[$k] as $k2=>$v2) $sth->bindParam($k2,$v2);
        $sth->execute();
      }
      $this->conn->commit();
    } catch (\Exception $ex) {
      $this->conn->rollBack();
      throw $ex;
    }
  }
  public abstract function backup($dbname, $backupInfo);
  public abstract function restore($dbname, $file, $appName);
}
