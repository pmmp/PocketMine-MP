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
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;

abstract class NBT{

	public const LITTLE_ENDIAN = 0;
	public const BIG_ENDIAN = 1;
	public const TAG_End = 0;
	public const TAG_Byte = 1;
	public const TAG_Short = 2;
	public const TAG_Int = 3;
	public const TAG_Long = 4;
	public const TAG_Float = 5;
	public const TAG_Double = 6;
	public const TAG_ByteArray = 7;
	public const TAG_String = 8;
	public const TAG_List = 9;
	public const TAG_Compound = 10;
	public const TAG_IntArray = 11;

	/**
	 * @param int $type
	 *
	 * @return Tag
	 */
	public static function createTag(int $type) : Tag{
		switch($type){
			case self::TAG_End:
				return new EndTag();
			case self::TAG_Byte:
				return new ByteTag();
			case self::TAG_Short:
				return new ShortTag();
			case self::TAG_Int:
				return new IntTag();
			case self::TAG_Long:
				return new LongTag();
			case self::TAG_Float:
				return new FloatTag();
			case self::TAG_Double:
				return new DoubleTag();
			case self::TAG_ByteArray:
				return new ByteArrayTag();
			case self::TAG_String:
				return new StringTag();
			case self::TAG_List:
				return new ListTag();
			case self::TAG_Compound:
				return new CompoundTag();
			case self::TAG_IntArray:
				return new IntArrayTag();
			default:
				throw new \InvalidArgumentException("Unknown NBT tag type $type");
		}
	}

	public static function matchList(ListTag $tag1, ListTag $tag2) : bool{
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

	public static function matchTree(CompoundTag $tag1, CompoundTag $tag2) : bool{
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
}
