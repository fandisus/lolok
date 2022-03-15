<?php
namespace Fandisus\Lolok;
class DB_PDO_Oracle extends DBEngineAbs {
  public function connect($host, $dbname, $username, $password, $port) {
    parent::connect($host, $dbname, $username, $password, $port);
    if ($host === 'localhost' && PHP_OS !== 'Linux') $host = '127.0.0.1';
    try {
      //oci:dbname=//localhost:1521/mydb
      $dsn = "oci:dbname=//$host:$port/$dbname";
      $pdo = new \PDO($dsn, $username, $password, array(
              \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
              \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
            ));
    } catch (\Exception $ex) { throw $ex; }
    $this->conn = $pdo;
  }
  public function nq($string) { return str_replace("'", "''", $string); }
  public function insert($sql, $bindings, $sequenceName = FALSE) {
    try {
      $sth = $this->conn->prepare($sql);
      foreach ($bindings as $k=>$v) {
        $sth->bindParam($k,$bindings[$k]);
      }
      $sth->execute();
      
      if ($sequenceName) return $this->conn->lastInsertId();
      else return true;
    } catch (\Exception $ex) { throw $ex; }
  }

  public function selectLimit($query, $offset, $len, $params=[]) {
    $end = $offset + $len;
    $query = "SELECT * FROM (SELECT t.*,rownum as recordnum from ($query) t where rownum<=$end) where recordnum>$offset";
    return $this->get($query, $params);
  }

  public function tableExists($tableName, $schema = '') {
    if ($schema === '') $schema = $this->username; //In oracle, default Schema = Username
    return self::rowExists(
      "SELECT * FROM ALL_ALL_TABLES WHERE OWNER=UPPER(:SNAME) AND TABLE_NAME=UPPER(:TNAME)",
      ['SNAME'=>$schema, 'TNAME'=>$tableName]
    );
  }

  public function backup($dbname, $backupInfo) { throw new \Exception('Backup has not been implemented for PDO Oracle'); }
  public function restore($dbname, $file, $appName) { throw new \Exception('Restore has not been implemented for PDO Oracle'); }
}

