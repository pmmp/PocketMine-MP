<?php
/**
 * Generated stub file for code completion purposes
 */

namespace Ds {
interface Hashable{

	public function hash();

	public function equals($obj) : bool;
}

interface Collection extends \Traversable, \Countable, \JsonSerializable{

	public function clear();

	public function copy() : \Ds\Collection;

	public function isEmpty() : bool;

	public function toArray() : array;
}

interface Sequence extends \Ds\Collection{

	public function allocate(int $capacity);

	public function capacity() : int;

	public function contains(...$values) : bool;

	public function filter(callable $callback = null) : \Ds\Sequence;

	public function find($value);

	public function first();

	public function get(int $index);

	public function insert(int $index, ...$values);

	public function join(string $glue = null) : string;

	public function last();

	public function map(callable $callback) : \Ds\Sequence;

	public function merge($values) : \Ds\Sequence;

	public function pop();

	public function push(...$values);

	public function reduce(callable $callback, $initial = null);

	public function remove(int $index);

	public function reverse();

	public function rotate(int $rotations);

	public function set(int $index, $value);

	public function shift();

	public function slice(int $index, int $length = null) : \Ds\Sequence;

	public function sort(callable $comparator = null);

	public function unshift(...$values);
}

final class Vector implements \Ds\Sequence{
	public const MIN_CAPACITY = 8;

	public function __construct($values = null){}

	public function allocate(int $capacity){}

	public function apply(callable $callback){}

	public function capacity() : int{}

	public function contains(...$values) : bool{}

	public function filter(callable $callback = null) : \Ds\Sequence{}

	public function find($value){}

	public function first(){}

	public function get(int $index){}

	public function insert(int $index, ...$values){}

	public function join(string $glue = null) : string{}

	public function last(){}

	public function map(callable $callback) : \Ds\Sequence{}

	public function merge($values) : \Ds\Sequence{}

	public function pop(){}

	public function push(...$values){}

	public function reduce(callable $callback, $initial = null){}

	public function remove(int $index){}

	public function reverse(){}

	public function reversed() : \Ds\Sequence{}

	public function rotate(int $rotations){}

	public function set(int $index, $value){}

	public function shift(){}

	public function slice(int $index, int $length = null) : \Ds\Sequence{}

	public function sort(callable $comparator = null){}

	public function sorted(callable $comparator = null) : \Ds\Sequence{}

	public function sum(){}

	public function unshift(...$values){}

	public function clear(){}

	public function copy() : \Ds\Collection{}

	public function count() : int{}

	public function isEmpty() : bool{}

	public function jsonSerialize(){}

	public function toArray() : array{}
}

final class Deque implements \Ds\Sequence{
	public const MIN_CAPACITY = 8;

	public function __construct($values = null){}

	public function clear(){}

	public function copy() : \Ds\Collection{}

	public function count() : int{}

	public function isEmpty() : bool{}

	public function jsonSerialize(){}

	public function toArray() : array{}

	public function allocate(int $capacity){}

	public function apply(callable $callback){}

	public function capacity() : int{}

	public function contains(...$values) : bool{}

	public function filter(callable $callback = null) : \Ds\Sequence{}

	public function find($value){}

	public function first(){}

	public function get(int $index){}

	public function insert(int $index, ...$values){}

	public function join(string $glue = null) : string{}

	public function last(){}

	public function map(callable $callback) : \Ds\Sequence{}

	public function merge($values) : \Ds\Sequence{}

	public function pop(){}

	public function push(...$values){}

	public function reduce(callable $callback, $initial = null){}

	public function remove(int $index){}

	public function reverse(){}

	public function reversed() : \Ds\Sequence{}

	public function rotate(int $rotations){}

	public function set(int $index, $value){}

	public function shift(){}

	public function slice(int $index, int $length = null) : \Ds\Sequence{}

	public function sort(callable $comparator = null){}

	public function sorted(callable $comparator = null) : \Ds\Sequence{}

	public function sum(){}

	public function unshift(...$values){}
}

final class Stack implements \Ds\Collection{

	public function __construct($values = null){}

	public function allocate(int $capacity){}

	public function capacity() : int{}

	public function peek(){}

	public function pop(){}

	public function push(...$values){}

	public function clear(){}

	public function copy() : \Ds\Collection{}

	public function count() : int{}

	public function isEmpty() : bool{}

	public function jsonSerialize(){}

	public function toArray() : array{}
}

final class Queue implements \Ds\Collection{
	public const MIN_CAPACITY = 8;

	public function __construct($values = null){}

	public function allocate(int $capacity){}

	public function capacity() : int{}

	public function peek(){}

	public function pop(){}

	public function push(...$values){}

	public function clear(){}

	public function copy() : \Ds\Collection{}

	public function count() : int{}

	public function isEmpty() : bool{}

	public function jsonSerialize(){}

	public function toArray() : array{}
}

final class Map implements \Ds\Collection{
	public const MIN_CAPACITY = 8;

	public function __construct($values = null){}

	public function allocate(int $capacity){}

	public function apply(callable $callback){}

	public function capacity() : int{}

	public function diff(\Ds\Map $map) : \Ds\Map{}

	public function filter(callable $callback = null) : \Ds\Map{}

	public function first() : \Ds\Pair{}

	public function get($key, $default = null){}

	public function hasKey($key) : bool{}

	public function hasValue($value) : bool{}

	public function intersect(\Ds\Map $map) : \Ds\Map{}

	public function keys() : \Ds\Set{}

	public function ksort(callable $comparator = null){}

	public function ksorted(callable $comparator = null) : \Ds\Map{}

	public function last() : \Ds\Pair{}

	public function map(callable $callback) : \Ds\Map{}

	public function merge($values) : \Ds\Map{}

	public function pairs() : \Ds\Sequence{}

	public function put($key, $value){}

	public function putAll($values){}

	public function reduce(callable $callback, $initial = null){}

	public function remove($key, $default = null){}

	public function reverse(){}

	public function reversed() : \Ds\Map{}

	public function skip(int $position) : \Ds\Pair{}

	public function slice(int $index, int $length = null) : \Ds\Map{}

	public function sort(callable $comparator = null){}

	public function sorted(callable $comparator = null) : \Ds\Map{}

	public function sum(){}

	public function union($map) : \Ds\Map{}

	public function values() : \Ds\Sequence{}

	public function xor(\Ds\Map $map) : \Ds\Map{}

	public function clear(){}

	public function copy() : \Ds\Collection{}

	public function count() : int{}

	public function isEmpty() : bool{}

	public function jsonSerialize(){}

	public function toArray() : array{}
}

final class Set implements \Ds\Collection{
	public const MIN_CAPACITY = 8;

	public function __construct($values = null){}

	public function add(...$values){}

	public function allocate(int $capacity){}

	public function capacity() : int{}

	public function contains(...$values) : bool{}

	public function diff(\Ds\Set $set) : \Ds\Set{}

	public function filter(callable $callback = null) : \Ds\Set{}

	public function first(){}

	public function get(int $index){}

	public function intersect(\Ds\Set $set) : \Ds\Set{}

	public function join(string $glue = null){}

	public function last(){}

	public function merge($values) : \Ds\Set{}

	public function reduce(callable $callback, $initial = null){}

	public function remove(...$values){}

	public function reverse(){}

	public function reversed() : \Ds\Set{}

	public function slice(int $index, int $length = null) : \Ds\Set{}

	public function sort(callable $comparator = null){}

	public function sorted(callable $comparator = null) : \Ds\Set{}

	public function sum(){}

	public function union(\Ds\Set $set) : \Ds\Set{}

	public function xor(\Ds\Set $set) : \Ds\Set{}

	public function clear(){}

	public function copy() : \Ds\Collection{}

	public function count() : int{}

	public function isEmpty() : bool{}

	public function jsonSerialize(){}

	public function toArray() : array{}
}

final class PriorityQueue implements \Ds\Collection{
	public const MIN_CAPACITY = 8;

	public function __construct(){}

	public function allocate(int $capacity){}

	public function capacity() : int{}

	public function peek(){}

	public function pop(){}

	public function push($value, int $priority){}

	public function clear(){}

	public function copy() : \Ds\Collection{}

	public function count() : int{}

	public function isEmpty() : bool{}

	public function jsonSerialize(){}

	public function toArray() : array{}
}

final class Pair implements \JsonSerializable{

	public function __construct($key = null, $value = null){}

	public function copy() : \Ds\Pair{}

	public function jsonSerialize(){}

	public function toArray() : array{}
}
}
