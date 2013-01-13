<?php
/**
 * Class for reading in NBT-format files.
 *
 * @author  Justin Martin <frozenfire@thefrozenfire.com>
 * @version 1.0
 * MODIFIED BY @shoghicp
 *
 * Dependencies:
 *  PHP 4.3+ (5.3+ recommended)
 */

class NBT {
	public $root = array();

	const TAG_END = 0;
	const TAG_BYTE = 1;
	const TAG_SHORT = 2;
	const TAG_INT = 3;
	const TAG_LONG = 4;
	const TAG_FLOAT = 5;
	const TAG_DOUBLE = 6;
	const TAG_BYTE_ARRAY = 7;
	const TAG_STRING = 8;
	const TAG_LIST = 9;
	const TAG_COMPOUND = 10;

	public function loadFile($filename) {
		if(is_file($filename)) {
			$fp = fopen($filename, "rb");
		}else{
			trigger_error("First parameter must be a filename", E_USER_WARNING);
			return false;
		}
		switch(basename($filename, ".dat")){
			case "level":
				$version = Utils::readLInt(fread($fp, 4));
				$lenght = Utils::readLInt(fread($fp, 4));
				break;
			case "entities":
				fread($fp, 12);
				break;
		}
		$this->traverseTag($fp, $this->root);
		return end($this->root);
	}

	public function traverseTag($fp, &$tree) {
		if(feof($fp)) {
			return false;
		}
		$tagType = $this->readType($fp, self::TAG_BYTE); // Read type byte.
		if($tagType == self::TAG_END) {
			return false;
		} else {
			$tagName = $this->readType($fp, self::TAG_STRING);
			$tagData = $this->readType($fp, $tagType);
			$tree[] = array("type"=>$tagType, "name"=>$tagName, "value"=>$tagData);
			return true;
		}
	}

	public function readType($fp, $tagType) {
		switch($tagType) {
			case self::TAG_BYTE: // Signed byte (8 bit)
				return Utils::readByte(fread($fp, 1));
			case self::TAG_SHORT: // Signed short (16 bit, big endian)
				return Utils::readLShort(fread($fp, 2));
			case self::TAG_INT: // Signed integer (32 bit, big endian)
				return Utils::readLInt(fread($fp, 4));
			case self::TAG_LONG: // Signed long (64 bit, big endian)
				return Utils::readLLong(fread($fp, 8));
			case self::TAG_FLOAT: // Floating point value (32 bit, big endian, IEEE 754-2008)
				return Utils::readLFloat(fread($fp, 4));
			case self::TAG_DOUBLE: // Double value (64 bit, big endian, IEEE 754-2008)
				return Utils::readLDouble(fread($fp, 8));
			case self::TAG_BYTE_ARRAY: // Byte array
				$arrayLength = $this->readType($fp, self::TAG_INT);
				$array = array();
				for($i = 0; $i < $arrayLength; $i++) $array[] = $this->readType($fp, self::TAG_BYTE);
				return $array;
			case self::TAG_STRING: // String
				if(!$stringLength = $this->readType($fp, self::TAG_SHORT)) return "";
				$string = fread($fp, $stringLength); // Read in number of bytes specified by string length, and decode from utf8.
				return $string;
			case self::TAG_LIST: // List
				$tagID = $this->readType($fp, self::TAG_BYTE);
				$listLength = $this->readType($fp, self::TAG_INT);
				$list = array("type"=>$tagID, "value"=>array());
				for($i = 0; $i < $listLength; $i++) {
					if(feof($fp)) break;
					$list["value"][] = $this->readType($fp, $tagID);
				}
				return $list;
			case self::TAG_COMPOUND: // Compound
				$tree = array();
				while($this->traverseTag($fp, $tree));
				return $tree;
		}
	}
}
?>