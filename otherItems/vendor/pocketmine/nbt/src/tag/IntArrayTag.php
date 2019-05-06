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

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;
use pocketmine\nbt\NBTStream;
use pocketmine\nbt\ReaderTracker;
use function assert;
use function get_class;
use function implode;
use function is_int;
use function str_repeat;

#include <rules/NBT.h>

class IntArrayTag extends NamedTag{
	/** @var int[] */
	private $value;

	/**
	 * @param string $name
	 * @param int[]  $value
	 */
	public function __construct(string $name = "", array $value = []){
		parent::__construct($name);

		assert((function() use(&$value){
			foreach($value as $v){
				if(!is_int($v)){
					return false;
				}
			}

			return true;
		})());

		$this->value = $value;
	}

	public function getType() : int{
		return NBT::TAG_IntArray;
	}

	public function read(NBTStream $nbt, ReaderTracker $tracker) : void{
		$this->value = $nbt->getIntArray();
	}

	public function write(NBTStream $nbt) : void{
		$nbt->putIntArray($this->value);
	}

	public function toString(int $indentation = 0) : string{
		return str_repeat("  ", $indentation) . get_class($this) . ": " . ($this->__name !== "" ? "name='$this->__name', " : "") . "value=[" . implode(",", $this->value) . "]";
	}

	/**
	 * @return int[]
	 */
	public function getValue() : array{
		return $this->value;
	}
}
