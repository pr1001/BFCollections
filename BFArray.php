<?php

/**
 * @author Peter Robinett <peter@bubblefoundry.com>
 * @since 2010-08-03
 */

function array2BFArray($arr) {
    if (is_array($arr)) {
        // stupid PHP doesn't like chaining calls when starting with a new object
        $tmp = new BFArray($arr);
        return $tmp->map(create_function('$a', 'return array2BFArray($a);'));
    }
    return $arr;
}

function BFArray2array(BFArray $BFArray) {
    return $BFArray->toArray();
}

class BFArray extends ArrayIterator {
    
    /**
     * function __construct(mixed $arr)
     * $arr must implement Iterator. This means you can pass a normal PHP array or a BFArray.
     */
    function __construct() {
        $args = func_get_args();
        if (count($args) > 1 || (count($args) == 1 && !is_array($args[0]))) {
            parent::__construct($args);
        } elseif ($args) {
            parent::__construct($this->input2array($args[0]));
        } else {
            parent::__construct();
        }
    }
    
    function toArray() {
        return $this->getArrayCopy();
    }
    
    function merge($arr) {
        return new BFArray(array_merge($this->toArray(), $this->input2array($arr)));
    }
    
    function filter($callback = NULL) {
        return new BFArray(array_filter($this->toArray(), $callback));
    }
    
    function map($callback) {
        return new BFArray(array_map($callback, $this->toArray()));
    }
    
    function contains($needle) {
        return (array_search($needle, $this->toArray(), true) !== false ? true : false);
    }
    
    function reduceLeft($initial, $callback) {
        // if we have numbers we can use the native function but it's simpler to just use our general purpose method across the board
        if ($this->count() == 1) {
            return call_user_func($callback, $initial, $this[0]);
        } elseif ($this->count() > 1) {
            return $this->slice(1)->reduceLeft(call_user_func($callback, $initial, $this[0]), $callback);
        }
    }
    
    function reduceRight($terminating, $callback) {
        if ($this->count() == 1) {
            return call_user_func($callback, $terminating, $this[0]);
        } elseif ($this->count() > 1) {
            return $this->slice(0, -1)->reduceRight(call_user_func($callback, $terminating, $this[$this->count() - 1]), $callback);
        }
    }
    
    function foldLeft($callback) {
        if ($this->count() > 0) {
            return $this->slice(1)->reduceLeft($this[0], $callback);
        }
    }
    
    function foldRight($callback) {
        if ($this->count() > 0) {
            return $this->slice(0, -1)->reduceRight($this[$this->count() - 1], $callback);
        }
    }
    
    function slice($offset, $length = NULL, $preserve = false) {
        if (is_null($length)) {
            $length = $this->count() - $offset;
        }
        return new BFArray(array_slice($this->toArray(), $offset, $length, $preserve));
    }
    
    function reverse($preserve_keys = false) {
        return new BFArray(array_reverse($this->toArray(), $preserve_keys));
    }
    
    function isEmpty() {
        return ($this->count() == 0 ? true : false);
    }
    
    /*
        array_combine(array keys, array values)
        array_chunk(array input, int size [, bool preserve_keys])
        array_diff(array array1, array array2 [, array ...])
        array_fill(int start_index, int num, mixed value)
        array_flip(array trans)
        array_intersect(array array1, array array2 [, array ...])
        array_pad(array input, int pad_size, mixed pad_value)
        array_pop(array &array)
        array_product(array array)
        array_push(array &array, mixed var [, mixed ...])
        array_reduce(array input, callback function [, int initial])
        array_rand(array input [, int num_req])
        array_reverse(array array [, bool preserve_keys])
        array_search(mixed needle, array haystack [, bool strict])
        array_shift(array &array)
        array_slice(array array, int offset [, int length [, bool preserve_keys]])
        array_splice(array &input, int offset [, int length [, array replacement]])
        array_sum(array array)
        array_unique(array array)
        array_unshift(array &array, mixed var [, mixed ...])
        array_udiff(array array1, array array2 [, array ..., callback data_compare_func])
        array_uintersect(array array1, array array2 [, array ..., callback data_compare_func])
        array_walk(array &array, callback funcname [, mixed userdata])
        array_walk_recursive(array &input, callback funcname [, mixed userdata])
    */
    
    function keys() {
        // array_keys(array input [, mixed search_value [, bool strict]])
        return new BFArray(array_keys($this->toArray()));
    }
    
    function values() {
        return new BFArray(array_values($this->toArray()));
    }
    
    private function input2array($arr) {
        if (!($arr instanceof ArrayIterator) && !(is_array($arr))) {
            throw new Exception('An array-like object must be given.');
        }
        return ($arr instanceof ArrayIterator ? $arr->getArrayCopy() : $arr);
    }
    
    private function getTypes() {    
        return $this->map(create_function('$a', 'return gettype($a);'))->unique();
    }
    
}

?>