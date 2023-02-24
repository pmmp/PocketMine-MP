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
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function array_filter;
use function array_keys;
use function in_array;

/**
 * @internal
 */
final class RuntimeBlockMapping{
	use SingletonTrait;

	public const CANONICAL_BLOCK_STATES_PATH = 0;
	public const R12_TO_CURRENT_BLOCK_MAP_PATH = 1;
	private const PATHS = [
		ProtocolInfo::CURRENT_PROTOCOL => [
			self::CANONICAL_BLOCK_STATES_PATH => '',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '',
		],
		ProtocolInfo::PROTOCOL_1_19_50 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.19.50',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '',
		],
		ProtocolInfo::PROTOCOL_1_19_40 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.19.40',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '',
		],
		ProtocolInfo::PROTOCOL_1_19_10 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.19.10',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '',
		],
		ProtocolInfo::PROTOCOL_1_18_30 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.18.30',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.18.30',
		],
		ProtocolInfo::PROTOCOL_1_18_10 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.18.10',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.18.10',
		],
		ProtocolInfo::PROTOCOL_1_18_0 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.18.0',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.18.0',
		],
		ProtocolInfo::PROTOCOL_1_17_40 => [ // 1.18.0 has negative chunk hacks
			self::CANONICAL_BLOCK_STATES_PATH => '-1.18.0',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.18.0',
		],
		ProtocolInfo::PROTOCOL_1_17_30 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.17.30',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.18.0',
		],
		ProtocolInfo::PROTOCOL_1_17_10 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.17.10',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.17.10',
		],
		ProtocolInfo::PROTOCOL_1_17_0 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.17.0',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.17.10',
		],
		ProtocolInfo::PROTOCOL_1_16_220 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.16.210',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.16.210',
		],
		ProtocolInfo::PROTOCOL_1_16_200 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.16.100',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.16.20',
		],
		ProtocolInfo::PROTOCOL_1_16_20 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.16.20',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.16.20',
		],
		ProtocolInfo::PROTOCOL_1_16_0 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.16.0',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.16.0',
		],
		ProtocolInfo::PROTOCOL_1_14_60 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.14.0',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.14.0',
		],
		ProtocolInfo::PROTOCOL_1_13_0 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.13.0',
			self::R12_TO_CURRENT_BLOCK_MAP_PATH => '-1.13.0',
		],
	];

	/** @var int[][] */
	private array $legacyToRuntimeMap = [];
	/** @var int[][] */
	private array $runtimeToLegacyMap = [];
	/** @var CompoundTag[][] */
	private array $bedrockKnownStates = [];

	public function __construct(){
		$minimalProtocol = Utils::getMinimalProtocol();
		$protocols = array_keys(RuntimeBlockMapping::PATHS);
		$protocols[] = ProtocolInfo::PROTOCOL_1_12_0;

		$protocols = array_filter($protocols, fn(int $protocolId) => $protocolId >= $minimalProtocol);
		foreach($protocols as $mappingProtocol){
			$this->initialize($mappingProtocol);
		}
	}

	private function initialize(int $mappingProtocol) : void{
		if(isset($this->bedrockKnownStates[$mappingProtocol])) {
			return;
		}

		if(in_array($mappingProtocol, LegacyR12BlockStates::getLegacyVersions(), true)) {
			foreach(LegacyR12BlockStates::getInstance()->getBlockPaletteEntries($mappingProtocol) as $k => $entry) {
				$this->registerMapping($mappingProtocol, $k, $entry->getId(), $entry->getMetadata());
			}
			return;
		}

		$canonicalBlockStatesFile = Path::join(\pocketmine\BEDROCK_DATA_PATH, "canonical_block_states" . self::PATHS[$mappingProtocol][self::CANONICAL_BLOCK_STATES_PATH] . ".nbt");
		$stream = PacketSerializer::decoder(
			Filesystem::fileGetContents($canonicalBlockStatesFile),
			0,
			new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary(GlobalItemTypeDictionary::getDictionaryProtocol($mappingProtocol)))
		);
		$list = [];
		while(!$stream->feof()){
			$list[] = $stream->getNbtCompoundRoot();
		}
		$this->bedrockKnownStates[$mappingProtocol] = $list;

		$r12ToCurrentBlockMapFile = Path::join(\pocketmine\BEDROCK_DATA_PATH, "r12_to_current_block_map" . self::PATHS[$mappingProtocol][self::R12_TO_CURRENT_BLOCK_MAP_PATH] . ".bin");
		$this->setupLegacyMappings($mappingProtocol, $r12ToCurrentBlockMapFile);
	}

	public static function getMappingProtocol(int $protocolId) : int{
		if($protocolId >= ProtocolInfo::PROTOCOL_1_19_60){
			return ProtocolInfo::PROTOCOL_1_19_63;
		}

		if($protocolId < ProtocolInfo::PROTOCOL_1_19_40 && $protocolId >= ProtocolInfo::PROTOCOL_1_19_0){
			if($protocolId === ProtocolInfo::PROTOCOL_1_19_0){
				return ProtocolInfo::PROTOCOL_1_19_10;
			}

			if($protocolId >= ProtocolInfo::PROTOCOL_1_19_20){
				return ProtocolInfo::PROTOCOL_1_19_40;
			}
		}

		if($protocolId === ProtocolInfo::PROTOCOL_1_16_210){
			return ProtocolInfo::PROTOCOL_1_16_220;
		}

		if($protocolId === ProtocolInfo::PROTOCOL_1_16_100){
			return ProtocolInfo::PROTOCOL_1_16_200;
		}

		if($protocolId === ProtocolInfo::PROTOCOL_1_14_0){
			return ProtocolInfo::PROTOCOL_1_14_60;
		}

		return $protocolId;
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

	private function setupLegacyMappings(int $mappingProtocol, string $r12ToCurrentBlockMapFile) : void{
		$legacyIdMap = LegacyBlockIdToStringIdMap::getInstance();
		/** @var R12ToCurrentBlockMapEntry[] $legacyStateMap */
		$legacyStateMap = [];
		$legacyStateMapReader = PacketSerializer::decoder(
			Filesystem::fileGetContents($r12ToCurrentBlockMapFile),
			0,
			new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary(GlobalItemTypeDictionary::getDictionaryProtocol($mappingProtocol)))
		);
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
		$this->initialize($mappingProtocol);

		return $this->legacyToRuntimeMap[$internalStateId][$mappingProtocol] ?? $this->legacyToRuntimeMap[BlockLegacyIds::INFO_UPDATE << Block::INTERNAL_METADATA_BITS][$mappingProtocol];
	}

	public function fromRuntimeId(int $runtimeId, int $mappingProtocol = ProtocolInfo::CURRENT_PROTOCOL) : int{
		$this->initialize($mappingProtocol);

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
		$this->initialize($mappingProtocol);

		return $this->bedrockKnownStates[$mappingProtocol];
	}
}
