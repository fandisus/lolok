<?php
namespace Fandisus\Lolok;
class DB_PDO_MySQL extends DBEngineAbs {
  public function connect($host, $dbname, $username, $password, $port) {
    parent::connect($host, $dbname, $username, $password, $port);
    if ($host === 'localhost' && PHP_OS !== 'Linux') $host = '127.0.0.1';
    try {
      //pgsql:host=localhost;port=5432;dbname=testdb;user=bruce;password=mypass
      //mysql:host=localhost;port=3307;dbname=testdb
      //oci:dbname=//localhost:1521/mydb
      $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
      $pdo = new \PDO($dsn, $username, $password, array(
              \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
              \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
            ));
    } catch (\Exception $ex) { throw $ex; }
    $this->conn = $pdo;
  }
  public function nq($string) { return str_replace("'", "\\'", str_replace("\\","\\\\",$string)); }
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

  public function tableExists($tableName, $schema = '') { return self::rowExists('SHOW TABLES LIKE :TNAME', ['TNAME'=>$tableName]); }

  public function backup($dbname, $backupInfo) { throw new \Exception('Backup has not been implemented for PDO MySQL'); }
  public function restore($dbname, $file, $appName) { throw new \Exception('Restore has not been implemented for PDO MySQL'); }
}
