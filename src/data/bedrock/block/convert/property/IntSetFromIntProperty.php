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
use pocketmine\data\bedrock\block\convert\IntFromRawStateMap;
use pocketmine\utils\AssumptionFailedError;

/**
 * @phpstan-template TBlock of object
 * @phpstan-implements Property<TBlock>
 */
final class IntSetFromIntProperty implements Property{

	private int $maxValue = 0;

	/**
	 * @phpstan-param IntFromRawStateMap<int> $map
	 * @phpstan-param \Closure(TBlock) : array<int> $getter
	 * @phpstan-param \Closure(TBlock, array<int>) : mixed $setter
	 */
	public function __construct(
		private string $name,
		private IntFromRawStateMap $map,
		private \Closure $getter,
		private \Closure $setter
	){
		$flagsToCases = $this->map->getDeserializeMap();
		foreach($flagsToCases as $possibleFlag => $option){
			if(($this->maxValue & $possibleFlag) !== 0){
				foreach($flagsToCases as $otherFlag => $otherOption){
					if(($possibleFlag & $otherFlag) === $otherFlag && $otherOption !== $option){
						throw new \InvalidArgumentException("Flag for option $option overlaps with flag for option $otherOption in property $this->name");
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
		foreach($this->map->getDeserializeMap() as $possibleFlag => $option){
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
			$flags |= $this->map->serialize($option);
		}

		$out->writeInt($this->name, $flags);
	}
}
