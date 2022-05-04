<?php
namespace Fandisus\Lolok;

use Exception;

class TableComposer {
  public $tableName;
  protected TableComposerAbs $adapter;

  protected function returner($colName) {
    $this->lastCol = $colName;
    return $this;
  }
  public function __construct($tableName) {
    if (DB::$engine === 'pgsql') $this->adapter = new TableComposerPg($tableName);
    elseif (DB::$engine === 'mysql') $this->adapter = new TableComposerMy($tableName);
    elseif (DB::$engine === 'ora') $this->adapter = new TableComposerOra($tableName);
  }
  public function increments($colName) { return $this->adapter->increments($colName); }
  public function bigIncrements($colName) { return $this->adapter->bigIncrements($colName); }
  public function string($colName, $length=50) { return $this->adapter->string($colName, $length); }
  public function text($colName) { return $this->adapter->string($colName); }
  public function integer($colName) { return $this->adapter->integer($colName); }
  public function bigInteger($colName) { return $this->adapter->bigInteger($colName); }
  public function double($colName) { return $this->adapter->double($colName); }
  public function numeric($colname, $precision, $scale) { return $this->adapter->numeric($colname, $precision, $scale); }
  public function bool($colName) { return $this->adapter->bool($colName); }
  public function timestamp($colName) { return $this->adapter->timestamp($colName); }
  public function date($colName) { return $this->adapter->date($colName); }
  public function time($colName) { return $this->adapter->time($colName); }
  public function jsonb($colName) { return $this->adapter->jsonb($colName); }
  public function point($colName) { return $this->adapter->point($colName); }
  
  public function notNull() { return $this->adapter->notNull(); }
  public function unique($cols = '') { return $this->adapter->unique($cols); }
  public function index() { return $this->adapter->index(); }
  public function ginPropIndex($props) {
    if (!$this->adapter instanceof TableComposerPg) throw new Exception('ginPropIndex is only supported for Postgres');
    return $this->adapter->ginPropIndex($props);
  }
  public function ginIndex() {
    if (!$this->adapter instanceof TableComposerPg) throw new Exception('ginIndex is only supported for Postgres');
    return $this->adapter->ginIndex();
  }
  public function mysqlJsonIndex($props) {
    if (!$this->adapter instanceof TableComposerMy) throw new Exception('mysqlJsonIndex is only supported for MySQL');
    return $this->adapter->jsonIndex($props);
  }
  public function primary($cols="") { return $this->adapter->primary($cols); }
  public function foreign($ref,$refcol,$onupdate = "",$ondelete = "") {
    return $this->adapter->foreign($ref, $refcol, $onupdate, $ondelete);
  }
  public function multiForeign($cols,$ref,$refcols,$onupdate,$ondelete) {
    return $this->adapter->multiForeign($cols, $ref, $refcols, $onupdate, $ondelete);
  }
  public function comment() { return $this->adapter->comment(); }
  
  public function parse() { return $this->adapter->parse(); }
}
