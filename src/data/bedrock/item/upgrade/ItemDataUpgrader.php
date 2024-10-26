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

namespace pocketmine\data\bedrock\item\upgrade;

use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\upgrade\BlockDataUpgrader;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\nbt\NBT;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\BlockStateDictionary;
use pocketmine\utils\Binary;
use function assert;

final class ItemDataUpgrader{
	private const TAG_LEGACY_ID = "id"; //TAG_Short (or TAG_String for Java itemstacks)

	public function __construct(
		private ItemIdMetaUpgrader $idMetaUpgrader,
		private LegacyItemIdToStringIdMap $legacyIntToStringIdMap,
		private R12ItemIdToBlockIdMap $r12ItemIdToBlockIdMap,
		private BlockDataUpgrader $blockDataUpgrader,
		private BlockItemIdMap $blockItemIdMap,
		private BlockStateDictionary $blockStateDictionary
	){}

	/**
	 * This function replaces the legacy ItemFactory::get().
	 *
	 * Unlike ItemFactory::get(), it returns a SavedItemStackData which you can do with as you please.
	 * If you want to deserialize it into a PocketMine-MP itemstack, pass it to the ItemDeserializer.
	 *
	 * @see ItemDataUpgrader::upgradeItemTypeDataInt()
	 */
	public function upgradeItemTypeDataString(string $rawNameId, int $meta, int $count, ?CompoundTag $nbt) : SavedItemStackData{
		if(($r12BlockId = $this->r12ItemIdToBlockIdMap->itemIdToBlockId($rawNameId)) !== null){
			try{
				$blockStateData = $this->blockDataUpgrader->upgradeStringIdMeta($r12BlockId, $meta);
			}catch(BlockStateDeserializeException $e){
				throw new SavedDataLoadingException("Failed to deserialize blockstate for legacy blockitem: " . $e->getMessage(), 0, $e);
			}
		}else{
			//probably a standard item
			$blockStateData = null;
		}

		[$newNameId, $newMeta] = $this->idMetaUpgrader->upgrade($rawNameId, $meta);

		//TODO: this won't account for spawn eggs from before 1.16.100 - perhaps we're lucky and they just left the meta in there anyway?

		return new SavedItemStackData(
			new SavedItemData($newNameId, $newMeta, $blockStateData, $nbt),
			$count,
			null,
			null,
			[],
			[]
		);
	}

	/**
	 * This function replaces the legacy ItemFactory::get().
	 *
	 * @throws SavedDataLoadingException if the legacy numeric ID doesn't map to a string ID
	 */
	public function upgradeItemTypeDataInt(int $legacyNumericId, int $meta, int $count, ?CompoundTag $nbt) : SavedItemStackData{
		//do not upgrade the ID beyond this initial step - we need the 1.12 ID for the item ID -> block ID map in the
		//next step
		$rawNameId = $this->legacyIntToStringIdMap->legacyToString($legacyNumericId);
		if($rawNameId === null){
			throw new SavedDataLoadingException("Unmapped legacy item ID $legacyNumericId");
		}
		return $this->upgradeItemTypeDataString($rawNameId, $meta, $count, $nbt);
	}

	/**
	 * @throws SavedDataLoadingException
	 */
	private function upgradeItemTypeNbt(CompoundTag $tag) : ?SavedItemData{
		if(($nameIdTag = $tag->getTag(SavedItemData::TAG_NAME)) instanceof StringTag){
			//Bedrock 1.6+

			$rawNameId = $nameIdTag->getValue();
		}elseif(($idTag = $tag->getTag(self::TAG_LEGACY_ID)) instanceof ShortTag){
			//Bedrock <= 1.5, PM <= 1.12

			if($idTag->getValue() === 0){
				//0 is a special case for air, which is not a valid item ID
				//this isn't supposed to be saved, but this appears in some places due to bugs in older versions
				return null;
			}
			$rawNameId = $this->legacyIntToStringIdMap->legacyToString($idTag->getValue());
			if($rawNameId === null){
				throw new SavedDataLoadingException("Legacy item ID " . $idTag->getValue() . " doesn't map to any modern string ID");
			}
		}elseif($idTag instanceof StringTag){
			//PC item save format - best we can do here is hope the string IDs match

			$rawNameId = $idTag->getValue();
		}else{
			throw new SavedDataLoadingException("Item stack data should have either a name ID or a legacy ID");
		}

		$meta = $tag->getShort(SavedItemData::TAG_DAMAGE, 0);

		$blockStateNbt = $tag->getCompoundTag(SavedItemData::TAG_BLOCK);
		if($blockStateNbt !== null){
			try{
				$blockStateData = $this->blockDataUpgrader->upgradeBlockStateNbt($blockStateNbt);
			}catch(BlockStateDeserializeException $e){
				throw new SavedDataLoadingException("Failed to deserialize blockstate for blockitem: " . $e->getMessage(), 0, $e);
			}
		}elseif(($r12BlockId = $this->r12ItemIdToBlockIdMap->itemIdToBlockId($rawNameId)) !== null){
			//this is a legacy blockitem represented by ID + meta
			try{
				$blockStateData = $this->blockDataUpgrader->upgradeStringIdMeta($r12BlockId, $meta);
			}catch(BlockStateDeserializeException $e){
				throw new SavedDataLoadingException("Failed to deserialize blockstate for legacy blockitem: " . $e->getMessage(), 0, $e);
			}
		}else{
			//probably a standard item
			$blockStateData = null;
		}

		[$newNameId, $newMeta] = $this->idMetaUpgrader->upgrade($rawNameId, $meta);

		//TODO: Dirty hack to load old skulls from disk: Put this into item upgrade schema's before Mojang makes something with a non 0 default state
		if($blockStateData === null && ($blockId = $this->blockItemIdMap->lookupBlockId($newNameId)) !== null){
			$networkRuntimeId = $this->blockStateDictionary->lookupStateIdFromIdMeta($blockId, 0);

			if($networkRuntimeId === null){
				throw new SavedDataLoadingException("Failed to find blockstate for blockitem $newNameId");
			}

			$blockStateData = $this->blockStateDictionary->generateDataFromStateId($networkRuntimeId);
		}

		//TODO: this won't account for spawn eggs from before 1.16.100 - perhaps we're lucky and they just left the meta in there anyway?
		//TODO: read version from VersionInfo::TAG_WORLD_DATA_VERSION - we may need it to fix up old items

		return new SavedItemData($newNameId, $newMeta, $blockStateData, $tag->getCompoundTag(SavedItemData::TAG_TAG));
	}

	/**
	 * @return string[]
	 * @throws SavedDataLoadingException
	 */
	private static function deserializeListOfStrings(?ListTag $list, string $tagName) : array{
		if($list === null){
			return [];
		}
		if($list->getTagType() !== NBT::TAG_String){
			throw new SavedDataLoadingException("Unexpected type of list for tag '$tagName', expected TAG_String");
		}
		$result = [];
		foreach($list as $item){
			assert($item instanceof StringTag);
			$result[] = $item->getValue();
		}

		return $result;
	}

	/**
	 * @throws SavedDataLoadingException
	 */
	public function upgradeItemStackNbt(CompoundTag $tag) : ?SavedItemStackData{
		$savedItemData = $this->upgradeItemTypeNbt($tag);
		if($savedItemData === null){
			//air - this isn't supposed to be saved, but older versions of PM saved it in some places
			return null;
		}
		try{
			//required
			$count = Binary::unsignByte($tag->getByte(SavedItemStackData::TAG_COUNT));

			//optional
			$slot = ($slotTag = $tag->getTag(SavedItemStackData::TAG_SLOT)) instanceof ByteTag ? Binary::unsignByte($slotTag->getValue()) : null;
			$wasPickedUp = ($wasPickedUpTag = $tag->getTag(SavedItemStackData::TAG_WAS_PICKED_UP)) instanceof ByteTag ? $wasPickedUpTag->getValue() : null;
			$canPlaceOnList = $tag->getListTag(SavedItemStackData::TAG_CAN_PLACE_ON);
			$canDestroyList = $tag->getListTag(SavedItemStackData::TAG_CAN_DESTROY);
		}catch(NbtException $e){
			throw new SavedDataLoadingException($e->getMessage(), 0, $e);
		}

		return new SavedItemStackData(
			$savedItemData,
			$count,
			$slot,
			$wasPickedUp !== 0,
			self::deserializeListOfStrings($canPlaceOnList, SavedItemStackData::TAG_CAN_PLACE_ON),
			self::deserializeListOfStrings($canDestroyList, SavedItemStackData::TAG_CAN_DESTROY)
		);
	}

	public function getIdMetaUpgrader() : ItemIdMetaUpgrader{ return $this->idMetaUpgrader; }
}
