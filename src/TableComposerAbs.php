<?php
namespace Fandisus\Lolok;

abstract class TableComposerAbs {
  public $tableName;
  protected $lastCol;
  protected $columns=[];
  protected $constraints=[];
  protected $indexes=[];
  protected $comments=[];
  protected $sequences=[];

  protected function returner($colName) {
    $this->lastCol = $colName;
    return $this;
  }

  public function __construct($tableName) { $this->tableName = $tableName; }
  public abstract function increments($colName);
  public abstract function bigIncrements($colName);
  public abstract function string($colName, $length=0);
  public abstract function text($colName);
  public abstract function integer($colName);
  public abstract function bigInteger($colName);
  public abstract function double($colName);
  public abstract function numeric($colname, $precision, $scale);
  public abstract function bool($colName);
  public abstract function timestamp($colName);
  public abstract function date($colName);
  public abstract function time($colName);
  public abstract function jsonb($colName);
  public abstract function point($colName);

  
  public function notNull() { $this->columns[count($this->columns)-1] .= " NOT NULL"; return $this; } //Same for all
  public function unique($cols = '') { //Same for all
    if ($cols == "") $cols = $this->lastCol;
    $strCols = (is_array($cols)) ? implode(",",$cols) : $cols;
    $uq_name = (is_array($cols)) ? $cols[0] : str_replace(',','',str_replace(' ','', $cols));
    $this->constraints[] = "CONSTRAINT uq_$this->tableName"."_$uq_name UNIQUE ($strCols)";
    return $this;
  }
  public function index($cols = '') { //Same for all (Pgsql default is USING BTREE index)
    if ($cols == "") $cols = $this->lastCol;
    $strCols = (is_array($cols)) ? implode(",",$cols) : $cols;
    $idx_name = (is_array($cols)) ? $cols[0] : str_replace(',','',str_replace(' ','', $cols));
    $this->indexes[] = "CREATE INDEX idx_$idx_name"."_$this->tableName ON $this->tableName ($strCols);";
    return $this;
  }
  public function primary($cols="") { //Same for all
    if ($cols == "") $cols = $this->lastCol;
    $strCols = (is_array($cols)) ? implode(",",$cols) : $cols;
    $this->constraints[] = "CONSTRAINT pk_$this->tableName PRIMARY KEY ($strCols)";
    return $this;
  }
  public function foreign($ref,$refcol,$onupdate = "",$ondelete = "") { //Same for all
    $col = $this->lastCol;
    $onupdate = ($onupdate == "") ? " ON UPDATE CASCADE" : " ON UPDATE $onupdate";
    $ondelete = ($ondelete == "") ? " ON DELETE CASCADE" : " ON DELETE $ondelete";
    $this->constraints[] = "CONSTRAINT fk_$col"."_$this->tableName FOREIGN KEY ($col) REFERENCES $ref ($refcol)$onupdate$ondelete";
    return $this;
  }
  public function multiForeign($cols,$ref,$refcols,$onupdate,$ondelete) { //Same for all
    $onupdate = ($onupdate == "") ? "" : " ON UPDATE $onupdate";
    $ondelete = ($ondelete == "") ? "" : " ON DELETE $ondelete";
    $this->constraints[] = "CONSTRAINT fk_$ref"."_$this->tableName FOREIGN KEY ($cols) REFERENCES $ref ($refcols)$onupdate$ondelete";
    return $this;
  }
  public function comment() { //Same for all
    $args = func_get_args();
    if (count($args) == 1) {
      $col = $this->lastCol;
      $c = $args[0];
    } else {
      $col = $args[0];
      $c = $args[1];
    }
    $c = str_replace("'", "''", $c);
    $this->comments[] = "COMMENT ON COLUMN $this->tableName.$col IS '$c';";
    return $this;
  }
  
  public function parse() { //Same for all?
    $insides = array_merge($this->columns, $this->constraints);
    $strInsides = implode(",\n  ", $insides);
    $comment = "-- tabel $this->tableName --";
    $dropper = "DROP TABLE IF EXISTS $this->tableName CASCADE;";
    $creator = "CREATE TABLE $this->tableName (\n  $strInsides\n);";
    return array_merge( [$comment, $dropper, $creator], $this->indexes, $this->comments );
  }
}