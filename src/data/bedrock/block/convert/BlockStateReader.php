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

namespace pocketmine\data\bedrock\block\convert;

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use function array_keys;
use function count;
use function get_class;
use function implode;

final class BlockStateReader{

	/**
	 * @var Tag[]
	 * @phpstan-var array<string, Tag>
	 */
	private array $unusedStates;

	public function __construct(
		private BlockStateData $data
	){
		$this->unusedStates = $this->data->getStates();
	}

	public function missingOrWrongTypeException(string $name, ?Tag $tag) : BlockStateDeserializeException{
		return new BlockStateDeserializeException("Property \"$name\" " . ($tag !== null ? "has unexpected type " . get_class($tag) : "is missing"));
	}

	public function badValueException(string $name, string $stringifiedValue, ?string $reason = null) : BlockStateDeserializeException{
		return new BlockStateDeserializeException(
			"Property \"$name\" has unexpected value \"$stringifiedValue\"" . (
			$reason !== null ? " ($reason)" : ""
		));
	}

	/** @throws BlockStateDeserializeException */
	public function readBool(string $name) : bool{
		unset($this->unusedStates[$name]);
		$tag = $this->data->getState($name);
		if($tag instanceof ByteTag){
			switch($tag->getValue()){
				case 0: return false;
				case 1: return true;
				default: throw $this->badValueException($name, (string) $tag->getValue());
			}
		}
		throw $this->missingOrWrongTypeException($name, $tag);
	}

	/** @throws BlockStateDeserializeException */
	public function readInt(string $name) : int{
		unset($this->unusedStates[$name]);
		$tag = $this->data->getState($name);
		if($tag instanceof IntTag){
			return $tag->getValue();
		}
		throw $this->missingOrWrongTypeException($name, $tag);
	}

	/** @throws BlockStateDeserializeException */
	public function readBoundedInt(string $name, int $min, int $max) : int{
		$result = $this->readInt($name);
		if($result < $min || $result > $max){
			throw $this->badValueException($name, (string) $result, "Must be inside the range $min ... $max");
		}
		return $result;
	}

	/** @throws BlockStateDeserializeException */
	public function readString(string $name) : string{
		unset($this->unusedStates[$name]);
		//TODO: only allow a specific set of values (strings are primarily used for enums)
		$tag = $this->data->getState($name);
		if($tag instanceof StringTag){
			return $tag->getValue();
		}
		throw $this->missingOrWrongTypeException($name, $tag);
	}

	/**
	 * Explicitly mark a property as unused, so it doesn't get flagged as an error when debug mode is enabled
	 */
	public function ignored(string $name) : void{
		if($this->data->getState($name) !== null){
			unset($this->unusedStates[$name]);
		}else{
			throw $this->missingOrWrongTypeException($name, null);
		}
	}

	/**
	 * Used to mark unused properties that haven't been implemented yet
	 */
	public function todo(string $name) : void{
		$this->ignored($name);
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function checkUnreadProperties() : void{
		if(count($this->unusedStates) > 0){
			throw new BlockStateDeserializeException("Unread properties: " . implode(", ", array_keys($this->unusedStates)));
		}
	}
}
