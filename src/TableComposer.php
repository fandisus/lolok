<?php
namespace Fandisus\Lolok;

use Exception;

class TableComposer {
  protected $adapter;

  public function __construct($tableName) {
    if (DB::$engine === 'pgsql') $this->adapter = new TableComposerPg($tableName);
    elseif (DB::$engine === 'mysql') $this->adapter = new TableComposerMy($tableName);
    elseif (DB::$engine === 'ora') $this->adapter = new TableComposerOra($tableName);
  }
  public function increments($colName) { $this->adapter->increments($colName); return $this; }
  public function bigIncrements($colName) { $this->adapter->bigIncrements($colName); return $this; }
  public function string($colName, $length=50) { $this->adapter->string($colName, $length); return $this; }
  public function text($colName) { $this->adapter->string($colName); return $this; }
  public function integer($colName) { $this->adapter->integer($colName); return $this; }
  public function bigInteger($colName) { $this->adapter->bigInteger($colName); return $this; }
  public function double($colName) { $this->adapter->double($colName); return $this; }
  public function numeric($colname, $precision, $scale) {  $this->adapter->numeric($colname, $precision, $scale); return $this; }
  public function bool($colName) { $this->adapter->bool($colName); return $this; }
  public function timestamp($colName) { $this->adapter->timestamp($colName); return $this; }
  public function date($colName) { $this->adapter->date($colName); return $this; }
  public function time($colName) { $this->adapter->time($colName); return $this; }
  public function jsonb($colName) { $this->adapter->jsonb($colName); return $this; }
  public function point($colName) { $this->adapter->point($colName); return $this; }
  
  public function notNull() { $this->adapter->notNull(); return $this; }
  public function unique($cols = '') { $this->adapter->unique($cols); return $this; }
  public function index($cols = '') { $this->adapter->index($cols); return $this; }
  public function ginPropIndex($props) {
    if (!$this->adapter instanceof TableComposerPg) throw new Exception('ginPropIndex is only supported for Postgres');
    $this->adapter->ginPropIndex($props); return $this; 
  }
  public function ginIndex() {
    if (!$this->adapter instanceof TableComposerPg) throw new Exception('ginIndex is only supported for Postgres');
    $this->adapter->ginIndex(); return $this; 
  }
  public function mysqlJsonIndex($props) {
    if (!$this->adapter instanceof TableComposerMy) throw new Exception('mysqlJsonIndex is only supported for MySQL');
    $this->adapter->jsonIndex($props); return $this; 
  }
  public function primary($cols="") { $this->adapter->primary($cols); return $this;}
  public function foreign($ref,$refcol,$onupdate = "",$ondelete = "") {
    $this->adapter->foreign($ref, $refcol, $onupdate, $ondelete);
    return $this; 
  }
  public function multiForeign($cols,$ref,$refcols,$onupdate,$ondelete) {
    $this->adapter->multiForeign($cols, $ref, $refcols, $onupdate, $ondelete);
    return $this; 
  }
  public function comment() { $this->adapter->comment(); return $this; }
  
  public function parse() { return $this->adapter->parse(); }
  public function execute() { $this->adapter->execute(); }
}
