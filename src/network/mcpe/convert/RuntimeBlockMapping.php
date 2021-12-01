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

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\LegacyBlockIdToStringIdMap;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializerContext;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use Webmozart\PathUtil\Path;
use function file_get_contents;

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

	private function __construct(){
		$canonicalBlockStatesFile = file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, "canonical_block_states.nbt"));
		if($canonicalBlockStatesFile === false){
			throw new AssumptionFailedError("Missing required resource file");
		}
		$stream = PacketSerializer::decoder($canonicalBlockStatesFile, 0, new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary()));
		$list = [];
		while(!$stream->feof()){
			$list[] = $stream->getNbtCompoundRoot();
		}
		$this->bedrockKnownStates = $list;

		$this->setupLegacyMappings();
	}

	private function setupLegacyMappings() : void{
		$legacyIdMap = LegacyBlockIdToStringIdMap::getInstance();
		/** @var R12ToCurrentBlockMapEntry[] $legacyStateMap */
		$legacyStateMap = [];
		$legacyStateMapReader = PacketSerializer::decoder(file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, "r12_to_current_block_map.bin")), 0, new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary()));
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
			$idToStatesMap[$state->getString("name")][] = $k;
		}
		foreach($legacyStateMap as $pair){
			$id = $legacyIdMap->stringToLegacy($pair->getId());
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
				if($mappedState->equals($networkState)){
					$this->registerMapping($k, $id, $data);
					continue 2;
				}
			}
			throw new \RuntimeException("Mapped new state does not appear in network table");
		}
	}

	public function toRuntimeId(int $internalStateId) : int{
		return $this->legacyToRuntimeMap[$internalStateId] ?? $this->legacyToRuntimeMap[BlockLegacyIds::INFO_UPDATE << Block::INTERNAL_METADATA_BITS];
	}

	public function fromRuntimeId(int $runtimeId) : int{
		return $this->runtimeToLegacyMap[$runtimeId];
	}

	private function registerMapping(int $staticRuntimeId, int $legacyId, int $legacyMeta) : void{
		$this->legacyToRuntimeMap[($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta] = $staticRuntimeId;
		$this->runtimeToLegacyMap[$staticRuntimeId] = ($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta;
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getBedrockKnownStates() : array{
		return $this->bedrockKnownStates;
	}
}
