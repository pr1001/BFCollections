<?php

/**
 * Box is the base class for Full, Empty, and Failure.
 *
 * Boxs are used to indicate the presence or absence of a value, with the possibility to indicate the reason for its absence. Return values do not need to be explicitly checked since iterators are available. This is inspired by Lift's Box and Scala's Option. Ideally this would be an abstract class but PHP doesn't let an abstract class implement an interface.
 */
class Box implements Iterator {
    protected $value;
    private $times = 0;
    
    function __construct($value) {
        $this->value = $value;
    }
    
    function current() {
        return $this->value;
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
    
    function isEmpty() {
        return !isset($this->value);
    }
    
    function isDefined() {
        return !$this->isEmpty();
    }
    
    function map($f) {
        return $this;
    }
    
    function flatMap($f) {
        return $this;
    }
    
    function each($f) {
    }
    
    function open() {
        throw new Exception('No such element.');
    }
    
    function openOr($d) {
        return $d;
    }
    
    function orElse($d) {
        $this->assertBox($d);
        return $this;
    }
    
    function filter($f) {
        $this->assertCallable($f);
        return $this;
    }
    
    function exists($f) {
        $this->assertCallable($f);
        return false;
    }
    
    function isA($c) {
        return $None;
    }
    
    function failMsg($msg) {
        return $this;
    }
    
    function compoundFailMsg($msg) {
        return $this->failMsg($msg);
    }
    
    protected function assertCallable($f) {
        if (!is_callable($f)) {
            throw new Exception("$f is not callbable");
        }
    }
    
    protected function assertBox($b) {
        if (!($b instanceof Box)) {
            throw new Exception("$b is not a Box");
        }
    }

}

class Full extends Box {    
    function open() {
        return $this->value;
    }
    
    function openOr($d) {
        return $this->open();
    }
    
    function map($f) {
        $this->assertCallable($f);
        return new Full($f($this->value));
    }
    
    function flatMap($f) {
        $this->assertCallable($f);
        $tmp = $f($this->value);
        $this->assertBox($tmp);
        return $tmp;
    }
    
    function each($f) {
        $this->assertCallable($f);
        $f($this->value);
    }
    
    function exists($f) {
        $this->assertCallable($f);
        return $f($this->value);
    }
    
    function isA($c) {
        global $Empty;
        return ($this->value instanceof $c ? $this : $Empty);
    }
}

class EmptyBox extends Box {
    function __construct() {
    }
    
    function next() {
        return false;
    }
    
    function valid() {
        return false;
    }
    
    function orElse($d) {
        $this->assertBox($d);
        return $d;
    }
    
    function failMsg($str) {
        return new Failure($str);
    }
}

$Empty = new EmptyBox();

class Failure extends EmptyBox {
    public $message, $exception, $failure;
    
    function __construct($message, Box $exception = NULL, Box $failure = NULL) {
        global $Empty;
        $this->message = $message;
        $this->exception = (!is_null($exception) && $exception->isDefined() && ($exception->open() instanceof Exception) ? $exception : $Empty);
        $this->failure = (!is_null($failure) && $failure->isDefined() && ($failure->open() instanceof Failure) ? $failure : $Empty);
    }
    
    function open() {
        throw new Exception(
            "Trying to open a Failure Box: " + $this->message,
            // could reuse the previous exception's code but probably best not
            // ($this->exception->map(create_function('$v', 'return $v->getCode();'))->openOr(0)),
            0,
            ($this->exception->openOr(NULL))
        );
    }
    
    function failMsg($msg) {
        return $this;
    }
    
    function compoundFailMsg($msg) {
        global $Empty;
        return new Failure($msg, $Empty, new Full($this));
    }
    
    function isA($c) {
        return $this;
    }
    
    function chainList() {
        if (!is_null($this->failure) && $this->failure->isDefined()) {
            return array_merge(array($this->failure->open()), $this->failure->open()->chainList());
        }
        return array();
    }
    
    function messageChain() {
        return implode(' <-', array_map(create_function('$a', 'return $a->message;'), array_merge(array($this), $this->chainList())));
    }
}

?>