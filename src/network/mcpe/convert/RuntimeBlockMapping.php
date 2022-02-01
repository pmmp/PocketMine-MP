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

use pocketmine\block\BlockFactory;
use pocketmine\block\UnknownBlock;
use pocketmine\data\bedrock\blockstate\BlockStateData;
use pocketmine\data\bedrock\blockstate\BlockStateSerializeException;
use pocketmine\data\bedrock\blockstate\BlockStateSerializer;
use pocketmine\data\bedrock\blockstate\BlockTypeNames;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use Webmozart\PathUtil\Path;
use function file_get_contents;

/**
 * @internal
 */
final class RuntimeBlockMapping{
	use SingletonTrait;

	private BlockStateDictionary $blockStateDictionary;
	private BlockStateSerializer $blockStateSerializer;
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $networkIdCache = [];

	/**
	 * Used when a blockstate can't be correctly serialized (e.g. because it's unknown)
	 */
	private BlockStateData $fallbackStateData;

	private function __construct(){
		$contents = Utils::assumeNotFalse(file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, "canonical_block_states.nbt")), "Missing required resource file");
		$this->blockStateDictionary = BlockStateDictionary::loadFromString($contents);
		$this->blockStateSerializer = new BlockStateSerializer();

		$this->fallbackStateData = new BlockStateData(BlockTypeNames::INFO_UPDATE, CompoundTag::create(), BlockStateData::CURRENT_VERSION);
	}

	public function toRuntimeId(int $internalStateId) : int{
		if(isset($this->networkIdCache[$internalStateId])){
			return $this->networkIdCache[$internalStateId];
		}

		//TODO: singleton usage not ideal
		$block = BlockFactory::getInstance()->fromFullBlock($internalStateId);
		if($block instanceof UnknownBlock){
			$blockStateData = $this->fallbackStateData;
		}else{
			try{
				$blockStateData = $this->blockStateSerializer->serialize($block);
			}catch(BlockStateSerializeException $e){
				throw new AssumptionFailedError("Invalid serializer for block $block", 0, $e);
			}
		}

		$networkId = $this->blockStateDictionary->lookupStateIdFromData($blockStateData);

		if($networkId === null){
			throw new AssumptionFailedError("Unmapped blockstate returned by blockstate serializer: " . $blockStateData->toNbt());
		}

		return $this->networkIdCache[$internalStateId] = $networkId;
	}

	public function getBlockStateDictionary() : BlockStateDictionary{ return $this->blockStateDictionary; }

	public function getFallbackStateData() : BlockStateData{ return $this->fallbackStateData; }
}
