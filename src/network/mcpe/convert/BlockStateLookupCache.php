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

namespace pocketmine\network\mcpe\convert;

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\utils\Utils;
use function array_key_first;
use function count;

/**
 * Facilitates quickly looking up a block's state ID based on its NBT.
 */
final class BlockStateLookupCache{

	/**
	 * @var int[][]
	 * @phpstan-var array<string, array<int, BlockStateData>>
	 */
	private array $nameToNetworkIdsLookup = [];

	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $nameToSingleNetworkIdLookup = [];

	/**
	 * @param BlockStateData[] $blockStates
	 * @phpstan-param list<BlockStateData> $blockStates
	 */
	public function __construct(array $blockStates){
		foreach($blockStates as $stateId => $stateNbt){
			$this->nameToNetworkIdsLookup[$stateNbt->getName()][$stateId] = $stateNbt;
		}

		//setup fast path for stateless blocks
		foreach(Utils::stringifyKeys($this->nameToNetworkIdsLookup) as $name => $stateIds){
			if(count($stateIds) === 1){
				$this->nameToSingleNetworkIdLookup[$name] = array_key_first($stateIds);
			}
		}
	}

	/**
	 * Searches for the appropriate state ID which matches the given blockstate NBT.
	 * Returns null if there were no matches.
	 */
	public function lookupStateId(BlockStateData $data) : ?int{
		$name = $data->getName();

		if(isset($this->nameToSingleNetworkIdLookup[$name])){
			return $this->nameToSingleNetworkIdLookup[$name];
		}

		if(isset($this->nameToNetworkIdsLookup[$name])){
			$states = $data->getStates();
			foreach($this->nameToNetworkIdsLookup[$name] as $stateId => $stateNbt){
				if($stateNbt->getStates()->equals($states)){
					return $stateId;
				}
			}
		}

		return null;
	}
}
