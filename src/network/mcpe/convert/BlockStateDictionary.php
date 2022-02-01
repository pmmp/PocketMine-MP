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

use pocketmine\data\bedrock\blockstate\BlockStateData;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use function array_map;

/**
 * Handles translation of network block runtime IDs into blockstate data, and vice versa
 */
final class BlockStateDictionary{

	private BlockStateLookupCache $lookupCache;

	/**
	 * @param BlockStateData[]             $states
	 *
	 * @phpstan-param list<BlockStateData> $states
	 */
	public function __construct(
		private array $states
	){
		$this->lookupCache = new BlockStateLookupCache($this->states);
	}

	public function getDataFromStateId(int $networkRuntimeId) : ?BlockStateData{
		return $this->states[$networkRuntimeId] ?? null;
	}

	/**
	 * Searches for the appropriate state ID which matches the given blockstate NBT.
	 * Returns null if there were no matches.
	 */
	public function lookupStateIdFromData(BlockStateData $data) : ?int{
		return $this->lookupCache->lookupStateId($data);
	}

	/**
	 * Returns an array mapping runtime ID => blockstate data.
	 * @return BlockStateData[]
	 * @phpstan-return array<int, BlockStateData>
	 */
	public function getStates() : array{ return $this->states; }

	public static function loadFromString(string $contents) : self{
		return new self(array_map(
			fn(TreeRoot $root) => BlockStateData::fromNbt($root->mustGetCompoundTag()),
			(new NetworkNbtSerializer())->readMultiple($contents)
		));
	}
}
