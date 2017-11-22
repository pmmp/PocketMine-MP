<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class CompoundTag extends NamedTag implements \ArrayAccess{

	/**
	 * CompoundTag constructor.
	 *
	 * @param string     $name
	 * @param NamedTag[] $value
	 */
	public function __construct(string $name = "", array $value = []){
		parent::__construct($name, $value);
	}

	/**
	 * @return int
	 */
	public function getCount(){
		return count($this->getValue());
	}

	/**
	 * @return NamedTag[]
	 */
	public function &getValue(){
		$result = [];
		foreach($this as $tag){
			if($tag instanceof NamedTag){
				$result[$tag->getName()] = $tag;
			}
		}

		return $result;
	}

	/**
	 * @param NamedTag[] $value
	 *
	 * @throws \TypeError
	 */
	public function setValue($value) : void{
		if(is_array($value)){
			foreach($value as $name => $tag){
				if($tag instanceof NamedTag){
					$this->{$tag->__name} = $tag;
				}else{
					throw new \TypeError("CompoundTag members must be NamedTags, got " . gettype($tag) . " in given array");
				}
			}
		}else{
			throw new \TypeError("CompoundTag value must be NamedTag[], " . gettype($value) . " given");
		}
	}

	/*
	 * Here follows many functions of misery for the sake of type safety. We really needs generics in PHP :(
	 */

	/**
	 * Returns the tag with the specified name, or null if it does not exist.
	 *
	 * @param string      $name
	 * @param string|null $expectedClass Class that extends NamedTag
	 *
	 * @return NamedTag|null
	 * @throws \RuntimeException if the tag exists and is not of the expected type (if specified)
	 */
	public function getTag(string $name, string $expectedClass = NamedTag::class) : ?NamedTag{
		assert(is_a($expectedClass, NamedTag::class, true));
		$tag = $this->{$name} ?? null;
		if($tag !== null and !($tag instanceof $expectedClass)){
			throw new \RuntimeException("Expected a tag of type $expectedClass, got " . get_class($tag));
		}

		return $tag;
	}

	/**
	 * Returns the ListTag with the specified name, or null if it does not exist. Triggers an exception if a tag exists
	 * with that name and the tag is not a ListTag.
	 *
	 * @param string $name
	 * @return ListTag|null
	 */
	public function getListTag(string $name) : ?ListTag{
		return $this->getTag($name, ListTag::class);
	}

	/**
	 * Returns the CompoundTag with the specified name, or null if it does not exist. Triggers an exception if a tag
	 * exists with that name and the tag is not a CompoundTag.
	 *
	 * @param string $name
	 * @return CompoundTag|null
	 */
	public function getCompoundTag(string $name) : ?CompoundTag{
		return $this->getTag($name, CompoundTag::class);
	}

	/**
	 * Sets the specified NamedTag as a child tag of the CompoundTag at the offset specified by the tag's name. If a tag
	 * already exists at the offset, it will be overwritten with the new one.
	 *
	 * @param NamedTag $tag
	 */
	public function setTag(NamedTag $tag) : void{
		$this->{$tag->__name} = $tag;
	}

	/**
	 * Removes the child tags with the specified names from the CompoundTag. This function accepts a variadic list of
	 * strings.
	 *
	 * @param string[] ...$names
	 */
	public function removeTag(string ...$names) : void{
		foreach($names as $name){
			unset($this->{$name});
		}
	}

	/**
	 * Returns whether the CompoundTag contains a child tag with the specified name.
	 *
	 * @param string $name
	 * @param string $expectedClass
	 *
	 * @return bool
	 */
	public function hasTag(string $name, string $expectedClass = NamedTag::class) : bool{
		assert(is_a($expectedClass, NamedTag::class, true));
		return ($this->{$name} ?? null) instanceof $expectedClass;
	}

	/**
	 * Returns the value of the child tag with the specified name, or $default if the tag doesn't exist. If the child
	 * tag is not of type $expectedType, an exception will be thrown, unless a default is given and $badTagDefault is
	 * true.
	 *
	 * @param string $name
	 * @param string $expectedClass
	 * @param mixed  $default
	 * @param bool   $badTagDefault Return the specified default if the tag is not of the expected type.
	 *
	 * @return mixed
	 */
	public function getTagValue(string $name, string $expectedClass, $default = null, bool $badTagDefault = false){
		$tag = $this->getTag($name, $badTagDefault ? NamedTag::class : $expectedClass);
		if($tag instanceof $expectedClass){
			return $tag->getValue();
		}

		if($default === null){
			throw new \RuntimeException("Tag with name \"$name\" " . ($tag !== null ? "not of expected type" : "not found") . " and no valid default value given");
		}

		return $default;
	}

	/*
	 * The following methods are wrappers around getTagValue() with type safety.
	 */

	/**
	 * @param string   $name
	 * @param int|null $default
	 * @param bool     $badTagDefault
	 *
	 * @return int
	 */
	public function getByte(string $name, ?int $default = null, bool $badTagDefault = false) : int{
		return $this->getTagValue($name, ByteTag::class, $default, $badTagDefault);
	}

	/**
	 * @param string   $name
	 * @param int|null $default
	 * @param bool     $badTagDefault
	 *
	 * @return int
	 */
	public function getShort(string $name, ?int $default = null, bool $badTagDefault = false) : int{
		return $this->getTagValue($name, ShortTag::class, $default, $badTagDefault);
	}

	/**
	 * @param string   $name
	 * @param int|null $default
	 * @param bool     $badTagDefault
	 *
	 * @return int
	 */
	public function getInt(string $name, ?int $default = null, bool $badTagDefault = false) : int{
		return $this->getTagValue($name, IntTag::class, $default, $badTagDefault);
	}

	/**
	 * @param string   $name
	 * @param int|null $default
	 * @param bool     $badTagDefault
	 *
	 * @return int
	 */
	public function getLong(string $name, ?int $default = null, bool $badTagDefault = false) : int{
		return $this->getTagValue($name, LongTag::class, $default, $badTagDefault);
	}

	/**
	 * @param string     $name
	 * @param float|null $default
	 * @param bool       $badTagDefault
	 *
	 * @return float
	 */
	public function getFloat(string $name, ?float $default = null, bool $badTagDefault = false) : float{
		return $this->getTagValue($name, FloatTag::class, $default, $badTagDefault);
	}

	/**
	 * @param string     $name
	 * @param float|null $default
	 * @param bool       $badTagDefault
	 *
	 * @return float
	 */
	public function getDouble(string $name, ?float $default = null, bool $badTagDefault = false) : float{
		return $this->getTagValue($name, DoubleTag::class, $default, $badTagDefault);
	}

	/**
	 * @param string      $name
	 * @param string|null $default
	 * @param bool        $badTagDefault
	 *
	 * @return string
	 */
	public function getByteArray(string $name, ?string $default = null, bool $badTagDefault = false) : string{
		return $this->getTagValue($name, ByteArrayTag::class, $default, $badTagDefault);
	}

	/**
	 * @param string      $name
	 * @param string|null $default
	 * @param bool        $badTagDefault
	 *
	 * @return string
	 */
	public function getString(string $name, ?string $default = null, bool $badTagDefault = false) : string{
		return $this->getTagValue($name, StringTag::class, $default, $badTagDefault);
	}

	/**
	 * @param string     $name
	 * @param int[]|null $default
	 * @param bool       $badTagDefault
	 *
	 * @return int[]
	 */
	public function getIntArray(string $name, ?array $default = null, bool $badTagDefault = false) : array{
		return $this->getTagValue($name, IntArrayTag::class, $default, $badTagDefault);
	}

	/**
	 * Sets the value of the child tag at the specified offset, creating it if it does not exist. If the child tag
	 * exists and the value is of the wrong type, an exception will be thrown.
	 *
	 * @param string $name Name of the tag to set
	 * @param string $tagClass Class that extends NamedTag
	 * @param mixed  $value Value to set. This should be compatible with the specified tag type.
	 * @param bool   $force Force set the value even if the existing tag is not the correct type (overwrite the old tag)
	 */
	public function setTagValue(string $name, string $tagClass, $value, bool $force) : void{
		assert(is_a($tagClass, NamedTag::class, true));
		$tag = $this->getTag($name, $force ? NamedTag::class : $tagClass);
		if($tag instanceof $tagClass){
			$tag->setValue($value);
		}else{
			$this->setTag(new $tagClass($name, $value));
		}
	}

	/*
	 * The following methods are wrappers around setTagValue() with type safety.
	 */

	/**
	 * @param string $name
	 * @param int    $value
	 * @param bool   $force
	 */
	public function setByte(string $name, int $value, bool $force = false) : void{
		$this->setTagValue($name, ByteTag::class, $value, $force);
	}

	/**
	 * @param string $name
	 * @param int    $value
	 * @param bool   $force
	 */
	public function setShort(string $name, int $value, bool $force = false) : void{
		$this->setTagValue($name, ShortTag::class, $value, $force);
	}

	/**
	 * @param string $name
	 * @param int    $value
	 * @param bool   $force
	 */
	public function setInt(string $name, int $value, bool $force = false) : void{
		$this->setTagValue($name, IntTag::class, $value, $force);
	}

	/**
	 * @param string $name
	 * @param int    $value
	 * @param bool   $force
	 */
	public function setLong(string $name, int $value, bool $force = false) : void{
		$this->setTagValue($name, LongTag::class, $value, $force);
	}

	/**
	 * @param string $name
	 * @param float  $value
	 * @param bool   $force
	 */
	public function setFloat(string $name, float $value, bool $force = false) : void{
		$this->setTagValue($name, FloatTag::class, $value, $force);
	}

	/**
	 * @param string $name
	 * @param float  $value
	 * @param bool   $force
	 */
	public function setDouble(string $name, float $value, bool $force = false) : void{
		$this->setTagValue($name, DoubleTag::class, $value, $force);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param bool   $force
	 */
	public function setByteArray(string $name, string $value, bool $force = false) : void{
		$this->setTagValue($name, ByteArrayTag::class, $value, $force);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param bool   $force
	 */
	public function setString(string $name, string $value, bool $force = false) : void{
		$this->setTagValue($name, StringTag::class, $value, $force);
	}

	/**
	 * @param string $name
	 * @param int[]  $value
	 * @param bool   $force
	 */
	public function setIntArray(string $name, array $value, bool $force = false) : void{
		$this->setTagValue($name, IntArrayTag::class, $value, $force);
	}



	public function offsetExists($offset){
		return isset($this->{$offset}) and $this->{$offset} instanceof Tag;
	}

	public function offsetGet($offset){
		if(isset($this->{$offset}) and $this->{$offset} instanceof Tag){
			if($this->{$offset} instanceof \ArrayAccess){
				return $this->{$offset};
			}else{
				return $this->{$offset}->getValue();
			}
		}

		assert(false, "Offset $offset not found");

		return null;
	}

	public function offsetSet($offset, $value){
		if($value instanceof Tag){
			$this->{$offset} = $value;
		}elseif(isset($this->{$offset}) and $this->{$offset} instanceof Tag){
			$this->{$offset}->setValue($value);
		}
	}

	public function offsetUnset($offset){
		unset($this->{$offset});
	}

	public function getType() : int{
		return NBT::TAG_Compound;
	}

	public function read(NBT $nbt, bool $network = false) : void{
		$this->value = [];
		do{
			$tag = $nbt->readTag($network);
			if($tag instanceof NamedTag and $tag->__name !== ""){
				$this->{$tag->__name} = $tag;
			}
		}while(!($tag instanceof EndTag) and !$nbt->feof());
	}

	public function write(NBT $nbt, bool $network = false) : void{
		foreach($this as $tag){
			if($tag instanceof Tag and !($tag instanceof EndTag)){
				$nbt->writeTag($tag, $network);
			}
		}
		$nbt->writeTag(new EndTag, $network);
	}

	public function __toString(){
		$str = get_class($this) . "{\n";
		foreach($this as $tag){
			if($tag instanceof Tag){
				$str .= get_class($tag) . ":" . $tag->__toString() . "\n";
			}
		}
		return $str . "}";
	}

	public function __clone(){
		foreach($this as $key => $tag){
			if($tag instanceof Tag){
				$this->{$key} = clone $tag;
			}
		}
	}
}