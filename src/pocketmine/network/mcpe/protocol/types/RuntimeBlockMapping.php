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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\block\BlockIds;
use pocketmine\nbt\NBT;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function file_get_contents;
use function getmypid;
use function json_decode;
use function mt_rand;
use function mt_srand;
use function shuffle;

/**
 * @internal
 */
final class RuntimeBlockMapping{

	/** @var int[] */
	private static $legacyToRuntimeMap = [];
	/** @var int[] */
	private static $runtimeToLegacyMap = [];
	/** @var CompoundTag[]|null */
	private static $bedrockKnownStates = null;

	private function __construct(){
		//NOOP
	}

	public static function init() : void{
		$tag = (new NetworkLittleEndianNBTStream())->read(file_get_contents(\pocketmine\RESOURCE_PATH . "vanilla/required_block_states.nbt"));
		if(!($tag instanceof ListTag) or $tag->getTagType() !== NBT::TAG_Compound){ //this is a little redundant currently, but good for auto complete and makes phpstan happy
			throw new \RuntimeException("Invalid blockstates table, expected TAG_List<TAG_Compound> root");
		}

		/** @var CompoundTag[] $list */
		$list = $tag->getValue();
		self::$bedrockKnownStates = self::randomizeTable($list);

		self::setupLegacyMappings();
	}

	private static function setupLegacyMappings() : void{
		$legacyIdMap = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . "vanilla/block_id_map.json"), true);
		$legacyStateMap = (new NetworkLittleEndianNBTStream())->read(file_get_contents(\pocketmine\RESOURCE_PATH . "vanilla/r12_to_current_block_map.nbt"));
		if(!($legacyStateMap instanceof ListTag) or $legacyStateMap->getTagType() !== NBT::TAG_Compound){
			throw new \RuntimeException("Invalid legacy states mapping table, expected TAG_List<TAG_Compound> root");
		}

		/**
		 * @var int[][] $idToStatesMap string id -> int[] list of candidate state indices
		 */
		$idToStatesMap = [];
		foreach(self::$bedrockKnownStates as $k => $state){
			$idToStatesMap[$state->getCompoundTag("block")->getString("name")][] = $k;
		}
		/** @var CompoundTag $pair */
		foreach($legacyStateMap as $pair){
			$oldState = $pair->getCompoundTag("old");
			$id = $legacyIdMap[$oldState->getString("name")];
			$data = $oldState->getShort("val");
			if($data > 15){
				//we can't handle metadata with more than 4 bits
				continue;
			}
			$mappedState = $pair->getCompoundTag("new");

			//TODO HACK: idiotic NBT compare behaviour on 3.x compares keys which are stored by values
			$mappedState->setName("block");
			$mappedName = $mappedState->getString("name");
			if(!isset($idToStatesMap[$mappedName])){
				throw new \RuntimeException("Mapped new state does not appear in network table");
			}
			foreach($idToStatesMap[$mappedName] as $k){
				$networkState = self::$bedrockKnownStates[$k];
				if($mappedState->equals($networkState->getCompoundTag("block"))){
					self::registerMapping($k, $id, $data);
					continue 2;
				}
			}
			throw new \RuntimeException("Mapped new state does not appear in network table");
		}
	}

	private static function lazyInit() : void{
		if(self::$bedrockKnownStates === null){
			self::init();
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

	public static function toStaticRuntimeId(int $id, int $meta = 0) : int{
		self::lazyInit();
		/*
		 * try id+meta first
		 * if not found, try id+0 (strip meta)
		 * if still not found, return update! block
		 */
		return self::$legacyToRuntimeMap[($id << 4) | $meta] ?? self::$legacyToRuntimeMap[$id << 4] ?? self::$legacyToRuntimeMap[BlockIds::INFO_UPDATE << 4];
	}

	/**
	 * @return int[] [id, meta]
	 */
	public static function fromStaticRuntimeId(int $runtimeId) : array{
		self::lazyInit();
		$v = self::$runtimeToLegacyMap[$runtimeId];
		return [$v >> 4, $v & 0xf];
	}

	private static function registerMapping(int $staticRuntimeId, int $legacyId, int $legacyMeta) : void{
		self::$legacyToRuntimeMap[($legacyId << 4) | $legacyMeta] = $staticRuntimeId;
		self::$runtimeToLegacyMap[$staticRuntimeId] = ($legacyId << 4) | $legacyMeta;
	}

	/**
	 * @return CompoundTag[]
	 */
	public static function getBedrockKnownStates() : array{
		self::lazyInit();
		return self::$bedrockKnownStates;
	}
}
