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
		return PHP_INT_SIZE === 8 ? (ord($c{0}) << 56 >> 56) : (ord($c{0}) << 24 >> 24);
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
		if(PHP_INT_SIZE === 8){
			return unpack("n", $str)[1] << 48 >> 48;
		}else{
			return unpack("n", $str)[1] << 16 >> 16;
		}
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
		if(PHP_INT_SIZE === 8){
			return unpack("v", $str)[1] << 48 >> 48;
		}else{
			return unpack("v", $str)[1] << 16 >> 16;
		}
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
		if(PHP_INT_SIZE === 8){
			return unpack("N", $str)[1] << 32 >> 32;
		}else{
			return unpack("N", $str)[1];
		}
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
		if(PHP_INT_SIZE === 8){
			return unpack("V", $str)[1] << 32 >> 32;
		}else{
			return unpack("V", $str)[1];
		}
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
		return (ENDIANNESS === self::BIG_ENDIAN ? unpack("f", $str)[1] : unpack("f", strrev($str))[1]);
	}

	/**
	 * Reads a 4-byte floating-point number, rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedFloat(string $str, int $accuracy){
		return round(self::readFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte floating-point number.
	 *
	 * @param float $value
	 * @return string
	 */
	public static function writeFloat(float $value) : string{
		return ENDIANNESS === self::BIG_ENDIAN ? pack("f", $value) : strrev(pack("f", $value));
	}

	/**
	 * Reads a 4-byte little-endian floating-point number.
	 *
	 * @param string $str
	 * @return float
	 */
	public static function readLFloat(string $str) : float{
		self::checkLength($str, 4);
		return (ENDIANNESS === self::BIG_ENDIAN ? unpack("f", strrev($str))[1] : unpack("f", $str)[1]);
	}

	/**
	 * Reads a 4-byte little-endian floating-point number rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedLFloat(string $str, int $accuracy){
		return round(self::readLFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte little-endian floating-point number.
	 *
	 * @param float $value
	 * @return string
	 */
	public static function writeLFloat(float $value) : string{
		return ENDIANNESS === self::BIG_ENDIAN ? strrev(pack("f", $value)) : pack("f", $value);
	}

	/**
	 * Returns a printable floating-point number.
	 *
	 * @param float $value
	 * @return string
	 */
	public static function printFloat(float $value){
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
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("d", $str)[1] : unpack("d", strrev($str))[1];
	}

	/**
	 * Writes an 8-byte floating-point number.
	 *
	 * @param float $value
	 * @return string
	 */
	public static function writeDouble(float $value) : string{
		return ENDIANNESS === self::BIG_ENDIAN ? pack("d", $value) : strrev(pack("d", $value));
	}

	/**
	 * Reads an 8-byte little-endian floating-point number.
	 *
	 * @param string $str
	 * @return float
	 */
	public static function readLDouble(string $str) : float{
		self::checkLength($str, 8);
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("d", strrev($str))[1] : unpack("d", $str)[1];
	}

	/**
	 * Writes an 8-byte floating-point little-endian number.
	 * @param float $value
	 * @return string
	 */
	public static function writeLDouble(float $value) : string{
		return ENDIANNESS === self::BIG_ENDIAN ? strrev(pack("d", $value)) : pack("d", $value);
	}

	/**
	 * Reads an 8-byte integer.
	 * Note that this method will return a string on 32-bit PHP.
	 *
	 * @param string $x
	 * @return int|string
	 */
	public static function readLong(string $x){
		self::checkLength($x, 8);
		if(PHP_INT_SIZE === 8){
			$int = unpack("N*", $x);
			return ($int[1] << 32) | $int[2];
		}else{
			$value = "0";
			for($i = 0; $i < 8; $i += 2){
				$value = bcmul($value, "65536", 0);
				$value = bcadd($value, self::readShort(substr($x, $i, 2)), 0);
			}

			if(bccomp($value, "9223372036854775807") == 1){
				$value = bcadd($value, "-18446744073709551616");
			}

			return $value;
		}
	}

	/**
	 * Writes an 8-byte integer.
	 *
	 * @param int|string $value
	 * @return string
	 */
	public static function writeLong($value) : string{
		if(PHP_INT_SIZE === 8){
			return pack("NN", $value >> 32, $value & 0xFFFFFFFF);
		}else{
			$x = "";

			if(bccomp($value, "0") == -1){
				$value = bcadd($value, "18446744073709551616");
			}

			$x .= self::writeShort(bcmod(bcdiv($value, "281474976710656"), "65536"));
			$x .= self::writeShort(bcmod(bcdiv($value, "4294967296"), "65536"));
			$x .= self::writeShort(bcmod(bcdiv($value, "65536"), "65536"));
			$x .= self::writeShort(bcmod($value, "65536"));

			return $x;
		}
	}

	/**
	 * Reads an 8-byte little-endian integer.
	 *
	 * @param string $str
	 * @return int|string
	 */
	public static function readLLong(string $str){
		return self::readLong(strrev($str));
	}

	/**
	 * Writes an 8-byte little-endian integer.
	 *
	 * @param int|string $value
	 * @return string
	 */
	public static function writeLLong($value) : string{
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
		$shift = PHP_INT_SIZE === 8 ? 63 : 31;
		$raw = self::readUnsignedVarInt($buffer, $offset);
		$temp = ((($raw << $shift) >> $shift) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << $shift));
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
		if(PHP_INT_SIZE === 8){
			$v = ($v << 32 >> 32);
		}
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
	 * Reads a 64-bit zigzag-encoded variable-length integer from the supplied stream.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int|string
	 */
	public static function readVarLong(string $buffer, int &$offset){
		if(PHP_INT_SIZE === 8){
			return self::readVarLong_64($buffer, $offset);
		}else{
			return self::readVarLong_32($buffer, $offset);
		}
	}

	/**
	 * Legacy BC Math zigzag VarLong reader. Will work on 32-bit or 64-bit, but will be slower than the regular 64-bit method.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return string
	 */
	public static function readVarLong_32(string $buffer, int &$offset) : string{
		/** @var string $raw */
		$raw = self::readUnsignedVarLong_32($buffer, $offset);
		$result = bcdiv($raw, "2");
		if(bcmod($raw, "2") === "1"){
			$result = bcsub(bcmul($result, "-1"), "1");
		}

		return $result;
	}

	/**
	 * 64-bit zizgag VarLong reader.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 */
	public static function readVarLong_64(string $buffer, int &$offset) : int{
		$raw = self::readUnsignedVarLong_64($buffer, $offset);
		$temp = ((($raw << 63) >> 63) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << 63));
	}

	/**
	 * Reads an unsigned VarLong from the supplied stream.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int|string
	 */
	public static function readUnsignedVarLong(string $buffer, int &$offset){
		if(PHP_INT_SIZE === 8){
			return self::readUnsignedVarLong_64($buffer, $offset);
		}else{
			return self::readUnsignedVarLong_32($buffer, $offset);
		}
	}

	/**
	 * Legacy BC Math unsigned VarLong reader.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return string
	 */
	public static function readUnsignedVarLong_32(string $buffer, int &$offset) : string{
		$value = "0";
		for($i = 0; $i <= 63; $i += 7){
			$b = ord($buffer{$offset++});
			$value = bcadd($value, bcmul($b & 0x7f, bcpow("2", "$i")));

			if(($b & 0x80) === 0){
				return $value;
			}elseif(!isset($buffer{$offset})){
				throw new \UnexpectedValueException("Expected more bytes, none left to read");
			}
		}

		throw new \InvalidArgumentException("VarLong did not terminate after 10 bytes!");
	}

	/**
	 * 64-bit unsigned VarLong reader.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 */
	public static function readUnsignedVarLong_64(string $buffer, int &$offset) : int{
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
	 * Writes a 64-bit integer as a variable-length long.
	 *
	 * @param int|string $v
	 * @return string up to 10 bytes
	 */
	public static function writeVarLong($v) : string{
		if(PHP_INT_SIZE === 8){
			return self::writeVarLong_64($v);
		}else{
			return self::writeVarLong_32($v);
		}
	}

	/**
	 * Legacy BC Math zigzag VarLong encoder.
	 *
	 * @param string $v
	 * @return string
	 */
	public static function writeVarLong_32(string $v) : string{
		$v = bcmod(bcmul($v, "2"), "18446744073709551616");
		if(bccomp($v, "0") == -1){
			$v = bcsub(bcmul($v, "-1"), "1");
		}

		return self::writeUnsignedVarLong_32($v);
	}

	/**
	 * 64-bit VarLong encoder.
	 *
	 * @param int $v
	 * @return string
	 */
	public static function writeVarLong_64(int $v) : string{
		return self::writeUnsignedVarLong_64(($v << 1) ^ ($v >> 63));
	}

	/**
	 * Writes a 64-bit integer as a variable-length long
	 *
	 * @param int|string $v
	 * @return string up to 10 bytes
	 */
	public static function writeUnsignedVarLong($v) : string{
		if(PHP_INT_SIZE === 8){
			return self::writeUnsignedVarLong_64($v);
		}else{
			return self::writeUnsignedVarLong_32($v);
		}
	}

	/**
	 * Legacy BC Math unsigned VarLong encoder.
	 *
	 * @param string $value
	 * @return string
	 */
	public static function writeUnsignedVarLong_32(string $value) : string{
		$buf = "";

		if(bccomp($value, "0") == -1){
			$value = bcadd($value, "18446744073709551616");
		}

		for($i = 0; $i < 10; ++$i){
			$byte = (int) bcmod($value, "128");
			$value = bcdiv($value, "128");
			if($value !== "0"){
				$buf .= chr($byte | 0x80);
			}else{
				$buf .= chr($byte);
				return $buf;
			}
		}

		throw new \InvalidArgumentException("Value too large to be encoded as a VarLong");
	}

	/**
	 * 64-bit unsigned VarLong encoder.
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeUnsignedVarLong_64(int $value) : string{
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
