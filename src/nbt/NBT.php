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

namespace PocketMine\NBT;
const LITTLE_ENDIAN = 0;
const BIG_ENDIAN = 1;
use PocketMine;

class NBT implements \ArrayAccess{
	private $buffer;
	private $offset;
	private $endianness;
	private $data;	

	public function get($len){
		if($len <= 0){
			$this->offset = strlen($this->buffer) - 1;
			return "";
		}elseif($len === true){
			return substr($this->buffer, $this->offset);
		}
		
		$buffer = b"";
		for(; $len > 0; --$len, ++$this->offset){
			$buffer .= @$this->buffer{$this->offset};
		}
		return $buffer;
	}
	
	public function put($v){
		$this->buffer .= $v;
	}
	
	public function feof(){
		return !isset($this->buffer{$this->offset});
	}

	public function __construct($endianness = NBT\LITTLE_ENDIAN){
		$this->offset = 0;
		$this->endianness = $endianness & 0x01;
	}
	
	public function read($buffer){
		$this->offset = 0;
		$this->buffer = $buffer;
		$this->data = $this->readTag();
		$this->buffer = b"";
	}
	
	public function write(){
		$this->offset = 0;
		if($this->data instanceof NBT\Tag\Compound){
			$this->writeTag($this->data);
			return $this->buffer;
		}else{
			return false;
		}
	}
	
	public function readTag(){
		switch($this->getByte()){
			case NBT\TAG_Byte:
				$tag = new NBT\Tag\Byte($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Byte:
				$tag = new NBT\Tag\Byte($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Short:
				$tag = new NBT\Tag\Short($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Int:
				$tag = new NBT\Tag\Int($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Long:
				$tag = new NBT\Tag\Long($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Float:
				$tag = new NBT\Tag\Float($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Double:
				$tag = new NBT\Tag\Double($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Byte_Array:
				$tag = new NBT\Tag\Byte_Array($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_String:
				$tag = new NBT\Tag\String($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Enum:
				$tag = new NBT\Tag\Enum($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Compound:
				$tag = new NBT\Tag\Compound($this->getString());
				$tag->read($this);
				break;
			case NBT\TAG_Int_Array:
				$tag = new NBT\Tag\Int_Array($this->getString());
				$tag->read($this);
				break;

			case NBT\TAG_End: //No named tag
			default:
				$tag = new NBT\Tag\End;
				break;
		}
		return $tag;
	}
	
	public function writeTag(NBTTag $tag){
		$this->putByte($tag->getType());
		if($tag instanceof NamedNBTTag){
			$this->putString($tag->getName());
		}
		$tag->write($this);
	}
	
	public function getByte(){
		return ord($this->get(1));
	}
	
	public function putByte($v){
		$this->buffer .= chr($v);
	}
	
	public function getShort(){
		return $this->endianness === self::BIG_ENDIAN ? Utils\Utils::readShort($this->get(2)) : Utils\Utils::readLShort($this->get(2));
	}
	
	public function putShort($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils\Utils::writeShort($v) : Utils\Utils::writeLShort($v);
	}
	
	public function getInt(){
		return $this->endianness === self::BIG_ENDIAN ? Utils\Utils::readInt($this->get(4)) : Utils\Utils::readLInt($this->get(4));
	}
	
	public function putInt($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils\Utils::writeInt($v) : Utils\Utils::writeLInt($v);
	}

	public function getLong(){
		return $this->endianness === self::BIG_ENDIAN ? Utils\Utils::readLong($this->get(8)) : Utils\Utils::readLLong($this->get(8));
	}
	
	public function putLong($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils\Utils::writeLong($v) : Utils\Utils::writeLLong($v);
	}

	public function getFloat(){
		return $this->endianness === self::BIG_ENDIAN ? Utils\Utils::readFloat($this->get(4)) : Utils\Utils::readLFloat($this->get(4));
	}
	
	public function putFloat($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils\Utils::writeFloat($v) : Utils\Utils::writeLFloat($v);
	}

	public function getDouble(){
		return $this->endianness === self::BIG_ENDIAN ? Utils\Utils::readDouble($this->get(8)) : Utils\Utils::readLDouble($this->get(8));
	}
	
	public function putDouble($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils\Utils::writeDouble($v) : Utils\Utils::writeLDouble($v);
	}

	public function getString(){
		return $this->get($this->getShort());
	}
	
	public function putString($v){
		$this->putShort(strlen($v));
		$this->buffer .= $v;
	}
	
	public function &__get($name){
		$ret = $this->data instanceof NBT\Tag\Compound ? $this->data[$name] : false;
		return $ret;
	}

	public function __set($name, $value){
		if($this->data instanceof NBT\Tag\Compound){
			$this->data[$name] = $value;
		}
	}
	
	public function __isset($name){
		return $this->data instanceof NBT\Tag\Compound ? isset($this->data[$name]) : false;
	}
	
	public function __unset($name){
		if($this->data instanceof NBT\Tag\Compound){
			unset($this->data[$name]);
		}
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
	
	public function getData(){
		return $this->data;
	}
	
	public function setData(NBT\Tag\Compound $data){
		$this->data = $data;
	}
	
}