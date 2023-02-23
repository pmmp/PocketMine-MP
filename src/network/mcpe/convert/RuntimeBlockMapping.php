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

use pocketmine\data\bedrock\BedrockDataFiles;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateSerializeException;
use pocketmine\data\bedrock\block\BlockStateSerializer;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

/**
 * @internal
 */
final class RuntimeBlockMapping{
	use SingletonTrait;

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $networkIdCache = [];

	/** Used when a blockstate can't be correctly serialized (e.g. because it's unknown) */
	private BlockStateData $fallbackStateData;
	private int $fallbackStateId;

	private static function make() : self{
		$canonicalBlockStatesRaw = Filesystem::fileGetContents(BedrockDataFiles::CANONICAL_BLOCK_STATES_NBT);
		$metaMappingRaw = Filesystem::fileGetContents(BedrockDataFiles::BLOCK_STATE_META_MAP_JSON);
		return new self(
			BlockStateDictionary::loadFromString($canonicalBlockStatesRaw, $metaMappingRaw),
			GlobalBlockStateHandlers::getSerializer()
		);
	}

	public function __construct(
		private BlockStateDictionary $blockStateDictionary,
		private BlockStateSerializer $blockStateSerializer
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
}
