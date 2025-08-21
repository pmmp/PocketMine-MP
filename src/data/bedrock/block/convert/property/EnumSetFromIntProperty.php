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

use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\bedrock\block\convert\EnumFromIntStateMap;
use pocketmine\utils\AssumptionFailedError;

/**
 * @phpstan-template TBlock of object
 * @phpstan-template TEnum of \UnitEnum
 * @phpstan-implements Property<TBlock>
 */
final class EnumSetFromIntProperty implements Property{

	private int $maxValue = 0;

	/**
	 * @phpstan-param EnumFromIntStateMap<TEnum> $map
	 * @phpstan-param \Closure(TBlock) : array<TEnum> $getter
	 * @phpstan-param \Closure(TBlock, array<TEnum>) : mixed $setter
	 */
	public function __construct(
		private string $name,
		private EnumFromIntStateMap $map,
		private \Closure $getter,
		private \Closure $setter
	){
		$flagsToCases = $this->map->getValueToEnum();
		foreach($flagsToCases as $possibleFlag => $option){
			if(($this->maxValue & $possibleFlag) !== 0){
				foreach($flagsToCases as $otherFlag => $otherOption){
					if(($possibleFlag & $otherFlag) === $otherFlag && $otherOption !== $option){
						throw new \InvalidArgumentException("Flag for $option->name overlaps with other flag $otherOption->name in property $this->name");
					}
				}

				throw new AssumptionFailedError("Unreachable");
			}

			$this->maxValue |= $possibleFlag;
		}
	}

	public function getName() : string{ return $this->name; }

	public function deserialize(object $block, BlockStateReader $in) : void{
		$flags = $in->readBoundedInt($this->name, 0, $this->maxValue);

		$value = [];
		foreach($this->map->getValueToEnum() as $possibleFlag => $option){
			if(($flags & $possibleFlag) === $possibleFlag){
				$value[] = $option;
			}
		}

		($this->setter)($block, $value);
	}

	public function serialize(object $block, BlockStateWriter $out) : void{
		$flags = 0;

		$value = ($this->getter)($block);
		foreach($value as $option){
			$flags |= $this->map->enumToValue($option);
		}

		$out->writeInt($this->name, $flags);
	}
}
