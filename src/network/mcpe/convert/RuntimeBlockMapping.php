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

use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\LegacyBlockIdToStringIdMap;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\utils\SingletonTrait;
use function file_get_contents;
use function getmypid;
use function mt_rand;
use function mt_srand;
use function shuffle;

/**
 * @internal
 */
final class RuntimeBlockMapping{
	use SingletonTrait;

	/** @var int[] */
	private $legacyToRuntimeMap = [];
	/** @var int[] */
	private $runtimeToLegacyMap = [];
	/** @var CompoundTag[] */
	private $bedrockKnownStates;
	/**
	 * @var CacheableNbt|null
	 * @phpstan-var CacheableNbt<\pocketmine\nbt\tag\ListTag>|null
	 */
	private $startGamePaletteCache = null;

	private function __construct(){
		$tag = (new NetworkNbtSerializer())->read(\pocketmine\resource_path()->join("vanilla/required_block_states.nbt")->getContents())->getTag();
		if(!($tag instanceof ListTag) or $tag->getTagType() !== NBT::TAG_Compound){ //this is a little redundant currently, but good for auto complete and makes phpstan happy
			throw new \RuntimeException("Invalid blockstates table, expected TAG_List<TAG_Compound> root");
		}

		/** @var CompoundTag[] $list */
		$list = $tag->getValue();
		$this->bedrockKnownStates = self::randomizeTable($list);

		$this->setupLegacyMappings();
	}

	private function setupLegacyMappings() : void{
		$legacyIdMap = LegacyBlockIdToStringIdMap::getInstance();
		/** @var R12ToCurrentBlockMapEntry[] $legacyStateMap */
		$legacyStateMap = [];
		$legacyStateMapReader = new PacketSerializer(file_get_contents(\pocketmine\resource_path()->join("vanilla/r12_to_current_block_map.bin")));
		$nbtReader = new NetworkNbtSerializer();
		while(!$legacyStateMapReader->feof()){
			$id = $legacyStateMapReader->getString();
			$meta = $legacyStateMapReader->getLShort();

			$offset = $legacyStateMapReader->getOffset();
			$state = $nbtReader->read($legacyStateMapReader->getBuffer(), $offset)->mustGetCompoundTag();
			$legacyStateMapReader->setOffset($offset);
			$legacyStateMap[] = new R12ToCurrentBlockMapEntry($id, $meta, $state);
		}

		/**
		 * @var int[][] $idToStatesMap string id -> int[] list of candidate state indices
		 */
		$idToStatesMap = [];
		foreach($this->bedrockKnownStates as $k => $state){
			$idToStatesMap[$state->getCompoundTag("block")->getString("name")][] = $k;
		}
		foreach($legacyStateMap as $pair){
			$id = $legacyIdMap->stringToLegacy($pair->getId()) ?? null;
			if($id === null){
				throw new \RuntimeException("No legacy ID matches " . $pair->getId());
			}
			$data = $pair->getMeta();
			if($data > 15){
				//we can't handle metadata with more than 4 bits
				continue;
			}
			$mappedState = $pair->getBlockState();
			$mappedName = $mappedState->getString("name");
			if(!isset($idToStatesMap[$mappedName])){
				throw new \RuntimeException("Mapped new state does not appear in network table");
			}
			foreach($idToStatesMap[$mappedName] as $k){
				$networkState = $this->bedrockKnownStates[$k];
				if($mappedState->equals($networkState->getCompoundTag("block"))){
					$this->registerMapping($k, $id, $data);
					continue 2;
				}
			}
			throw new \RuntimeException("Mapped new state does not appear in network table");
		}
	}

	/**
	 * Randomizes the order of the runtimeID table to prevent plugins relying on them.
	 * Plugins shouldn't use this stuff anyway, but plugin devs have an irritating habit of ignoring what they
	 * aren't supposed to do, so we have to deliberately break it to make them stop.
	 *
	 * @param CompoundTag[] $table
	 *
	 * @return CompoundTag[]
	 */
	private static function randomizeTable(array $table) : array{
		$postSeed = mt_rand(); //save a seed to set afterwards, to avoid poor quality randoms
		mt_srand(getmypid()); //Use a seed which is the same on all threads. This isn't a secure seed, but we don't care.
		shuffle($table);
		mt_srand($postSeed); //restore a good quality seed that isn't dependent on PID
		return $table;
	}

	public function toRuntimeId(int $id, int $meta = 0) : int{
		/*
		 * try id+meta first
		 * if not found, try id+0 (strip meta)
		 * if still not found, return update! block
		 */
		return $this->legacyToRuntimeMap[($id << 4) | $meta] ?? $this->legacyToRuntimeMap[$id << 4] ?? $this->legacyToRuntimeMap[BlockLegacyIds::INFO_UPDATE << 4];
	}

	/**
	 * @return int[] [id, meta]
	 */
	public function fromRuntimeId(int $runtimeId) : array{
		$v = $this->runtimeToLegacyMap[$runtimeId];
		return [$v >> 4, $v & 0xf];
	}

	private function registerMapping(int $staticRuntimeId, int $legacyId, int $legacyMeta) : void{
		$this->legacyToRuntimeMap[($legacyId << 4) | $legacyMeta] = $staticRuntimeId;
		$this->runtimeToLegacyMap[$staticRuntimeId] = ($legacyId << 4) | $legacyMeta;
		$this->startGamePaletteCache = null;
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getBedrockKnownStates() : array{
		return $this->bedrockKnownStates;
	}

	/**
	 * @phpstan-return CacheableNbt<\pocketmine\nbt\tag\ListTag>
	 */
	public function getStartGamePaletteCache() : CacheableNbt{
		return $this->startGamePaletteCache ?? ($this->startGamePaletteCache = new CacheableNbt(new ListTag($this->bedrockKnownStates)));
	}
}
