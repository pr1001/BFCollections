BFCollections is a collection (ha! get it?) of collections classes for PHP 5.x. They should make your code cleaner, more concise, and more correct.

They include:
- BFArray: A better array. It can be used just like a native array but it also has methods for common array operations such as map, filter, and reduceLeft.
- Option: Indicates an optional return result, via the form of an instance of the Some class or the global $None. Inspired by Scala's Option (naturally) and Haskell's Maybe. There are basic methods to operate on the values.
- Box: Again indicates an optional return result, though the lack of a value can also be indicated and even chained. Inspired by the Lift framework's Box.
