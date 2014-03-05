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

class NBT implements ArrayAccess{	
	const LITTLE_ENDIAN = 0;
	const BIG_ENDIAN = 1;

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

	public function __construct($endianness = NBT::LITTLE_ENDIAN){
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
		if($this->data instanceof NBTTag_Compound){
			$this->writeTag($this->data);
			return $this->buffer;
		}else{
			return false;
		}
	}
	
	public function readTag(){
		switch($this->getByte()){
			case NBTTag::TAG_Byte:
				$tag = new NBTTag_Byte($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_Byte:
				$tag = new NBTTag_Byte($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_Short:
				$tag = new NBTTag_Short($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_Int:
				$tag = new NBTTag_Int($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_Long:
				$tag = new NBTTag_Long($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_Float:
				$tag = new NBTTag_Float($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_Double:
				$tag = new NBTTag_Double($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_Byte_Array:
				$tag = new NBTTag_Byte_Array($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_String:
				$tag = new NBTTag_String($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_List:
				$tag = new NBTTag_List($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_Compound:
				$tag = new NBTTag_Compound($this->getString());
				$tag->read($this);
				break;
			case NBTTag::TAG_Int_Array:
				$tag = new NBTTag_Int_Array($this->getString());
				$tag->read($this);
				break;

			case NBTTag::TAG_End: //No named tag
			default:
				$tag = new NBTTag_End;
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
		return $this->endianness === self::BIG_ENDIAN ? Utils::readShort($this->get(2)) : Utils::readLShort($this->get(2));
	}
	
	public function putShort($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils::writeShort($v) : Utils::writeLShort($v);
	}
	
	public function getInt(){
		return $this->endianness === self::BIG_ENDIAN ? Utils::readInt($this->get(4)) : Utils::readLInt($this->get(4));
	}
	
	public function putInt($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils::writeInt($v) : Utils::writeLInt($v);
	}

	public function getLong(){
		return $this->endianness === self::BIG_ENDIAN ? Utils::readLong($this->get(8)) : Utils::readLLong($this->get(8));
	}
	
	public function putLong($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils::writeLong($v) : Utils::writeLLong($v);
	}

	public function getFloat(){
		return $this->endianness === self::BIG_ENDIAN ? Utils::readFloat($this->get(4)) : Utils::readLFloat($this->get(4));
	}
	
	public function putFloat($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils::writeFloat($v) : Utils::writeLFloat($v);
	}

	public function getDouble(){
		return $this->endianness === self::BIG_ENDIAN ? Utils::readDouble($this->get(8)) : Utils::readLDouble($this->get(8));
	}
	
	public function putDouble($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Utils::writeDouble($v) : Utils::writeLDouble($v);
	}

	public function getString(){
		return $this->get($this->getShort());
	}
	
	public function putString($v){
		$this->putShort(strlen($v));
		$this->buffer .= $v;
	}
	
	public function &__get($name){
		$ret = $this->data instanceof NBTTag_Compound ? $this->data[$name] : false;
		return $ret;
	}

	public function __set($name, $value){
		if($this->data instanceof NBTTag_Compound){
			$this->data[$name] = $value;
		}
	}
	
	public function __isset($name){
		return $this->data instanceof NBTTag_Compound ? isset($this->data[$name]) : false;
	}
	
	public function __unset($name){
		if($this->data instanceof NBTTag_Compound){
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
	
	public function setData(NBTTag_Compound $data){
		$this->data = $data;
	}
	
}