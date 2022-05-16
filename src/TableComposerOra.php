<?php
namespace Fandisus\Lolok;
//Note: AUTO_INCREMENT is not handled here. You must create SEQUENCES manually after this.
class TableComposerOra extends TableComposerAbs {
  public function increments($colName) { $this->columns[] = "$colName INT"; $this->lastCol = $colName; }
  public function bigIncrements($colName) { $this->columns[] = "$colName NUMBER(19)"; $this->lastCol = $colName; }
  public function string($colName, $length=50) { $this->columns[] = "$colName VARCHAR($length)"; $this->lastCol = $colName; }
  public function text($colName) { $this->columns[] = "$colName VARCHAR2(4000)"; $this->lastCol = $colName; } //Same for all
  public function integer($colName) { $this->columns[] = "$colName INT"; $this->lastCol = $colName; }
  public function bigInteger($colName) { $this->columns[] = "$colName NUMBER(19)"; $this->lastCol = $colName; } //Same with PgSQL
  public function double($colName) { $this->columns[] = "$colName BINARY_DOUBLE"; $this->lastCol = $colName; }
  public function numeric($colName, $precision, $scale) { $this->columns[] = "$colName NUMBER($precision, $scale)"; $this->lastCol = $colName; } //Same for all
  public function bool($colName) { $this->columns[] = "$colName CHAR(1)"; $this->lastCol = $colName; } //Same for all
  public function timestamp($colName) { $this->columns[] = "$colName TIMESTAMP"; $this->lastCol = $colName; }
  public function timestampTz($colName) { throw new \Exception('Oracle db adapter does not support TimestampTz yet'); $this->lastCol = $colName; }
  public function date($colName) { $this->columns[] = "$colName DATE"; $this->lastCol = $colName; } //Same for all
  public function time($colName) { $this->columns[] = "$colName TIMESTAMP"; $this->lastCol = $colName; } //Same for all
  public function jsonb($colName) {
    $this->columns[] = "$colName VARCHAR2(32767)"; $this->lastCol = $colName;
    //Below for version above 11g
    // $this->columns[] = "$colName VARCHAR2(32767) CONSTRAINT ensure_json CHECK ($colName IS JSON))"; $this->lastCol = $colName;
  }
  public function point($colName) { throw new \Exception('POINT data type not supported in ORACLE'); } //Same for all
}
