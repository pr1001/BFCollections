BFCollections is a collection (ha! get it?) of collections classes for PHP 5.3+. They should make your code cleaner, more concise, and more correct.

They include:

- BFArray: A better array. It can be used just like a native array but it also has methods for common array operations such as map, filter, and reduceLeft.
- Option: Indicates an optional return result, via the form of an instance of the Some class or the global $None. Inspired by Scala's Option (naturally) and Haskell's Maybe. There are basic methods to operate on the values.
- Box: Again indicates an optional return result, though the lack of a value can also be indicated and even chained. Inspired by the Lift framework's Box.
- PartialFunction: Again [Scala inspired](http://www.scala-lang.org/api/current/scala/PartialFunction.html), partial functions are functions that do not have output for every possible input and that are easily combined or composed. The [Wikipedia page on partial functions](http://en.wikipedia.org/wiki/Partial_function) has more on the mathematical theory.

## Partial Function Examples

PartialFunctions do a very simple form of pattern matching by either testing input, either for strict equality (`===`) against the condition or as a `true` result when processed by the callable condition.

    $f = pf(
      pfCase(function ($i) { return is_integer($i); }, function ($i) { return "$i is an integer"; }),
      pfCase("test", function ($str) { return "$str == test"; })
    );
    var_dump($f->isDefinedAt(1)); // -> bool(true)
    print($f("test")); // -> test == test
    
    $f2 = $f->orElse(pf(
      pfCase($always, function () {
        return "Our wildcard catch got the following arguments: " . print_r(func_get_args(), true);
      })
    ));
    print($f2(1, 2, 3)); // -> Our wildcard catch got the following arguments: Array ( [0] => 1 [1] => 2 [2] => 3 )
    
    $f3 = $f2->andThen(function ($result) { return "<p><strong>$result</strong></p>"; });
    print($f3(1412)); // -> <p><strong>1412 is an integer</strong></p>
