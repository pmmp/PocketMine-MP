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


namespace pocketmine\utils;

/**
 * Projects a box of array with a key
 *
 * @template K the key type of the array
 */
final class ProjectionBox implements MutableBox{
	/**
	 * @var MutableBox
	 * @phpstan-var MutableBox<array<K, mixed>>
	 */
	private $parent;
	/**
	 * @var int|string
	 * @phpstan-var K
	 */
	private $key;

	/**
	 * ProjectionBox constructor.
	 * @param MutableBox $parent
	 * @param string|int $key
	 * @phpstan-param MutableBox<array<K, mixed>> $box
	 * @phpstan-param K $key
	 */
	public function __construct(MutableBox $parent, $key){
		$this->parent = $parent;
		$this->key = $key;
	}

	public function getValue(){
		return $this->parent->getValue()[$this->key];
	}

	public function setValue($value) : void{
		$parent = $this->parent->getValue();
		$parent[$this->key] = $value;
		$this->parent->setValue($parent);
	}

	public function hasChanged() : bool{
		return $this->parent->hasChanged();
	}

	public function setUnchanged() : void{
		$this->parent->setUnchanged();
	}
}
