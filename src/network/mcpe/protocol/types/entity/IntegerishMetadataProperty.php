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

namespace pocketmine\network\mcpe\protocol\types\entity;

trait IntegerishMetadataProperty{
	/** @var int */
	private $value;

	public function __construct(int $value){
		if($value < $this->min() or $value > $this->max()){
			throw new \InvalidArgumentException("Value is out of range " . $this->min() . " - " . $this->max());
		}
		$this->value = $value;
	}

	abstract protected function min() : int;

	abstract protected function max() : int;

	public function getValue() : int{
		return $this->value;
	}

	public function equals(MetadataProperty $other) : bool{
		return $other instanceof self and $other->value === $this->value;
	}

	/**
	 * @param bool[] $flags
	 * @phpstan-param array<int, bool> $flags
	 */
	public static function buildFromFlags(array $flags) : self{
		$value = 0;
		foreach($flags as $flag => $v){
			if($v){
				$value |= 1 << $flag;
			}
		}
		return new self($value);
	}
}
