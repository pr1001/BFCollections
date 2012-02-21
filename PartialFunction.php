<?php
namespace com\bubblefoundry\collections;

class BFCase {
  var $condition, $f;
  
  function __construct($condition, $f) {
    if (!is_callable($f)) {
      throw new Error('$f must be a function');
    }
    $this->condition = $condition;
    $this->f = $f;
  }
}

function pfCase($condition, $f) {
  return new BFCase($condition, $f);
}

class PartialFunction {
  private $cases;
  
  function __construct() {
    $args = func_get_args();
    if (is_array($args) && count($args) == 1) {
      $args = $args[0];
    }
    
    foreach ($args as $k => $v) {
      if (!($v instanceof BFCase)) {
        throw new Error('Only BFCases are allowed. You gave: ' . $v);
      }
      $this->cases[] = $v;
    }
  }
  
  function getCases() {
    return $this->cases;
  }
  
  function __invoke() {
    $args = func_get_args();
    foreach ($this->cases as $k => $v) {
      if (is_callable($v->condition)) {
        $vc = new \ReflectionFunction($v->condition);
        // reduce mistaken hits by only calling the condition on cases when there aren't too many or too few args
        // the || 0 case is a special one when the condition function hasn't taken any paramenters. Then we should always invoke the function to check because it is presumably using func_get_args() or is our $always condition
        if ($vc->getNumberOfParameters() <= count($args) && ($vc->getNumberOfRequiredParameters() == 0 || $vc->getNumberOfRequiredParameters() >= count($args)) && $vc->invokeArgs($args)) {
          $vf = new \ReflectionFunction($v->f);
          return $vf->invokeArgs($args);
        }
      }
      elseif ($v->condition === $args || (count($args) === 1 && $v->condition === $args[0])) {
        $vf = new \ReflectionFunction($v->f);
        return $vf->invokeArgs($args);
      }
    }
    throw new \UnexpectedValueException("No conditions matched the given input:" . $args);
  }
  
  function isDefinedAt($point) {
    foreach ($this->cases as $k => $v) {
      $vc = new \ReflectionFunction($v->condition);
      if ((is_callable($v->condition) && $vc->invokeArgs(array($point))) || $v->condition === $point) {
        return true;
      }
    }
    return false;
  }
  
  function orElse(PartialFunction $pf) {
    return new PartialFunction(array_merge($this->cases, $pf->getCases()));
  }
  
  function andThen($f) {
    if (!is_callable($f)) {
      throw new Error('$f must be a function');
    }
    $new_cases = array();
    foreach ($this->cases as $k => $v) {
      $new_cases[] = new BFCase($v->condition, function () use ($f, $v) {
        $args = func_get_args();
        $vf = new \ReflectionFunction($v->f);
        return $f($vf->invokeArgs($args));
      });
    }
    return new PartialFunction($new_cases);
  }
}

function pf() {
  $args = func_get_args();
  return new PartialFunction($args);
}

$always = function () { return true; };

?>