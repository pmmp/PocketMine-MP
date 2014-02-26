<?php

/**
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

class NBTTag_List extends NamedNBTTag implements ArrayAccess, Iterator{
	
	private $tagType;
	
	public function getType(){
		return NBTTag::TAG_List;
	}
	
	public function setTagType($type){
		$this->tagType = $type;
	}
	
	public function getTagType(){
		return $this->tagType;
	}
	
	public function rewind(){
		reset($this->value);
	}
	
	public function current(){
		return current($this->value);
	}
	
	public function key(){
		return key($this->value);
	}
	
	public function next(){
		return next($this->value);
	}
	
	public function valid(){
		$key = key($this->value);
		return $key !== null and $key !== false;
	}
	
	public function offsetExists($name){
		return $this->__isset($name);
	}
	
	public function &offsetGet($name){
		return $this->__get($name);
	}
	
	public function offsetSet($name, $value){
		$this->__set($name, $value);
	}
	
	public function offsetUnset($name){
		$this->__unset($name);
	}
	
	public function &__get($name){
		$ret = isset($this->value[$name]) ? $this->value[$name] : false;
		if(!is_object($ret) or $ret instanceof ArrayAccess){
			return $ret;
		}else{
			return $ret->getValue();
		}
	}

	public function __set($name, $value){
		if($value instanceof NBTTag){
			$this->value[$name] = $value;
		}elseif(isset($this->value[$name])){
			$this->value[$name]->setValue($value);
		}
	}
	
	public function __isset($name){
		return isset($this->value[$name]);
	}
	
	public function __unset($name){
		unset($this->value[$name]);
	}
	
	public function read(NBT $nbt){
		$this->value = array();
		$this->tagType = $nbt->getByte();
		$size = $nbt->getInt();
		for($i = 0; $i < $size and !$nbt->feof(); ++$i){
			switch($this->tagType){
				case NBTTag::TAG_Byte:
					$tag = new NBTTag_Byte(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Byte:
					$tag = new NBTTag_Byte(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Short:
					$tag = new NBTTag_Short(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Int:
					$tag = new NBTTag_Int(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Long:
					$tag = new NBTTag_Long(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Float:
					$tag = new NBTTag_Float(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Double:
					$tag = new NBTTag_Double(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Byte_Array:
					$tag = new NBTTag_Byte_Array(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_String:
					$tag = new NBTTag_String(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_List:
					$tag = new NBTTag_List(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Compound:
					$tag = new NBTTag_Compound(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
				case NBTTag::TAG_Int_Array:
					$tag = new NBTTag_Int_Array(false);
					$tag->read($nbt);
					$this->value[] = $tag;
					break;
			}
		}
	}
	
	public function write(NBT $nbt){
		$nbt->putByte($this->tagType);
		$nbt->putInt(count($this->value));
		foreach($this->value as $tag){
			if($tag instanceof NBTTag){
				$tag->write($nbt);
			}
		}
	}
}