<?php
namespace Fandisus\Lolok;
class DB_PDO_PostgreSQL extends DBEngineAbs {
  public function connect($host, $dbname, $username, $password, $port) {
    parent::connect($host, $dbname, $username, $password, $port);
    if ($host === 'localhost' && PHP_OS !== 'Linux') $host = '127.0.0.1';
    try {
      //pgsql:host=localhost;port=5432;dbname=testdb;user=bruce;password=mypass
      $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
      $pdo = new \PDO($dsn, $username, $password, array(
              \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
              \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
            ));
    } catch (\Exception $ex) { throw $ex; }
    $this->conn = $pdo;
  }
  public function nq($string) { return str_replace("'", "''", str_replace("\\","\\\\",$string)); }
  public function insert($sql, $bindings, $sequenceName = FALSE) {
    try {
      $sth = $this->conn->prepare($sql);
      foreach ($bindings as $k=>$v) {
        $sth->bindParam($k,$bindings[$k]);
      }
      $sth->execute();
      
      if ($sequenceName) return $this->conn->lastInsertId($sequenceName);
      else return true;
    } catch (\Exception $ex) { throw $ex; }
  }

  public function tableExists($tableName, $schema = '') {
    if ($schema === '') $schema = 'public'; //Postgresql default schema = public
    return self::rowExists(
      'SELECT table_name FROM information_schema.tables WHERE table_schema=:SNAME AND table_name=:TNAME',
      ['SNAME'=>$schema, 'TNAME'=>$tableName]
    );
  }

  public function backup($dbname, $backupInfo) {
    putenv('PGPASSWORD='.$this->password);
    exec('pg_dump -U '.$this->username.' -p '.$this->port.' -d '.$dbname.' -c -O',$out, $ret);//pg_dump -U '.DB::$user.' -p '.DB::$port.' -d '.$dbname.' -c -O
    putenv('PGPASSWORD');
    if (!count($out)) die ('Database backup failed');
    $filesize = 0;
    array_unshift($out, $backupInfo);
//    foreach ($out as $v) $filesize += strlen($v);
//    $filesize += (count($out)) * strlen("\r\n");
    $out = gzencode(implode("\r\n", $out),5);
    $filesize = strlen($out);
    

    header("Content-Disposition: attachment; filename=\"".date('Ymd').".ssbin\"");
    header("Content-type: application/octet-stream");
    header("Content-Length: " .$filesize);
    header("Connection: close");
    
    //foreach ($out as $v) echo $v."\r\n";
    echo $out;
  }

  public function restore($dbname, $file, $appName) {
    $path = 'dbrestore.tmp';
    
    $isi = file_get_contents($file['tmp_name']);
    unlink($file['tmp_name']);
    $decoded = @gzdecode($isi);
    if (!$decoded) throw new \Exception('Fail to decode backup file');
    
    $restore = explode("\r\n",$decoded);
    $pop = array_shift($restore);
    $backupInfo = json_decode($pop);
    if ($backupInfo == null) throw new \Exception('Invalid backup file');
    
    if ($backupInfo->app != $appName) throw new \Exception('Invalid backup file version');
    if ($backupInfo->ver != 1) throw new \Exception('Invalid backup file version');
    
    $fh = fopen($path, 'w');
    fwrite($fh, implode("\r\n", $restore));
    fclose($fh);
    
    putenv('PGPASSWORD='.$this->password);
    $comm='psql -U '.$this->username.' -d '.$this->dbname.' -p '.$this->port. ' < "'.$path.'"';
    exec($comm, $out, $ret);
    putenv('PGPASSWORD');
    
    unlink($path);
  }
}
