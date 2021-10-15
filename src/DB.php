<?php
//Already updated to all (2019-01-15). If any problem, might be caused by below.
//static::$host, static::setHost, init($force = false) *sebelumnya $force=true belum diupdate ke ICFM. takut karena ubah method init.
namespace Fandisus\Lolok;
class DB {
  private static $initialized = false;
  public static $appName;
  public static $pdo;
  public static $driver = 'pgsql';
  public static $host = 'localhost';
  public static $fetch_mode = \PDO::FETCH_OBJ;
  public static $db, $user, $pass, $port;
  public static function setHost($host) { static::$host = $host; }
  public static function setDriver($driver) { static::$driver = $driver; }
  public static function setFetchMode($mode) { static::$fetch_mode = $mode; }
  public static function setConnection($db, $user, $pass, $port) {
    list(static::$db,static::$user,static::$pass,static::$port) = [$db,$user,$pass,$port];
    static::$initialized = false;
  }
  public static function setConnectionWithObject($obj){
    list(static::$host, static::$driver, static::$db,static::$user,static::$pass,static::$port) =
        [$obj->host, $obj->driver, $obj->dbname, $obj->username, $obj->password, $obj->port];
    static::$initialized=false;
  }
  public static function nq($string) {
    if (static::$driver === 'pgsql') return str_replace("'", "''", str_replace("\\","\\\\",$string));
    elseif (static::$driver === 'oracle') return str_replace("'", "''", $string);
    return str_replace("'", "\\'", str_replace("\\","\\\\",$string));
  }
  public static function nqq($string) {
    return "'".static::nq($string)."'";
  }
  public static function init($force = false) {
    if (!$force && static::$initialized) return;
    if (static::$host === 'localhost' && PHP_OS !== 'Linux') static::$host = '127.0.0.1';
    $host = static::$host;
    $driver = static::$driver;
    $port = static::$port;
    $db = static::$db;
    try {
      //pgsql:host=localhost;port=5432;dbname=testdb;user=bruce;password=mypass
      //mysql:host=localhost;port=3307;dbname=testdb
      //oci:dbname=//localhost:1521/mydb
      $dsn = "$driver:host=$host;port=$port;dbname=$db;";
      if (self::$driver === 'mysql') $dsn.='charset=utf8;';
      elseif (self::$driver === 'oracle') $dsn = "oci:dbname=//$host:$port/$db";
      $pdo = new \PDO($dsn, static::$user, static::$pass, array(
              \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
              \PDO::ATTR_DEFAULT_FETCH_MODE => static::$fetch_mode
            ));
    } catch (\Exception $ex) { throw $ex; }
    DB::$pdo = $pdo;
    static::$initialized = true;
  }
  public static function exec($sql, $bindings) {
    static::init();
    try {
      $sth = static::$pdo->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      return $sth->rowCount();
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  //Todo: tambah method update, insertMulti dan updateMulti
  public static function insert($sql, $bindings, $sequenceName=FALSE) {
    static::init();
    try {
      $sth = static::$pdo->prepare($sql);
      foreach ($bindings as $k=>$v) {
        $sth->bindParam($k,$bindings[$k]);
      }
      $sth->execute();
      if ($sequenceName) {
        if (in_array(static::$driver, ['mysql', 'oracle'])) return static::$pdo->lastInsertId();
        elseif (static::$driver === 'pgsql') return static::$pdo->lastInsertId($sequenceName);
      }
      return true;
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public static function getOneVal($sql,$bindings=[]) {
    static::init();
    try {
      $sth = static::$pdo->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      $baris = $sth->fetch(\PDO::FETCH_NUM);
      if (!$baris) return null;
      return $baris[0];
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public static function rowExists($sql,$bindings=[]) {
    static::init();
    try {
      $sth = static::$pdo->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      $baris = $sth->fetch(\PDO::FETCH_NUM);
      if (!$baris) return false;
      return true;
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public static function get($sql, $bindings=[]) {
    static::init();
    try {
      $sth = static::$pdo->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      return $sth->fetchAll();
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public static function oraSelectCount($query, $params=array()) {
    $query = "SELECT count(*) jum FROM ($query)";
    return self::getOneVal($query, $params);
  }
  public static function oraSelectLimit($query, $offset, $len, $params=array()) {
    $end = $offset + $len;
    $query = "SELECT * FROM (SELECT t.*,rownum as recordnum from ($query) t where rownum<=$end) where recordnum>$offset";
    return self::get($query, $params);
  }
  public static function tableExists($tableName, $schema='') {
    if (self::$driver === 'mysql') { return self::rowExists('SHOW TABLES LIKE :TNAME', ['TNAME'=>$tableName]); }
    elseif (self::$driver === 'pgsql') { 
      if ($schema === '') $schema = 'public'; //Postgresql default schema = public
      return self::rowExists(
        'SELECT table_name FROM information_schema.tables WHERE table_schema=:SNAME AND table_name=:TNAME',
        ['SNAME'=>$schema, 'TNAME'=>$tableName]
      );
    }
    elseif (self::$driver === 'oracle') {
      if ($schema === '') $schema = self::$user; //In oracle, default Schema = Username
      return self::rowExists(
        "SELECT * FROM ALL_ALL_TABLES WHERE OWNER=UPPER(:SNAME) AND TABLE_NAME=UPPER(:TNAME)",
        ['SNAME'=>$schema, 'TNAME'=>$tableName]
      );
    }
  }

  public static function getOneRow($sql, $bindings=[]) {
    static::init();
    try {
      /* @var $sth \PDOStatement */
      $sth = static::$pdo->prepare($sql);
      foreach ($bindings as $k=>$v) $sth->bindParam($k,$bindings[$k]);
      $sth->execute();
      $result = $sth->fetch();
      if ($result === false) return null;
      else return $result;
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public static function transExecute($sqls, $bindings=[]) { //$bindings is 2 level nested array
    static::init();
    try {
      static::$pdo->beginTransaction();
      foreach ($sqls as $k=>$v) {
        $sth = static::$pdo->prepare($sqls[$k]);
        if (count($bindings)) foreach ($bindings[$k] as $k2=>$v2) $sth->bindParam($k2,$v2);
        $sth->execute();
      }
      static::$pdo->commit();
    } catch (\Exception $ex) {
      static::$pdo->rollBack();
      throw $ex;
    }
  }
  public static function pgRelocateId($tableName) { //Kolom PK dan AI harus namonyo: id
    static::init();try{
      $sql = "SELECT id FROM $tableName ORDER BY id";
      $sth = static::$pdo->prepare($sql);
      $sth->execute();
      //Panggil galo galo id: mis: 1, 2, 3, 6, 10, 11, 12;
      $ids = $sth->fetchAll();
      $ids = array_map(function($o) {return $o->id;}, $ids);
      
      $sqls = [];
      $jumlahRow = count($ids);
      for ($i=$jumlahRow-1; $i>0; $i--) {
        $diff = $ids[$i]-$ids[$i-1] - 1;
        if ($diff != 0) {
          $sqls[] = "UPDATE $tableName SET id=id-$diff WHERE id>=".$ids[$i];
//          for($j=$i; $j<$jumlahRow; $j++) $ids[$j] -= $diff;  
        }
      }
      if ($ids[0] > 1) {
        $diff = $ids[0]-1;
        $sqls[] = "UPDATE $tableName SET id=id-$diff";
//        foreach ($ids as $k=>$v) $ids[$k] -= $diff;
      }
      $jumlahRow++; //Set sequence baru
      $sqls[] = "ALTER SEQUENCE $tableName"."_id_seq RESTART WITH $jumlahRow";
      static::transExecute($sqls);
    } catch (\Exception $ex) {
      throw $ex;
    }
  }
  public static function pgBackup($dbname, $backupInfo) {
    static::$db = $dbname;
    try { static::init(true); }
    catch (\Exception $ex) { throw new \Exception('Failed to connect to database',0,$ex); }

    putenv('PGPASSWORD='.DB::$pass);
    exec('pg_dump -U '.DB::$user.' -p '.DB::$port.' -d '.$dbname.' -c -O',$out, $ret);//pg_dump -U '.DB::$user.' -p '.DB::$port.' -d '.$dbname.' -c -O
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
  public static function pgRestore($dbname, $file) {
    if (!isset(self::$appName)) throw new \Exception('App name is not set');
    $path = 'dbrestore.tmp';
    
    $isi = file_get_contents($file['tmp_name']);
    unlink($file['tmp_name']);
    $decoded = @gzdecode($isi);
    if (!$decoded) throw new \Exception('Fail to decode backup file');
    
    $restore = explode("\r\n",$decoded);
    $pop = array_shift($restore);
    $backupInfo = json_decode($pop);
    if ($backupInfo == null) throw new \Exception('Invalid backup file');
    
    if ($backupInfo->app != self::$appName) throw new \Exception('Invalid backup file version');
    if ($backupInfo->ver != 1) throw new \Exception('Invalid backup file version');
    
    $fh = fopen($path, 'w');
    fwrite($fh, implode("\r\n", $restore));
    fclose($fh);
    
    putenv('PGPASSWORD='.DB::$pass);
    $comm='psql -U '.DB::$user.' -d '.DB::$db.' -p '.DB::$port. ' < "'.$path.'"';
    exec($comm, $out, $ret);
    putenv('PGPASSWORD');
    
    unlink($path);
  }
}
