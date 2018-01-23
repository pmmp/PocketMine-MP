<?php

/**
 * WeakRef extension stub file for code completion purposes
 *
 * WARNING: Do not include this file
 *
 */
class WeakRef{

	/**
	 * Constructs a new weak reference.
	 *
	 * @param object $object
	 */
	public function __construct($object = null){
	}

	/**
	 * Acquires a strong reference on that object,
	 * virtually turning the weak reference into a strong one.
	 *
	 * @return bool
	 */
	public function acquire(){
	}

	/**
	 * Returns the object pointed to by the weak reference
	 *
	 * @return object
	 */
	public function get(){
	}

	/**
	 * Releases a previously acquired reference,
	 * potentially turning a strong reference back into a weak reference.
	 *
	 * @return bool
	 */
	public function release(){
	}

	/**
	 * Checks whether the object referenced still exists
	 *
	 * @return bool
	 */
	public function valid(){
	}
}

class WeakMap implements Countable, ArrayAccess, Iterator{

	/**
	 * Constructs a new map
	 */
	public function __construct(){
	}

	/**
	 * Counts the number of live entries in the map
	 *
	 * @return int
	 */
	public function count(){
	}

	/**
	 * Returns the current value being iterated on in the map.
	 *
	 * @return mixed
	 */
	public function current(){
	}

	/**
	 * Returns the object serving as key in the map, at the current iterating position.
	 *
	 * @return mixed
	 */
	public function key(){
	}

	/**
	 * Advances to the next map element.
	 */
	public function next(){
	}

	/**
	 * Checks whether the passed object is referenced in the map.
	 *
	 * @param object $object
	 *
	 * @return bool
	 */
	public function offsetExists($object){
	}

	/**
	 * Returns the value pointed to by a certain object.
	 *
	 * @param object $object
	 *
	 * @return mixed
	 */
	public function offsetGet($object){
	}

	/**
	 * Updates the map with a new key-value pair.
	 * If the key already existed in the map, the old value is replaced with the new.
	 *
	 * @param object $object
	 * @param mixed  $value
	 */
	public function offsetSet($object, $value){
	}

	/**
	 * Removes an entry from the map.
	 *
	 * @param object $object
	 */
	public function offsetUnset($object){
	}

	/**
	 * Rewinds the iterator to the beginning of the map.
	 */
	public function rewind(){
	}

	/**
	 * Returns whether the iterator is still on a valid map element.
	 *
	 * @return bool
	 */
	public function valid(){
	}
}