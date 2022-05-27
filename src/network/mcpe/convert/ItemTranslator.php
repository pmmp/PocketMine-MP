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
use pocketmine\data\bedrock\item\ItemTypeSerializeException;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;

/**
 * This class handles translation between network item ID+metadata to PocketMine-MP internal ID+metadata and vice versa.
 */
final class ItemTranslator{
	public const NO_BLOCK_RUNTIME_ID = 0;

	use SingletonTrait;

	private static function make() : self{
		return new self(GlobalItemTypeDictionary::getInstance()->getDictionary(), new ItemSerializer(), new ItemDeserializer());
	}

	public function __construct(
		private ItemTypeDictionary $dictionary,
		private ItemSerializer $itemSerializer,
		private ItemDeserializer $itemDeserializer
	){}

	/**
	 * @return int[]|null
	 * @phpstan-return array{int, int, int}|null
	 */
	public function toNetworkIdQuiet(int $internalId, int $internalMeta) : ?array{
		//TODO: we should probably come up with a cache for this

		try{
			$itemData = $this->itemSerializer->serialize(ItemFactory::getInstance()->get($internalId, $internalMeta));
		}catch(ItemTypeSerializeException){
			//TODO: this will swallow any serializer error; this is not ideal, but it should be OK since unit tests
			//should cover this
			return null;
		}

		$numericId = $this->dictionary->fromStringId($itemData->getName());
		$blockStateData = $itemData->getBlock();

		if($blockStateData !== null){
			$blockRuntimeId = RuntimeBlockMapping::getInstance()->getBlockStateDictionary()->lookupStateIdFromData($blockStateData);
			if($blockRuntimeId === null){
				throw new AssumptionFailedError("Unmapped blockstate returned by blockstate serializer: " . $blockStateData->toNbt());
			}
		}else{
			$blockRuntimeId = self::NO_BLOCK_RUNTIME_ID; //this is technically a valid block runtime ID, but is used to represent "no block" (derp mojang)
		}

		return [$numericId, $itemData->getMeta(), $blockRuntimeId];
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int, int}
	 */
	public function toNetworkId(int $internalId, int $internalMeta) : array{
		return $this->toNetworkIdQuiet($internalId, $internalMeta) ??
			$this->toNetworkIdQuiet(ItemIds::INFO_UPDATE, 0) ?? //TODO: bad duct tape
			throw new \InvalidArgumentException("Unmapped ID/metadata combination $internalId:$internalMeta");
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int}
	 * @throws TypeConversionException
	 */
	public function fromNetworkId(int $networkId, int $networkMeta, int $networkBlockRuntimeId) : array{
		$stringId = $this->dictionary->fromIntId($networkId);

		$blockStateData = $networkBlockRuntimeId !== self::NO_BLOCK_RUNTIME_ID ?
			RuntimeBlockMapping::getInstance()->getBlockStateDictionary()->getDataFromStateId($networkBlockRuntimeId) :
			null;

		$item = $this->itemDeserializer->deserialize(new SavedItemData($stringId, $networkMeta, $blockStateData));
		return [$item->getId(), $item->getMeta()];
	}

	/**
	 * @return int[]
	 * @phpstan-return array{int, int}
	 * @throws TypeConversionException
	 */
	public function fromNetworkIdWithWildcardHandling(int $networkId, int $networkMeta) : array{
		if($networkMeta !== 0x7fff){
			return $this->fromNetworkId($networkId, $networkMeta, 0);
		}
		[$id, ] = $this->fromNetworkId($networkId, 0, 0);
		return [$id, -1];
	}
}
