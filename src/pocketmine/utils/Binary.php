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

/**
 * Methods for working with binary strings
 */
namespace pocketmine\utils;


class Binary{
	const BIG_ENDIAN = 0x00;
	const LITTLE_ENDIAN = 0x01;

	public static function signByte(int $value) : int{
		return $value << 56 >> 56;
	}

	public static function unsignByte(int $value) : int{
		return $value & 0xff;
	}

	public static function signShort(int $value) : int{
		return $value << 48 >> 48;
	}

	public static function unsignShort(int $value) : int{
		return $value & 0xffff;
	}

	public static function signInt(int $value) : int{
		return $value << 32 >> 32;
	}

	public static function unsignInt(int $value) : int{
		return $value & 0xffffffff;
	}


	public static function flipShortEndianness(int $value) : int{
		return self::readLShort(self::writeShort($value));
	}

	public static function flipIntEndianness(int $value) : int{
		return self::readLInt(self::writeInt($value));
	}

	public static function flipLongEndianness(int $value) : int{
		return self::readLLong(self::writeLong($value));
	}


	private static function checkLength($str, $expect){
		assert(($len = strlen($str)) === $expect, "Expected $expect bytes, got $len");
	}

	/**
	 * Reads a byte boolean
	 *
	 * @param string $b
	 * @return bool
	 */
	public static function readBool(string $b) : bool{
		return $b !== "\x00";
	}

	/**
	 * Writes a byte boolean
	 *
	 * @param bool $b
	 * @return string
	 */
	public static function writeBool(bool $b) : string{
		return $b ? "\x01" : "\x00";
	}

	/**
	 * Reads an unsigned byte (0 - 255)
	 *
	 * @param string $c
	 * @return int
	 */
	public static function readByte(string $c) : int{
		self::checkLength($c, 1);
		return ord($c{0});
	}

	/**
	 * Reads a signed byte (-128 - 127)
	 *
	 * @param string $c
	 * @return int
	 */
	public static function readSignedByte(string $c) : int{
		return self::signByte(ord($c{0}));
	}

	/**
	 * Writes an unsigned/signed byte
	 *
	 * @param int $c
	 * @return string
	 */
	public static function writeByte(int $c) : string{
		return chr($c);
	}

	/**
	 * Reads a 16-bit unsigned big-endian number
	 *
	 * @param string $str
	 * @return int
	 */
	public static function readShort(string $str) : int{
		self::checkLength($str, 2);
		return unpack("n", $str)[1];
	}

	/**
	 * Reads a 16-bit signed big-endian number
	 *
	 * @param $str
	 *
	 * @return int
	 */
	public static function readSignedShort(string $str) : int{
		self::checkLength($str, 2);
		return self::signShort(unpack("n", $str)[1]);
	}

	/**
	 * Writes a 16-bit signed/unsigned big-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeShort(int $value) : string{
		return pack("n", $value);
	}

	/**
	 * Reads a 16-bit unsigned little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLShort(string $str) : int{
		self::checkLength($str, 2);
		return unpack("v", $str)[1];
	}

	/**
	 * Reads a 16-bit signed little-endian number
	 *
	 * @param      $str
	 *
	 * @return int
	 */
	public static function readSignedLShort(string $str) : int{
		self::checkLength($str, 2);
		return self::signShort(unpack("v", $str)[1]);
	}

	/**
	 * Writes a 16-bit signed/unsigned little-endian number
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function writeLShort(int $value) : string{
		return pack("v", $value);
	}

	/**
	 * Reads a 3-byte big-endian number
	 *
	 * @param string $str
	 * @return int
	 */
	public static function readTriad(string $str) : int{
		self::checkLength($str, 3);
		return unpack("N", "\x00" . $str)[1];
	}

	/**
	 * Writes a 3-byte big-endian number
	 *
	 * @param int $value
	 * @return string
	 */
	public static function writeTriad(int $value) : string{
		return substr(pack("N", $value), 1);
	}

	/**
	 * Reads a 3-byte little-endian number
	 *
	 * @param string $str
	 * @return int
	 */
	public static function readLTriad(string $str) : int{
		self::checkLength($str, 3);
		return unpack("V", $str . "\x00")[1];
	}

	/**
	 * Writes a 3-byte little-endian number
	 *
	 * @param int $value
	 * @return string
	 */
	public static function writeLTriad(int $value) : string{
		return substr(pack("V", $value), 0, -1);
	}

	/**
	 * Reads a 4-byte signed integer
	 *
	 * @param string $str
	 * @return int
	 */
	public static function readInt(string $str) : int{
		self::checkLength($str, 4);
		return self::signInt(unpack("N", $str)[1]);
	}

	/**
	 * Writes a 4-byte integer
	 *
	 * @param int $value
	 * @return string
	 */
	public static function writeInt(int $value) : string{
		return pack("N", $value);
	}

	/**
	 * Reads a 4-byte signed little-endian integer
	 *
	 * @param string $str
	 * @return int
	 */
	public static function readLInt(string $str) : int{
		self::checkLength($str, 4);
		return self::signInt(unpack("V", $str)[1]);
	}

	/**
	 * Writes a 4-byte signed little-endian integer
	 *
	 * @param int $value
	 * @return string
	 */
	public static function writeLInt(int $value) : string{
		return pack("V", $value);
	}

	/**
	 * Reads a 4-byte floating-point number
	 *
	 * @param string $str
	 * @return float
	 */
	public static function readFloat(string $str) : float{
		self::checkLength($str, 4);
		return unpack("G", $str)[1];
	}

	/**
	 * Reads a 4-byte floating-point number, rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedFloat(string $str, int $accuracy) : float{
		return round(self::readFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte floating-point number.
	 *
	 * @param float $value
	 * @return string
	 */
	public static function writeFloat(float $value) : string{
		return pack("G", $value);
	}

	/**
	 * Reads a 4-byte little-endian floating-point number.
	 *
	 * @param string $str
	 * @return float
	 */
	public static function readLFloat(string $str) : float{
		self::checkLength($str, 4);
		return unpack("g", $str)[1];
	}

	/**
	 * Reads a 4-byte little-endian floating-point number rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedLFloat(string $str, int $accuracy) : float{
		return round(self::readLFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte little-endian floating-point number.
	 *
	 * @param float $value
	 * @return string
	 */
	public static function writeLFloat(float $value) : string{
		return pack("g", $value);
	}

	/**
	 * Returns a printable floating-point number.
	 *
	 * @param float $value
	 * @return string
	 */
	public static function printFloat(float $value) : string{
		return preg_replace("/(\\.\\d+?)0+$/", "$1", sprintf("%F", $value));
	}

	/**
	 * Reads an 8-byte floating-point number.
	 *
	 * @param string $str
	 * @return float
	 */
	public static function readDouble(string $str) : float{
		self::checkLength($str, 8);
		return unpack("E", $str)[1];
	}

	/**
	 * Writes an 8-byte floating-point number.
	 *
	 * @param float $value
	 * @return string
	 */
	public static function writeDouble(float $value) : string{
		return pack("E", $value);
	}

	/**
	 * Reads an 8-byte little-endian floating-point number.
	 *
	 * @param string $str
	 * @return float
	 */
	public static function readLDouble(string $str) : float{
		self::checkLength($str, 8);
		return unpack("e", $str)[1];
	}

	/**
	 * Writes an 8-byte floating-point little-endian number.
	 * @param float $value
	 * @return string
	 */
	public static function writeLDouble(float $value) : string{
		return pack("e", $value);
	}

	/**
	 * Reads an 8-byte integer.
	 *
	 * @param string $x
	 * @return int
	 */
	public static function readLong(string $x) : int{
		self::checkLength($x, 8);
		$int = unpack("N*", $x);
		return ($int[1] << 32) | $int[2];
	}

	/**
	 * Writes an 8-byte integer.
	 *
	 * @param int $value
	 * @return string
	 */
	public static function writeLong(int $value) : string{
		return pack("NN", $value >> 32, $value & 0xFFFFFFFF);
	}

	/**
	 * Reads an 8-byte little-endian integer.
	 *
	 * @param string $str
	 * @return int
	 */
	public static function readLLong(string $str) : int{
		return self::readLong(strrev($str));
	}

	/**
	 * Writes an 8-byte little-endian integer.
	 *
	 * @param int $value
	 * @return string
	 */
	public static function writeLLong(int $value) : string{
		return strrev(self::writeLong($value));
	}


	/**
	 * Reads a 32-bit zigzag-encoded variable-length integer.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 */
	public static function readVarInt(string $buffer, int &$offset) : int{
		$raw = self::readUnsignedVarInt($buffer, $offset);
		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << 63));
	}

	/**
	 * Reads a 32-bit variable-length unsigned integer.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 *
	 * @throws \InvalidArgumentException if the var-int did not end after 5 bytes
	 */
	public static function readUnsignedVarInt(string $buffer, int &$offset) : int{
		$value = 0;
		for($i = 0; $i <= 35; $i += 7){
			$b = ord($buffer{$offset++});
			$value |= (($b & 0x7f) << $i);

			if(($b & 0x80) === 0){
				return $value;
			}elseif(!isset($buffer{$offset})){
				throw new \UnexpectedValueException("Expected more bytes, none left to read");
			}
		}

		throw new \InvalidArgumentException("VarInt did not terminate after 5 bytes!");
	}

	/**
	 * Writes a 32-bit integer as a zigzag-encoded variable-length integer.
	 *
	 * @param int $v
	 * @return string
	 */
	public static function writeVarInt(int $v) : string{
		$v = ($v << 32 >> 32);
		return self::writeUnsignedVarInt(($v << 1) ^ ($v >> 31));
	}

	/**
	 * Writes a 32-bit unsigned integer as a variable-length integer.
	 *
	 * @param int $value
	 * @return string up to 5 bytes
	 */
	public static function writeUnsignedVarInt(int $value) : string{
		$buf = "";
		$value &= 0xffffffff;
		for($i = 0; $i < 5; ++$i){
			if(($value >> 7) !== 0){
				$buf .= chr($value | 0x80);
			}else{
				$buf .= chr($value & 0x7f);
				return $buf;
			}

			$value = (($value >> 7) & (PHP_INT_MAX >> 6)); //PHP really needs a logical right-shift operator
		}

		throw new \InvalidArgumentException("Value too large to be encoded as a VarInt");
	}


	/**
	 * Reads a 64-bit zigzag-encoded variable-length integer.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 */
	public static function readVarLong(string $buffer, int &$offset) : int{
		$raw = self::readUnsignedVarLong($buffer, $offset);
		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << 63));
	}

	/**
	 * Reads a 64-bit unsigned variable-length integer.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 */
	public static function readUnsignedVarLong(string $buffer, int &$offset) : int{
		$value = 0;
		for($i = 0; $i <= 63; $i += 7){
			$b = ord($buffer{$offset++});
			$value |= (($b & 0x7f) << $i);

			if(($b & 0x80) === 0){
				return $value;
			}elseif(!isset($buffer{$offset})){
				throw new \UnexpectedValueException("Expected more bytes, none left to read");
			}
		}

		throw new \InvalidArgumentException("VarLong did not terminate after 10 bytes!");
	}

	/**
	 * Writes a 64-bit integer as a zigzag-encoded variable-length long.
	 *
	 * @param int $v
	 * @return string
	 */
	public static function writeVarLong(int $v) : string{
		return self::writeUnsignedVarLong(($v << 1) ^ ($v >> 63));
	}

	/**
	 * Writes a 64-bit unsigned integer as a variable-length long.
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeUnsignedVarLong(int $value) : string{
		$buf = "";
		for($i = 0; $i < 10; ++$i){
			if(($value >> 7) !== 0){
				$buf .= chr($value | 0x80); //Let chr() take the last byte of this, it's faster than adding another & 0x7f.
			}else{
				$buf .= chr($value & 0x7f);
				return $buf;
			}

			$value = (($value >> 7) & (PHP_INT_MAX >> 6)); //PHP really needs a logical right-shift operator
		}

		throw new \InvalidArgumentException("Value too large to be encoded as a VarLong");
	}
}
