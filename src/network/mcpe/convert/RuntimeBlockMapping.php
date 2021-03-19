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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
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
            ProtocolInfo::PROTOCOL_1_16_200 => "-1.16.200"
        ];

	    foreach ($paths as $protocolId => $path){
            $canonicalBlockStatesFile = file_get_contents(\pocketmine\RESOURCE_PATH . "vanilla/canonical_block_states" . $path . ".nbt");
            if($canonicalBlockStatesFile === false){
                throw new AssumptionFailedError("Missing required resource file");
            }
            $stream = new PacketSerializer($canonicalBlockStatesFile);
            $list = [];
            while(!$stream->feof()){
                $list[] = $stream->getNbtCompoundRoot();
            }
            $this->bedrockKnownStates[$protocolId] = $list;

            $this->setupLegacyMappings($protocolId, $path);
        }
	}

	public static function getMappingProtocol(int $protocolId) : int{
		return $protocolId <= ProtocolInfo::PROTOCOL_1_16_200 ? ProtocolInfo::PROTOCOL_1_16_200 : ProtocolInfo::CURRENT_PROTOCOL;
	}

	/**
	 * @param Player[] $players
	 *
	 * @return Player[][]
	 */
	public static function sortByProtocol(array $players) : array{
		$sortPlayers = [];

		foreach($players as $player){
			$protocolId = self::getMappingProtocol($player->getNetworkSession()->getProtocolId());

			if(isset($sortPlayers[$protocolId])){
				$sortPlayers[$protocolId][] = $player;
			}else{
				$sortPlayers[$protocolId] = [$player];
			}
		}

		return $sortPlayers;
	}

	private function setupLegacyMappings(int $protocolId, string $path) : void{
		$legacyIdMap = LegacyBlockIdToStringIdMap::getInstance();
		/** @var R12ToCurrentBlockMapEntry[] $legacyStateMap */
		$legacyStateMap = [];
		$legacyStateMapReader = new PacketSerializer(file_get_contents(\pocketmine\RESOURCE_PATH . "vanilla/r12_to_current_block_map" . $path . ".bin"));
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
		foreach($this->bedrockKnownStates[$protocolId] as $k => $state){
			$idToStatesMap[$state->getString("name")][] = $k;
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
				$networkState = $this->bedrockKnownStates[$protocolId][$k];
				if($mappedState->equals($networkState)){
					$this->registerMapping($protocolId, $k, $id, $data);
					continue 2;
				}
			}
			throw new \RuntimeException("Mapped new state does not appear in network table");
		}
	}

	public function toRuntimeId(int $internalStateId, int $protocolId = ProtocolInfo::CURRENT_PROTOCOL) : int{
		return $this->legacyToRuntimeMap[$internalStateId][$protocolId] ?? $this->legacyToRuntimeMap[BlockLegacyIds::INFO_UPDATE << 4][$protocolId];
	}

	public function fromRuntimeId(int $runtimeId, int $protocolId = ProtocolInfo::CURRENT_PROTOCOL) : int{
		return $this->runtimeToLegacyMap[$runtimeId][$protocolId];
	}

	private function registerMapping(int $protocolId, int $staticRuntimeId, int $legacyId, int $legacyMeta) : void{
		$this->legacyToRuntimeMap[($legacyId << 4) | $legacyMeta][$protocolId] = $staticRuntimeId;
		$this->runtimeToLegacyMap[$staticRuntimeId][$protocolId] = ($legacyId << 4) | $legacyMeta;
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getBedrockKnownStates(int $protocolId = ProtocolInfo::CURRENT_PROTOCOL) : array{
		return $this->bedrockKnownStates[$protocolId];
	}
}
