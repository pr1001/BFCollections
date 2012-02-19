<?php
namespace com\bubblefoundry\collections;

class BFString extends BFArray {
  
  function __construct($str) {
    if ($str instanceof BFArray || is_array($str)) {
      $arr = $str;
    }
    else {
      $arr = array();
      for ($k = 0; $k < strlen($str); $k++) {
        $arr[] = substr($str, $k, 1);
      }
    }
    parent::__construct($arr);
  }
  
  function toString() {
    return $this->implode("");
  }
  
  function toUpperCase() {
    return new BFString($this->map(function ($c) { return ucfirst($c); })->toArray());
  }
  
  function toLowerCase() {
    return new BFString($this->map(function ($c) { return strtolower($c); })->toArray());
  }
  
  function replace($search, $replace) {
    return new BFString(str_replace($search, $replace, $this->toString()));
  }
  
  function substr($start, $length = NULL) {
    if (is_null($length)) {
      return new BFString(substr($this->toString(), $start));
    } else {
      return new BFString(substr($this->toString(), $start, $length));
    }
  }
  
  function position($needle, $offset = NULL) {
    return strpos($this->toString(), $needle, $offset);
  }
  
  function contains($needle) {
    return is_numeric($this->position($needle)) ? true : false;
  }
  
  function explode($sep, $limit = NULL) {
    return new BFArray(explode($sep, $this->toString(), $limit));
  }
}

?>