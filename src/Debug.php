<?php
namespace Fandisus\Lolok;
class Debug {
  public static function print_r($arrObj) {
    echo "<pre>".print_r($arrObj,true)."</pre>";
  }
}
