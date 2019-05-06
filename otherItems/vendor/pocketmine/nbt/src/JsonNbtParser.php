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

namespace pocketmine\nbt;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\BinaryStream;
use function is_numeric;
use function strpos;
use function strtolower;
use function substr;
use function trim;

class JsonNbtParser{

	/**
	 * Parses JSON-formatted NBT into a CompoundTag and returns it. Used for parsing tags supplied with the /give command.
	 *
	 * @param string $data
	 *
	 * @return CompoundTag|null
	 *
	 * @throws \Exception
	 */
	public static function parseJson(string $data){
		$stream = new BinaryStream(trim($data, " \r\n\t"));

		if(($b = $stream->get(1)) !== "{"){
			throw new \UnexpectedValueException("Syntax error: expected compound start but got '$b'");
		}
		$ret = self::parseCompound($stream, ""); //don't return directly, syntax needs to be validated
		if(!$stream->feof()){
			throw new \UnexpectedValueException("Syntax error: unexpected trailing characters after end of tag: " . $stream->getRemaining());
		}

		return $ret;
	}

	/**
	 * @param BinaryStream $stream
	 * @param string       $name
	 *
	 * @return ListTag
	 * @throws \Exception
	 */
	private static function parseList(BinaryStream $stream, string $name = "") : ListTag{
		$retval = new ListTag($name);

		if(self::skipWhitespace($stream, "]")){
			while(!$stream->feof()){
				$retval->push(self::readValue($stream));
				if(self::readBreak($stream, "]")){
					return $retval;
				}
			}

			throw new \UnexpectedValueException("Syntax error: unexpected end of stream reading tag '$name'");
		}

		return $retval;
	}

	/**
	 * @param BinaryStream $stream
	 * @param string       $name
	 *
	 * @return CompoundTag
	 * @throws \Exception
	 */
	private static function parseCompound(BinaryStream $stream, string $name = "") : CompoundTag{
		$retval = new CompoundTag($name);

		if(self::skipWhitespace($stream, "}")){
			while(!$stream->feof()){
				$retval->setTag(self::readValue($stream, self::readKey($stream)));

				if(self::readBreak($stream, "}")){
					return $retval;
				}
			}

			throw new \UnexpectedValueException("Syntax error: unexpected end of stream reading tag '$name'");
		}

		return $retval;
	}

	private static function skipWhitespace(BinaryStream $stream, string $terminator) : bool{
		while(!$stream->feof()){
			$b = $stream->get(1);
			if($b === $terminator){
				return false;
			}
			if($b === " " or $b === "\n" or $b === "\t" or $b === "\r"){
				continue;
			}

			--$stream->offset;
			return true;
		}

		throw new \UnexpectedValueException("Syntax error: unexpected end of stream, expected start of key");
	}

	/**
	 * @param BinaryStream $stream
	 * @param string       $terminator
	 *
	 * @return bool true if terminator has been found, false if comma was found
	 * @throws \UnexpectedValueException if something else was found
	 */
	private static function readBreak(BinaryStream $stream, string $terminator) : bool{
		if($stream->feof()){
			throw new \UnexpectedValueException("Syntax error: unexpected end of stream, expected '$terminator'");
		}
		$offset = $stream->offset;
		$c = $stream->get(1);
		if($c === ","){
			return false;
		}
		if($c === $terminator){
			return true;
		}

		throw new \UnexpectedValueException("Syntax error: unexpected '$c' end at offset $offset");
	}

	/**
	 * @param BinaryStream $stream
	 * @param string       $name
	 *
	 * @return NamedTag
	 * @throws \UnexpectedValueException
	 */
	private static function readValue(BinaryStream $stream, string $name = "") : NamedTag{
		$value = "";
		$inQuotes = false;

		$offset = $stream->offset;

		$foundEnd = false;

		/** @var NamedTag|null $retval */
		$retval = null;

		while(!$stream->feof()){
			$offset = $stream->offset;
			$c = $stream->get(1);

			if($inQuotes){ //anything is allowed inside quotes, except unescaped quotes
				if($c === '"'){
					$inQuotes = false;
					$foundEnd = true;
				}elseif($c === "\\"){
					$value .= $stream->get(1);
				}else{
					$value .= $c;
				}
			}else{
				if($c === "," or $c === "}" or $c === "]"){ //end of parent tag
					$stream->offset--; //the caller needs to be able to read this character
					$foundEnd = true;
					break;
				}

				if($value === "" or $foundEnd){
					if($c === "\r" or $c === "\n" or $c === "\t" or $c === " "){ //leading or trailing whitespace, ignore it
						continue;
					}

					if($foundEnd){ //unexpected non-whitespace character after end of value
						throw new \UnexpectedValueException("Syntax error: unexpected '$c' after end of value at offset $offset");
					}
				}

				if($c === '"'){ //start of quoted string
					if($value !== ""){
						throw new \UnexpectedValueException("Syntax error: unexpected quote at offset $offset");
					}
					$inQuotes = true;

				}elseif($c === "{"){ //start of compound tag
					if($value !== ""){
						throw new \UnexpectedValueException("Syntax error: unexpected compound start at offset $offset (enclose in double quotes for literal)");
					}

					$retval = self::parseCompound($stream, $name);
					$foundEnd = true;

				}elseif($c === "["){ //start of list tag - TODO: arrays
					if($value !== ""){
						throw new \UnexpectedValueException("Syntax error: unexpected list start at offset $offset (enclose in double quotes for literal)");
					}

					$retval = self::parseList($stream, $name);
					$foundEnd = true;

				}else{ //any other character
					$value .= $c;
				}
			}
		}

		if($retval !== null){
			return $retval;
		}

		if($value === ""){
			throw new \UnexpectedValueException("Syntax error: empty value at offset $offset");
		}
		if(!$foundEnd){
			throw new \UnexpectedValueException("Syntax error: unexpected end of stream at offset $offset");
		}

		$last = strtolower(substr($value, -1));
		$part = substr($value, 0, -1);

		if($last !== "b" and $last !== "s" and $last !== "l" and $last !== "f" and $last !== "d"){
			$part = $value;
			$last = null;
		}

		if(is_numeric($part)){
			if($last === "f" or $last === "d" or strpos($part, ".") !== false or strpos($part, "e") !== false){ //e = scientific notation
				$value = (float) $part;
				switch($last){
					case "d":
						return new DoubleTag($name, $value);
					case "f":
					default:
						return new FloatTag($name, $value);
				}
			}else{
				$value = (int) $part;
				switch($last){
					case "b":
						return new ByteTag($name, $value);
					case "s":
						return new ShortTag($name, $value);
					case "l":
						return new LongTag($name, $value);
					default:
						return new IntTag($name, $value);
				}
			}
		}else{
			return new StringTag($name, $value);
		}
	}

	/**
	 * @param BinaryStream $stream
	 *
	 * @return string
	 * @throws \Exception
	 */
	private static function readKey(BinaryStream $stream) : string{
		$key = "";
		$offset = $stream->offset;

		$inQuotes = false;
		$foundEnd = false;

		while(!$stream->feof()){
			$c = $stream->get(1);

			if($inQuotes){
				if($c === '"'){
					$inQuotes = false;
					$foundEnd = true;
				}elseif($c === "\\"){
					$key .= $stream->get(1);
				}else{
					$key .= $c;
				}
			}else{
				if($c === ":"){
					$foundEnd = true;
					break;
				}

				if($key === "" or $foundEnd){
					if($c === "\r" or $c === "\n" or $c === "\t" or $c === " "){ //leading or trailing whitespace, ignore it
						continue;
					}

					if($foundEnd){ //unexpected non-whitespace character after end of value
						throw new \UnexpectedValueException("Syntax error: unexpected '$c' after end of value at offset $offset");
					}
				}

				if($c === '"'){ //start of quoted string
					if($key !== ""){
						throw new \UnexpectedValueException("Syntax error: unexpected quote at offset $offset");
					}
					$inQuotes = true;

				}elseif($c === "{" or $c === "}" or $c === "[" or $c === "]" or $c === ","){
					throw new \UnexpectedValueException("Syntax error: unexpected '$c' at offset $offset (enclose in double quotes for literal)");
				}else{ //any other character
					$key .= $c;
				}
			}
		}

		if($key === ""){
			throw new \UnexpectedValueException("Syntax error: invalid empty key at offset $offset");
		}
		if(!$foundEnd){
			throw new \UnexpectedValueException("Syntax error: unexpected end of stream at offset $offset");
		}

		return $key;
	}
}
