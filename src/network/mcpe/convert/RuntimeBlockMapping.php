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
use pocketmine\data\bedrock\block\BlockStateSerializeException;
use pocketmine\data\bedrock\block\BlockStateSerializer;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgrader;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaUtils;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\ProtocolSingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use Symfony\Component\Filesystem\Path;
use const pocketmine\BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH;

/**
 * @internal
 */
final class RuntimeBlockMapping{
	use ProtocolSingletonTrait;

	public const CANONICAL_BLOCK_STATES_PATH = 0;
	public const BLOCK_STATE_META_MAP_PATH = 1;

	public const PATHS = [
		ProtocolInfo::CURRENT_PROTOCOL => [
			self::CANONICAL_BLOCK_STATES_PATH => '',
			self::BLOCK_STATE_META_MAP_PATH => '',
		],
		ProtocolInfo::PROTOCOL_1_19_40 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.19.40',
			self::BLOCK_STATE_META_MAP_PATH => '-1.19.40',
		],
		ProtocolInfo::PROTOCOL_1_19_10 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.19.10',
			self::BLOCK_STATE_META_MAP_PATH => '-1.19.10',
		],
		ProtocolInfo::PROTOCOL_1_18_30 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.18.30',
			self::BLOCK_STATE_META_MAP_PATH => '-1.19.10',
		],
		ProtocolInfo::PROTOCOL_1_18_10 => [
			self::CANONICAL_BLOCK_STATES_PATH => '-1.18.10',
			self::BLOCK_STATE_META_MAP_PATH => '-1.19.10',
		],
	];

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $networkIdCache = [];

	/** Used when a blockstate can't be correctly serialized (e.g. because it's unknown) */
	private BlockStateData $fallbackStateData;
	private int $fallbackStateId;

	private static function make(int $protocolId) : self{
		$canonicalBlockStatesRaw = Filesystem::fileGetContents(Path::join(\pocketmine\BEDROCK_DATA_PATH, "canonical_block_states" . self::PATHS[$protocolId][self::CANONICAL_BLOCK_STATES_PATH] . ".nbt"));
		$metaMappingRaw = Filesystem::fileGetContents(Path::join(\pocketmine\BEDROCK_DATA_PATH, 'block_state_meta_map' . self::PATHS[$protocolId][self::BLOCK_STATE_META_MAP_PATH] . '.json'));

		if(($blockStateSchemaId = self::getBlockStateSchemaId($protocolId)) !== null){
			$blockStateUpgrader = new BlockStateUpgrader(BlockStateUpgradeSchemaUtils::loadSchemas(
				Path::join(BEDROCK_BLOCK_UPGRADE_SCHEMA_PATH, 'nbt_upgrade_schema'),
				$blockStateSchemaId
			));
		}

		return new self(
			BlockStateDictionary::loadFromString($canonicalBlockStatesRaw, $metaMappingRaw),
			GlobalBlockStateHandlers::getSerializer(),
			$blockStateUpgrader ?? null
		);
	}

	public function __construct(
		private BlockStateDictionary $blockStateDictionary,
		private BlockStateSerializer $blockStateSerializer,
		private ?BlockStateUpgrader $blockStateUpgrader
	){
		$this->fallbackStateId = $this->blockStateDictionary->lookupStateIdFromData(
				BlockStateData::current(BlockTypeNames::INFO_UPDATE, [])
			) ?? throw new AssumptionFailedError(BlockTypeNames::INFO_UPDATE . " should always exist");
		//lookup the state data from the dictionary to avoid keeping two copies of the same data around
		$this->fallbackStateData = $this->blockStateDictionary->getDataFromStateId($this->fallbackStateId) ?? throw new AssumptionFailedError("We just looked up this state data, so it must exist");
	}

	public function toRuntimeId(int $internalStateId) : int{
		if(isset($this->networkIdCache[$internalStateId])){
			return $this->networkIdCache[$internalStateId];
		}

		try{
			$blockStateData = $this->blockStateSerializer->serialize($internalStateId);
			if($this->blockStateUpgrader !== null){
				$blockStateData = $this->blockStateUpgrader->upgrade($blockStateData);
			}

			$networkId = $this->blockStateDictionary->lookupStateIdFromData($blockStateData);
			if($networkId === null){
				throw new AssumptionFailedError("Unmapped blockstate returned by blockstate serializer: " . $blockStateData->toNbt());
			}
		}catch(BlockStateSerializeException){
			//TODO: this will swallow any error caused by invalid block properties; this is not ideal, but it should be
			//covered by unit tests, so this is probably a safe assumption.
			$networkId = $this->fallbackStateId;
		}

		return $this->networkIdCache[$internalStateId] = $networkId;
	}

	/**
	 * Looks up the network state data associated with the given internal state ID.
	 */
	public function toStateData(int $internalStateId) : BlockStateData{
		//we don't directly use the blockstate serializer here - we can't assume that the network blockstate NBT is the
		//same as the disk blockstate NBT, in case we decide to have different world version than network version (or in
		//case someone wants to implement multi version).
		$networkRuntimeId = $this->toRuntimeId($internalStateId);

		return $this->blockStateDictionary->getDataFromStateId($networkRuntimeId) ?? throw new AssumptionFailedError("We just looked up this state ID, so it must exist");
	}

	public function getBlockStateDictionary() : BlockStateDictionary{ return $this->blockStateDictionary; }

	public function getFallbackStateData() : BlockStateData{ return $this->fallbackStateData; }

	public static function convertProtocol(int $protocolId) : int{
		if($protocolId < ProtocolInfo::PROTOCOL_1_19_40){
			if($protocolId === ProtocolInfo::PROTOCOL_1_19_0){
				return ProtocolInfo::PROTOCOL_1_19_10;
			}

			if($protocolId >= ProtocolInfo::PROTOCOL_1_19_20){
				return ProtocolInfo::PROTOCOL_1_19_40;
			}
		}

		return $protocolId;
	}

	private static function getBlockStateSchemaId(int $protocolId) : ?int{
		return match($protocolId){
			ProtocolInfo::PROTOCOL_1_19_50, ProtocolInfo::PROTOCOL_1_19_40 => 151,
			ProtocolInfo::PROTOCOL_1_19_10 => 141,
			ProtocolInfo::PROTOCOL_1_18_30 => 131,
			ProtocolInfo::PROTOCOL_1_18_10 => null,
			default => throw new AssumptionFailedError("Unknown protocol ID $protocolId"),
		};
	}
}
