<?php
namespace Fandisus\Lolok;
class TableComposerPg extends TableComposerAbs {  
  public function increments($colName) { $this->columns[] = "$colName SERIAL"; $this->lastCol = $colName; }
  public function bigIncrements($colName) { $this->columns[] = "$colName BIGSERIAL"; $this->lastCol = $colName; }
  public function string($colName, $length=50) { $this->columns[] = "$colName CHARACTER VARYING($length)"; $this->lastCol = $colName; }
  public function text($colName) { $this->columns[] = "$colName TEXT"; $this->lastCol = $colName; } //Same for all
  public function integer($colName) { $this->columns[] = "$colName INTEGER"; $this->lastCol = $colName; }
  public function bigInteger($colName) { $this->columns[] = "$colName BIGINT"; $this->lastCol = $colName; } //Same with MySQL
  public function double($colName) { $this->columns[] = "$colName DOUBLE PRECISION"; $this->lastCol = $colName; }
  public function numeric($colName, $precision, $scale) { $this->columns[] = "$colName NUMERIC ($precision, $scale)"; $this->lastCol = $colName; } //Same for all
  public function bool($colName) { $this->columns[] = "$colName BOOL"; $this->lastCol = $colName; } //Same for all
  public function timestamp($colName) { $this->columns[] = "$colName TIMESTAMP"; $this->lastCol = $colName; }
  public function date($colName) { $this->columns[] = "$colName DATE"; $this->lastCol = $colName; } //Same for all
  public function time($colName) { $this->columns[] = "$colName TIME"; $this->lastCol = $colName; } //Same for all
  public function jsonb($colName) { $this->columns[] = "$colName JSONB"; $this->lastCol = $colName; }
  public function point($colName) { $this->columns[] = "$colName POINT"; $this->lastCol = $colName; } //Same for all


  public function ginPropIndex($props) {
    $col = $this->lastCol;
    if (!is_array($props)) $props = [$props];
    foreach ($props as $v) {
      $this->indexes[] = "CREATE INDEX idx_$v"."_$col"."_$this->tableName ON $this->tableName USING GIN (($col"."->'$v'));";
    }
  }
  public function ginIndex() {
    $col = $this->lastCol;
    $this->indexes[] = "CREATE INDEX idx_$col"."_$this->tableName ON $this->tableName USING GIN ($col);";
  }
}
