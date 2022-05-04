<?php
namespace Fandisus\Lolok;
class TableComposerMy extends TableComposerAbs {

  public function increments($colName) { $this->columns[] = "$colName INT AUTO_INCREMENT"; $this->lastCol = $colName; }
  public function bigIncrements($colName) { $this->columns[] = "$colName BIGINT AUTO_INCREMENT"; $this->lastCol = $colName; }
  public function string($colName, $length=50) { $this->columns[] = "$colName VARCHAR($length)"; $this->lastCol = $colName; }
  public function text($colName) { $this->columns[] = "$colName TEXT"; $this->lastCol = $colName; } //Same for all
  public function integer($colName) { $this->columns[] = "$colName INT"; $this->lastCol = $colName; }
  public function bigInteger($colName) { $this->columns[] = "$colName BIGINT"; $this->lastCol = $colName; } //Same with PgSQL
  public function double($colName) { $this->columns[] = "$colName DOUBLE"; $this->lastCol = $colName; }
  public function numeric($colName, $precision, $scale) { $this->columns[] = "$colName NUMERIC ($precision, $scale)"; $this->lastCol = $colName; } //Same for all
  public function bool($colName) { $this->columns[] = "$colName BOOL"; $this->lastCol = $colName; } //Same for all
  public function timestamp($colName) { $this->columns[] = "$colName DATETIME"; $this->lastCol = $colName; }
  public function date($colName) { $this->columns[] = "$colName DATE"; $this->lastCol = $colName; } //Same for all
  public function time($colName) { $this->columns[] = "$colName TIME"; $this->lastCol = $colName; } //Same for all
  public function jsonb($colName) { $this->columns[] = "$colName JSON"; $this->lastCol = $colName; }
  public function point($colName) { $this->columns[] = "$colName POINT"; $this->lastCol = $colName; } //Same for all

  public function jsonIndex($props) {
    $col = $this->lastCol;
    //$props format: [['name'=>'name','path'=>'$.location.name','type'=>'INT/VARCHAR(45)']]
    foreach ($props as $v) {
      $this->columns[] = "{$this->lastCol}_{$v['name']} {$v['type']} AS ($this->lastCol->>\"$v[path]\")";
//      $this->indexes[] = "ALTER TABLE $this->tableName ADD {$this->lastCol}_{$v['name']} $v[type] "
//              . "AS ($this->lastCol->>\"$v[path]\")";
      $this->indexes[] = "CREATE INDEX idx_{$this->lastCol}_{$v['name']}_{$this->tableName} ON $this->tableName ({$this->lastCol}_{$v['name']});";
    }
    return $this;
  }
}
