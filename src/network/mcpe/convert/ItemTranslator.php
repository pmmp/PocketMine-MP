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

use pocketmine\data\bedrock\item\ItemDeserializer;
use pocketmine\data\bedrock\item\ItemSerializer;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\bedrock\item\ItemTypeSerializeException;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\utils\AssumptionFailedError;

/**
 * This class handles translation between network item ID+metadata to PocketMine-MP internal ID+metadata and vice versa.
 */
final class ItemTranslator{
	public const NO_BLOCK_RUNTIME_ID = 0;

	public function __construct(
		private ItemTypeDictionary $itemTypeDictionary,
		private BlockStateDictionary $blockStateDictionary,
		private ItemSerializer $itemSerializer,
		private ItemDeserializer $itemDeserializer
	){}

	/**
	 * @return int[]|null
	 * @phpstan-return array{int, int, int}|null
	 */
	public function toNetworkIdQuiet(Item $item) : ?array{
		try{
			return $this->toNetworkId($item);
		}catch(ItemTypeSerializeException){
			return null;
		}
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int, int}
	 *
	 * @throws ItemTypeSerializeException
	 */
	public function toNetworkId(Item $item) : array{
		//TODO: we should probably come up with a cache for this

		$itemData = $this->itemSerializer->serializeType($item);

		$numericId = $this->itemTypeDictionary->fromStringId($itemData->getName());
		$blockStateData = $itemData->getBlock();

		if($blockStateData !== null){
			$blockRuntimeId = $this->blockStateDictionary->lookupStateIdFromData($blockStateData);
			if($blockRuntimeId === null){
				throw new AssumptionFailedError("Unmapped blockstate returned by blockstate serializer: " . $blockStateData->toNbt());
			}
		}else{
			$blockRuntimeId = self::NO_BLOCK_RUNTIME_ID; //this is technically a valid block runtime ID, but is used to represent "no block" (derp mojang)
		}

		return [$numericId, $itemData->getMeta(), $blockRuntimeId];
	}

	/**
	 * @throws ItemTypeSerializeException
	 */
	public function toNetworkNbt(Item $item) : CompoundTag{
		//TODO: this relies on the assumption that network item NBT is the same as disk item NBT, which may not always
		//be true - if we stick on an older world version while updating network version, this could be a problem (and
		//may be a problem for multi version implementations)
		return $this->itemSerializer->serializeStack($item)->toNbt();
	}

	/**
	 * @throws TypeConversionException
	 */
	public function fromNetworkId(int $networkId, int $networkMeta, int $networkBlockRuntimeId) : Item{
		try{
			$stringId = $this->itemTypeDictionary->fromIntId($networkId);
		}catch(\InvalidArgumentException $e){
			//TODO: a quiet version of fromIntId() would be better than catching InvalidArgumentException
			throw TypeConversionException::wrap($e, "Invalid network itemstack ID $networkId");
		}

		$blockStateData = null;
		if($networkBlockRuntimeId !== self::NO_BLOCK_RUNTIME_ID){
			$blockStateData = $this->blockStateDictionary->generateDataFromStateId($networkBlockRuntimeId);
			if($blockStateData === null){
				throw new TypeConversionException("Blockstate runtimeID $networkBlockRuntimeId does not correspond to any known blockstate");
			}
		}

		try{
			return $this->itemDeserializer->deserializeType(new SavedItemData($stringId, $networkMeta, $blockStateData));
		}catch(ItemTypeDeserializeException $e){
			throw TypeConversionException::wrap($e, "Invalid network itemstack data");
		}
	}
}
