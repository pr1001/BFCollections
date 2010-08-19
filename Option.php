<?php

/**
 * Option is the base class for Some and None.
 *
 * Options are used to indicate the presence or absence of a value. Return values do not need to be explicitly checked since iterators are available. This is inspired by Scala's Option. Ideally this would be an abstract class but PHP doesn't let an abstract class implement an interface.
 */
class Option implements Iterator {
    protected $v;
    private $times = 0;
    
    function __construct($v) {
        $this->v = $v;
    }
    
    function current() {
        return $this->v;
    }
    
    function key() {
        return 0;
    }
    
    function next() {
        $this->times++;
        return ($this->times < 1);
    }
    
    function rewind() {
        $this->times = 0;
    }
    
    function valid() {
        return ($this->times < 1);
    }
}

class Some extends Option {
    public function map($f) {
        $this->assertCallable($f);
        return new Some($f($this->v));
    }
    
    public function flatMap($f) {
        $this->assertCallable($f);
        $ret = $f($this->v);
        if (!($ret instanceof Option)) {
            throw new Exception("$f must return an Option.");
        }
        return $ret;
    }
    
    public function get() {
        return $this->v;
    }
    
    public function getOrElse($d) {
        return $this->get();
    }
    
    private function assertCallable($f) {
        if (!is_callable($f)) {
            throw new Exception("$f is not callbable");
        }
    }
}

class None extends Option {
    public function __construct() {
    }
    
    public function next() {
        return false;
    }
    
    public function valid() {
        return false;
    }
    
    public function map($f) {
        return $this;
    }
    
    public function flatMap($f) {
        return $this;
    }
    
    public function each($f) {
    }
    
    public function get() {
        throw new Exception('No such element.');
    }
    
    public function getOrElse($d) {
        return $d;
    }
}

$None = new None();

?>