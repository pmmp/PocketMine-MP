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

use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\EndTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\NamedTAG;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;

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
	const TAG_List = 9;
	const TAG_Compound = 10;
	const TAG_IntArray = 11;

	public $buffer;
	private $offset;
	public $endianness;
	private $data;

	public static function matchList(ListTag $tag1, ListTag $tag2){
		if($tag1->getName() !== $tag2->getName() or $tag1->getCount() !== $tag2->getCount()){
			return false;
		}

		foreach($tag1 as $k => $v){
			if(!($v instanceof Tag)){
				continue;
			}

			if(!isset($tag2->{$k}) or !($tag2->{$k} instanceof $v)){
				return false;
			}

			if($v instanceof CompoundTag){
				if(!self::matchTree($v, $tag2->{$k})){
					return false;
				}
			}elseif($v instanceof ListTag){
				if(!self::matchList($v, $tag2->{$k})){
					return false;
				}
			}else{
				if($v->getValue() !== $tag2->{$k}->getValue()){
					return false;
				}
			}
		}

		return true;
	}

	public static function matchTree(CompoundTag $tag1, CompoundTag $tag2){
		if($tag1->getName() !== $tag2->getName() or $tag1->getCount() !== $tag2->getCount()){
			return false;
		}

		foreach($tag1 as $k => $v){
			if(!($v instanceof Tag)){
				continue;
			}

			if(!isset($tag2->{$k}) or !($tag2->{$k} instanceof $v)){
				return false;
			}

			if($v instanceof CompoundTag){
				if(!self::matchTree($v, $tag2->{$k})){
					return false;
				}
			}elseif($v instanceof ListTag){
				if(!self::matchList($v, $tag2->{$k})){
					return false;
				}
			}else{
				if($v->getValue() !== $tag2->{$k}->getValue()){
					return false;
				}
			}
		}

		return true;
	}

	public static function parseJSON($data, &$offset = 0){
		$len = strlen($data);
		for(; $offset < $len; ++$offset){
			$c = $data{$offset};
			if($c === "{"){
				++$offset;
				$data = self::parseCompound($data, $offset);
				return new CompoundTag("", $data);
			}elseif($c !== " " and $c !== "\r" and $c !== "\n" and $c !== "\t"){
				throw new \Exception("Syntax error: unexpected '$c' at offset $offset");
			}
		}

		return null;
	}

	private static function parseList($str, &$offset = 0){
		$len = strlen($str);


		$key = 0;
		$value = null;

		$data = [];

		for(; $offset < $len; ++$offset){
			if($str{$offset - 1} === "]"){
				break;
			}elseif($str{$offset} === "]"){
				++$offset;
				break;
			}

			$value = self::readValue($str, $offset, $type);

			switch($type){
				case NBT::TAG_Byte:
					$data[$key] = new ByteTag($key, $value);
					break;
				case NBT::TAG_Short:
					$data[$key] = new ShortTag($key, $value);
					break;
				case NBT::TAG_Int:
					$data[$key] = new IntTag($key, $value);
					break;
				case NBT::TAG_Long:
					$data[$key] = new LongTag($key, $value);
					break;
				case NBT::TAG_Float:
					$data[$key] = new FloatTag($key, $value);
					break;
				case NBT::TAG_Double:
					$data[$key] = new DoubleTag($key, $value);
					break;
				case NBT::TAG_ByteArray:
					$data[$key] = new ByteArrayTag($key, $value);
					break;
				case NBT::TAG_String:
					$data[$key] = new StringTag($key, $value);
					break;
				case NBT::TAG_List:
					$data[$key] = new ListTag($key, $value);
					break;
				case NBT::TAG_Compound:
					$data[$key] = new CompoundTag($key, $value);
					break;
				case NBT::TAG_IntArray:
					$data[$key] = new IntArrayTag($key, $value);
					break;
			}

			$key++;
		}

		return $data;
	}

	private static function parseCompound($str, &$offset = 0){
		$len = strlen($str);

		$data = [];

		for(; $offset < $len; ++$offset){
			if($str{$offset - 1} === "}"){
				break;
			}elseif($str{$offset} === "}"){
				++$offset;
				break;
			}

			$key = self::readKey($str, $offset);
			$value = self::readValue($str, $offset, $type);

			switch($type){
				case NBT::TAG_Byte:
					$data[$key] = new ByteTag($key, $value);
					break;
				case NBT::TAG_Short:
					$data[$key] = new ShortTag($key, $value);
					break;
				case NBT::TAG_Int:
					$data[$key] = new IntTag($key, $value);
					break;
				case NBT::TAG_Long:
					$data[$key] = new LongTag($key, $value);
					break;
				case NBT::TAG_Float:
					$data[$key] = new FloatTag($key, $value);
					break;
				case NBT::TAG_Double:
					$data[$key] = new DoubleTag($key, $value);
					break;
				case NBT::TAG_ByteArray:
					$data[$key] = new ByteArrayTag($key, $value);
					break;
				case NBT::TAG_String:
					$data[$key] = new StringTag($key, $value);
					break;
				case NBT::TAG_List:
					$data[$key] = new ListTag($key, $value);
					break;
				case NBT::TAG_Compound:
					$data[$key] = new CompoundTag($key, $value);
					break;
				case NBT::TAG_IntArray:
					$data[$key] = new IntArrayTag($key, $value);
					break;
			}
		}

		return $data;
	}

	private static function readValue($data, &$offset, &$type = null){
		$value = "";
		$type = null;
		$inQuotes = false;

		$len = strlen($data);
		for(; $offset < $len; ++$offset){
			$c = $data{$offset};

			if(!$inQuotes and ($c === " " or $c === "\r" or $c === "\n" or $c === "\t" or $c === "," or $c === "}" or $c === "]")){
				if($c === "," or $c === "}" or $c === "]"){
					break;
				}
			}elseif($c === '"'){
				$inQuotes = !$inQuotes;
				if($type === null){
					$type = self::TAG_String;
				}elseif($inQuotes){
					throw new \Exception("Syntax error: invalid quote at offset $offset");
				}
			}elseif($c === "\\"){
				$value .= $data{$offset + 1} ?? "";
				++$offset;
			}elseif($c === "{" and !$inQuotes){
				if($value !== ""){
					throw new \Exception("Syntax error: invalid compound start at offset $offset");
				}
				++$offset;
				$value = self::parseCompound($data, $offset);
				$type = self::TAG_Compound;
				break;
			}elseif($c === "[" and !$inQuotes){
				if($value !== ""){
					throw new \Exception("Syntax error: invalid list start at offset $offset");
				}
				++$offset;
				$value = self::parseList($data, $offset);
				$type = self::TAG_List;
				break;
			}else{
				$value .= $c;
			}
		}

		if($value === ""){
			throw new \Exception("Syntax error: invalid empty value at offset $offset");
		}

		if($type === null and strlen($value) > 0){
			$value = trim($value);
			$last = strtolower(substr($value, -1));
			$part = substr($value, 0, -1);

			if($last !== "b" and $last !== "s" and $last !== "l" and $last !== "f" and $last !== "d"){
				$part = $value;
				$last = null;
			}

			if($last !== "f" and $last !== "d" and ((string) ((int) $part)) === $part){
				if($last === "b"){
					$type = self::TAG_Byte;
				}elseif($last === "s"){
					$type = self::TAG_Short;
				}elseif($last === "l"){
					$type = self::TAG_Long;
				}else{
					$type = self::TAG_Int;
				}
				$value = (int) $part;
			}elseif(is_numeric($part)){
				if($last === "f" or $last === "d" or strpos($part, ".") !== false){
					if($last === "f"){
						$type = self::TAG_Float;
					}elseif($last === "d"){
						$type = self::TAG_Double;
					}else{
						$type = self::TAG_Float;
					}
					$value = (float) $part;
				}else{
					if($last === "l"){
						$type = self::TAG_Long;
					}else{
						$type = self::TAG_Int;
					}

					$value = $part;
				}
			}else{
				$type = self::TAG_String;
			}
		}

		return $value;
	}

	private static function readKey($data, &$offset){
		$key = "";

		$len = strlen($data);
		for(; $offset < $len; ++$offset){
			$c = $data{$offset};

			if($c === ":"){
				++$offset;
				break;
			}elseif($c !== " " and $c !== "\r" and $c !== "\n" and $c !== "\t" and $c !== "\""){
				$key .= $c;
			}
		}

		if($key === ""){
			throw new \Exception("Syntax error: invalid empty key at offset $offset");
		}

		return $key;
	}

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

	public function read($buffer, $doMultiple = false, bool $network = false){
		$this->offset = 0;
		$this->buffer = $buffer;
		$this->data = $this->readTag($network);
		if($doMultiple and $this->offset < strlen($this->buffer)){
			$this->data = [$this->data];
			do{
				$this->data[] = $this->readTag($network);
			}while($this->offset < strlen($this->buffer));
		}
		$this->buffer = "";
	}

	public function readCompressed($buffer, $compression = ZLIB_ENCODING_GZIP){
		$this->read(zlib_decode($buffer));
	}

	public function readNetworkCompressed($buffer, $compression = ZLIB_ENCODING_GZIP){
		$this->read(zlib_decode($buffer), false, true);
	}


	/**
	 * @return string|bool
	 */
	public function write(bool $network = false){
		$this->offset = 0;
		$this->buffer = "";

		if($this->data instanceof CompoundTag){
			$this->writeTag($this->data, $network);

			return $this->buffer;
		}elseif(is_array($this->data)){
			foreach($this->data as $tag){
				$this->writeTag($tag, $network);
			}
			return $this->buffer;
		}

		return false;
	}

	public function writeCompressed($compression = ZLIB_ENCODING_GZIP, $level = 7){
		if(($write = $this->write()) !== false){
			return zlib_encode($write, $compression, $level);
		}

		return false;
	}

	public function writeNetworkCompressed($compression = ZLIB_ENCODING_GZIP, $level = 7){
		if(($write = $this->write(true)) !== false){
			return zlib_encode($write, $compression, $level);
		}

		return false;
	}

	public function readTag(bool $network = false){
		if($this->feof()){
			$tagType = -1; //prevent crashes for empty tags
		}else{
			$tagType = $this->getByte();
		}
		switch($tagType){
			case NBT::TAG_Byte:
				$tag = new ByteTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_Short:
				$tag = new ShortTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_Int:
				$tag = new IntTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_Long:
				$tag = new LongTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_Float:
				$tag = new FloatTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_Double:
				$tag = new DoubleTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_ByteArray:
				$tag = new ByteArrayTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_String:
				$tag = new StringTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_List:
				$tag = new ListTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_Compound:
				$tag = new CompoundTag($this->getString($network));
				$tag->read($this, $network);
				break;
			case NBT::TAG_IntArray:
				$tag = new IntArrayTag($this->getString($network));
				$tag->read($this, $network);
				break;

			case NBT::TAG_End: //No named tag
			default:
				$tag = new EndTag;
				break;
		}
		return $tag;
	}

	public function writeTag(Tag $tag, bool $network = false){
		$this->putByte($tag->getType());
		if($tag instanceof NamedTAG){
			$this->putString($tag->getName(), $network);
		}
		$tag->write($this, $network);
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

	public function getInt(bool $network = false){
		if($network === true){
			return Binary::readVarInt($this);
		}
		return $this->endianness === self::BIG_ENDIAN ? Binary::readInt($this->get(4)) : Binary::readLInt($this->get(4));
	}

	public function putInt($v, bool $network = false){
		if($network === true){
			$this->buffer .= Binary::writeVarInt($v);
		}else{
			$this->buffer .= $this->endianness === self::BIG_ENDIAN ? Binary::writeInt($v) : Binary::writeLInt($v);
		}
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

	public function getString(bool $network = false){
		$len = $network ? $this->getByte() : $this->getShort();
		return $this->get($len);
	}

	public function putString($v, bool $network = false){
		if($network === true){
			$this->putByte(strlen($v));
		}else{
			$this->putShort(strlen($v));
		}
		$this->buffer .= $v;
	}

	public function getArray(){
		$data = [];
		self::toArray($data, $this->data);
	}

	private static function toArray(array &$data, Tag $tag){
		/** @var CompoundTag[]|ListTag[]|IntArrayTag[] $tag */
		foreach($tag as $key => $value){
			if($value instanceof CompoundTag or $value instanceof ListTag or $value instanceof IntArrayTag){
				$data[$key] = [];
				self::toArray($data[$key], $value);
			}else{
				$data[$key] = $value->getValue();
			}
		}
	}

	public static function fromArrayGuesser($key, $value){
		if(is_int($value)){
			return new IntTag($key, $value);
		}elseif(is_float($value)){
			return new FloatTag($key, $value);
		}elseif(is_string($value)){
			return new StringTag($key, $value);
		}elseif(is_bool($value)){
			return new ByteTag($key, $value ? 1 : 0);
		}

		return null;
	}

	private static function fromArray(Tag $tag, array $data, callable $guesser){
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
				$tag{$key} = $isNumeric ? ($isIntArray ? new IntArrayTag($key, []) : new ListTag($key, [])) : new CompoundTag($key, []);
				self::fromArray($tag->{$key}, $value, $guesser);
			}else{
				$v = call_user_func($guesser, $key, $value);
				if($v instanceof Tag){
					$tag{$key} = $v;
				}
			}
		}
	}

	public function setArray(array $data, callable $guesser = null){
		$this->data = new CompoundTag("", []);
		self::fromArray($this->data, $data, $guesser === null ? [self::class, "fromArrayGuesser"] : $guesser);
	}

	/**
	 * @return CompoundTag|array
	 */
	public function getData(){
		return $this->data;
	}

	/**
	 * @param CompoundTag|array $data
	 */
	public function setData($data){
		$this->data = $data;
	}

}