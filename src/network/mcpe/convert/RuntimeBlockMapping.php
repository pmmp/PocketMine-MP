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
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializerContext;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use Webmozart\PathUtil\Path;
use function file_get_contents;

/**
 * @internal
 */
final class RuntimeBlockMapping{
	use SingletonTrait;

	/** @var int[][] */
	private $legacyToRuntimeMap = [];
	/** @var int[][] */
	private $runtimeToLegacyMap = [];
	/** @var CompoundTag[][] */
	private $bedrockKnownStates = [];

	private function __construct(){
		$paths = [
			ProtocolInfo::CURRENT_PROTOCOL => "",
			ProtocolInfo::PROTOCOL_1_17_30 => "-1.17.30",
			ProtocolInfo::PROTOCOL_1_17_10 => "-1.17.10",
			ProtocolInfo::PROTOCOL_1_17_0 => "-1.17.0",
			ProtocolInfo::PROTOCOL_1_16_220 => "-1.16.220",
			ProtocolInfo::PROTOCOL_1_16_200 => "-1.16.200"
		];

		foreach($paths as $mappingProtocol => $path){
			$canonicalBlockStatesFile = file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, "canonical_block_states" . $path . ".nbt"));
			if($canonicalBlockStatesFile === false){
				throw new AssumptionFailedError("Missing required resource file");
			}
			$stream = PacketSerializer::decoder($canonicalBlockStatesFile, 0, new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary(GlobalItemTypeDictionary::getDictionaryProtocol($mappingProtocol))));
			$list = [];
			while(!$stream->feof()){
				$list[] = $stream->getNbtCompoundRoot();
			}
			$this->bedrockKnownStates[$mappingProtocol] = $list;

			if($mappingProtocol === ProtocolInfo::PROTOCOL_1_17_0){
				$this->setupLegacyMappings($mappingProtocol, $paths[ProtocolInfo::PROTOCOL_1_17_10]);
			}elseif($mappingProtocol === ProtocolInfo::PROTOCOL_1_17_30){
				$this->setupLegacyMappings($mappingProtocol, $paths[ProtocolInfo::PROTOCOL_1_17_40]);
			}else{
				$this->setupLegacyMappings($mappingProtocol, $path);
			}
		}
	}

	public static function getMappingProtocol(int $protocolId) : int{
		if($protocolId <= ProtocolInfo::PROTOCOL_1_16_200){
			return ProtocolInfo::PROTOCOL_1_16_200;
		}

		if($protocolId <= ProtocolInfo::PROTOCOL_1_16_220){
			return ProtocolInfo::PROTOCOL_1_16_220;
		}

		if($protocolId <= ProtocolInfo::PROTOCOL_1_17_0){
			return ProtocolInfo::PROTOCOL_1_17_0;
		}

		if($protocolId <= ProtocolInfo::PROTOCOL_1_17_10){
			return ProtocolInfo::PROTOCOL_1_17_10;
		}

		if($protocolId <= ProtocolInfo::PROTOCOL_1_17_30){
			return ProtocolInfo::PROTOCOL_1_17_30;
		}

		return ProtocolInfo::CURRENT_PROTOCOL;
	}

	/**
	 * @param Player[] $players
	 *
	 * @return Player[][]
	 */
	public static function sortByProtocol(array $players) : array{
		$sortPlayers = [];

		foreach($players as $player){
			$mappingProtocol = self::getMappingProtocol($player->getNetworkSession()->getProtocolId());

			if(isset($sortPlayers[$mappingProtocol])){
				$sortPlayers[$mappingProtocol][] = $player;
			}else{
				$sortPlayers[$mappingProtocol] = [$player];
			}
		}

		return $sortPlayers;
	}

	private function setupLegacyMappings(int $mappingProtocol, string $path) : void{
		$legacyIdMap = LegacyBlockIdToStringIdMap::getInstance();
		/** @var R12ToCurrentBlockMapEntry[] $legacyStateMap */
		$legacyStateMap = [];
		$legacyStateMapReader = PacketSerializer::decoder(file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, "r12_to_current_block_map" . $path . ".bin")), 0, new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary(GlobalItemTypeDictionary::getDictionaryProtocol($mappingProtocol))));
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
		foreach($this->bedrockKnownStates[$mappingProtocol] as $k => $state){
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
				$networkState = $this->bedrockKnownStates[$mappingProtocol][$k];
				if($mappedState->equals($networkState)){
					$this->registerMapping($mappingProtocol, $k, $id, $data);
					continue 2;
				}
			}
			throw new \RuntimeException("Mapped new state does not appear in network table");
		}
	}

	public function toRuntimeId(int $internalStateId, int $mappingProtocol = ProtocolInfo::CURRENT_PROTOCOL) : int{
		return $this->legacyToRuntimeMap[$internalStateId][$mappingProtocol] ?? $this->legacyToRuntimeMap[BlockLegacyIds::INFO_UPDATE << Block::INTERNAL_METADATA_BITS][$mappingProtocol];
	}

	public function fromRuntimeId(int $runtimeId, int $mappingProtocol = ProtocolInfo::CURRENT_PROTOCOL) : int{
		return $this->runtimeToLegacyMap[$runtimeId][$mappingProtocol];
	}

	private function registerMapping(int $mappingProtocol, int $staticRuntimeId, int $legacyId, int $legacyMeta) : void{
		$this->legacyToRuntimeMap[($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta][$mappingProtocol] = $staticRuntimeId;
		$this->runtimeToLegacyMap[$staticRuntimeId][$mappingProtocol] = ($legacyId << Block::INTERNAL_METADATA_BITS) | $legacyMeta;
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getBedrockKnownStates(int $mappingProtocol = ProtocolInfo::CURRENT_PROTOCOL) : array{
		return $this->bedrockKnownStates[$mappingProtocol];
	}
}
