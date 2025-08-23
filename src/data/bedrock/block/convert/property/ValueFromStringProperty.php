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

namespace pocketmine\data\bedrock\block\convert\property;

use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use function array_keys;
use function array_map;
use function strval;

/**
 * @phpstan-template TBlock of object
 * @phpstan-template TValue of int|\UnitEnum
 * @phpstan-implements StringProperty<TBlock>
 */
final class ValueFromStringProperty implements StringProperty{

	/**
	 * @phpstan-param StateMap<TValue, string>   $map
	 * @phpstan-param \Closure(TBlock) : TValue        $getter
	 * @phpstan-param \Closure(TBlock, TValue) : mixed $setter
	 */
	public function __construct(
		private string $name,
		private StateMap $map,
		private \Closure $getter,
		private \Closure $setter
	){}

	public function getName() : string{ return $this->name; }

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getPossibleValues() : array{
		//PHP sucks
		return array_map(strval(...), array_keys($this->map->getRawToValueMap()));
	}

	public function deserialize(object $block, BlockStateReader $in) : void{
		$this->deserializePlain($block, $in->readString($this->name));
	}

	public function deserializePlain(object $block, string $raw) : void{
		//TODO: duplicated code from BlockStateReader :(
		$value = $this->map->rawToValue($raw) ?? throw new BlockStateDeserializeException("Property \"$this->name\" has invalid value \"$raw\"");
		($this->setter)($block, $value);
	}

	public function serialize(object $block, BlockStateWriter $out) : void{
		$out->writeString($this->name, $this->serializePlain($block));
	}

	public function serializePlain(object $block) : string{
		$value = ($this->getter)($block);
		return $this->map->valueToRaw($value);
	}
}
