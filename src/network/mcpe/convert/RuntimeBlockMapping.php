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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use Webmozart\PathUtil\Path;
use function file_get_contents;

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
		$canonicalBlockStatesFile = Path::join(\pocketmine\BEDROCK_DATA_PATH, "canonical_block_states.nbt");
		$canonicalBlockStatesRaw = Utils::assumeNotFalse(file_get_contents($canonicalBlockStatesFile), "Missing required resource file");

		$metaMappingFile = Path::join(\pocketmine\BEDROCK_DATA_PATH, 'block_state_meta_map.json');
		$metaMappingRaw = Utils::assumeNotFalse(file_get_contents($metaMappingFile), "Missing required resource file");
		return new self(
			BlockStateDictionary::loadFromString($canonicalBlockStatesRaw, $metaMappingRaw),
			GlobalBlockStateHandlers::getSerializer()
		);
	}

	public function __construct(
		private BlockStateDictionary $blockStateDictionary,
		private BlockStateSerializer $blockStateSerializer
	){
		$this->fallbackStateData = new BlockStateData(BlockTypeNames::INFO_UPDATE, CompoundTag::create(), BlockStateData::CURRENT_VERSION);
		$this->fallbackStateId = $this->blockStateDictionary->lookupStateIdFromData($this->fallbackStateData) ?? throw new AssumptionFailedError(BlockTypeNames::INFO_UPDATE . " should always exist");
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

	public function getBlockStateDictionary() : BlockStateDictionary{ return $this->blockStateDictionary; }

	public function getFallbackStateData() : BlockStateData{ return $this->fallbackStateData; }
}
