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

/**
 * Named Binary Tag handling classes
 */
namespace pocketmine\nbt;

use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\End;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\IntArray;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\NamedTAG;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Utils;

#ifndef COMPILE
use pocketmine\utils\Binary;

#endif


#include <rules/NBT.h>

/**
 * Named Binary Tag encoder/decoder
 */
class NBT{

	const LITTLE_ENDIAN = 0;
	const BIG_ENDIAN = 1;
	const TAG_End = 0;
	const TAG_Byte = 1;
	const TAG_Short = 2;
	const TAG_Int = 3;
	const TAG_Long = 4;
	const TAG_Float = 5;
	const TAG_Double = 6;
	const TAG_ByteArray = 7;
	const TAG_String = 8;
	const TAG_Enum = 9;
	const TAG_Compound = 10;
	const TAG_IntArray = 11;

	public $buffer;
	private $offset;
	public $endianness;
	private $data;

	public function get($len){
		if($len < 0){
			$this->offset = strlen($this->buffer) - 1;
			return "";
		}elseif($len === true){
			return substr($this->buffer, $this->offset);
		}

		return $len === 1 ? $this->buffer{$this->offset++} : substr($this->buffer, ($this->offset += $len) - $len, $len);
	}

	public function put($v){
		$this->buffer .= $v;
	}

	public function feof(){
		return !isset($this->buffer{$this->offset});
	}

	public function __construct($endianness = self::LITTLE_ENDIAN){
		$this->offset = 0;
		$this->endianness = $endianness & 0x01;
	}

	public function read($buffer, $doMultiple = false){
		$this->offset = 0;
		$this->buffer = $buffer;
		$this->data = $this->readTag();
		if($doMultiple and $this->offset < strlen($this->buffer)){
			$this->data = [$this->data];
			do{
				$this->data[] = $this->readTag();
			}while($this->offset < strlen($this->buffer));
		}
		$this->buffer = "";
	}

	public function readCompressed($buffer, $compression = ZLIB_ENCODING_GZIP){
		$this->read(zlib_decode($buffer));
	}

	/**
	 * @return string|bool
	 */
	public function write(){
		$this->offset = 0;
		$data = false;
		if($this->data instanceof Compound){
			$this->writeTag($this->data);

			$data = $this->buffer;
		}elseif(is_array($this->data)){
			foreach($this->data as $tag){
				$this->writeTag($tag);
			}
			$data = $this->buffer;
		}

		return $data;
	}

	public function writeCompressed($compression = ZLIB_ENCODING_GZIP, $level = 7){
		$data = false;
		if(($write = $this->write()) !== false){
			$data = zlib_encode($write, $compression, $level);
		}

		return $data;
	}

	public function readTag(){
		switch($this->getByte()){
			case NBT::TAG_Byte:
				$tag = new Byte($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_Short:
				$tag = new Short($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_Int:
				$tag = new Int($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_Long:
				$tag = new Long($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_Float:
				$tag = new Float($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_Double:
				$tag = new Double($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_ByteArray:
				$tag = new ByteArray($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_String:
				$tag = new String($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_Enum:
				$tag = new Enum($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_Compound:
				$tag = new Compound($this->getString());
				$tag->read($this);
				break;
			case NBT::TAG_IntArray:
				$tag = new IntArray($this->getString());
				$tag->read($this);
				break;

			case NBT::TAG_End: //No named tag
			default:
				$tag = new End;
				break;
		}
		return $tag;
	}

	public function writeTag(Tag $tag){
		$this->putByte($tag->getType());
		if($tag instanceof NamedTAG){
			$this->putString($tag->getName());
		}
		$tag->write($this);
	}

	public function getByte(){
		return Binary::readByte($this->get(1));
	}

	public function putByte($v){
		$this->buffer .= Binary::writeByte($v);
	}

	public function getShort(){
		return $this->endianness === self::BIG_ENDIAN ? Binary::readShort($this->get(2)) : Binary::readLShort($this->get(2));
	}

	public function putShort($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Binary::writeShort($v) : Binary::writeLShort($v);
	}

	public function getInt(){
		return $this->endianness === self::BIG_ENDIAN ? Binary::readInt($this->get(4)) : Binary::readLInt($this->get(4));
	}

	public function putInt($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Binary::writeInt($v) : Binary::writeLInt($v);
	}

	public function getLong(){
		return $this->endianness === self::BIG_ENDIAN ? Binary::readLong($this->get(8)) : Binary::readLLong($this->get(8));
	}

	public function putLong($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Binary::writeLong($v) : Binary::writeLLong($v);
	}

	public function getFloat(){
		return $this->endianness === self::BIG_ENDIAN ? Binary::readFloat($this->get(4)) : Binary::readLFloat($this->get(4));
	}

	public function putFloat($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Binary::writeFloat($v) : Binary::writeLFloat($v);
	}

	public function getDouble(){
		return $this->endianness === self::BIG_ENDIAN ? Binary::readDouble($this->get(8)) : Binary::readLDouble($this->get(8));
	}

	public function putDouble($v){
		$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Binary::writeDouble($v) : Binary::writeLDouble($v);
	}

	public function getString(){
		return $this->get($this->getShort());
	}

	public function putString($v){
		$this->putShort(strlen($v));
		$this->buffer .= $v;
	}

	public function getArray(){
		$data = [];
		$this->toArray($data, $this->data);
	}

	private function toArray(array &$data, Tag $tag){
		/** @var Compound[]|Enum[]|IntArray[] $tag */
		foreach($tag as $key => $value){
			if($value instanceof Compound or $value instanceof Enum or $value instanceof IntArray){
				$data[$key] = [];
				$this->toArray($data[$key], $value);
			}else{
				$data[$key] = $value->getValue();
			}
		}
	}

	private function fromArray(Tag $tag, array $data){
		foreach($data as $key => $value){
			if(is_array($value)){
				$isNumeric = true;
				$isIntArray = true;
				foreach($value as $k => $v){
					if(!is_numeric($k)){
						$isNumeric = false;
						break;
					}elseif(!is_int($v)){
						$isIntArray = false;
					}
				}
				$tag{$key} = $isNumeric ? ($isIntArray ? new IntArray($key, []) : new Enum($key, [])) : new Compound($key, []);
				$this->fromArray($tag->{$key}, $value);
			}elseif(is_int($value)){
				$tag{$key} = new Int($key, $value);
			}elseif(is_float($value)){
				$tag{$key} = new Float($key, $value);
			}elseif(is_string($value)){
				if(Utils::printable($value) !== $value){
					$tag{$key} = new ByteArray($key, $value);
				}else{
					$tag{$key} = new String($key, $value);
				}
			}elseif(is_bool($value)){
				$tag{$key} = new Byte($key, $value ? 1 : 0);
			}
		}
	}

	public function setArray(array $data){
		$this->data = new Compound(null, []);
		$this->fromArray($this->data, $data);
	}

	public function getData(){
		return $this->data;
	}

	public function setData(Compound $data){
		$this->data = $data;
	}

}