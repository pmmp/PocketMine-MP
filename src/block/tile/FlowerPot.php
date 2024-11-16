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

namespace pocketmine\block\tile;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

/**
 * @deprecated
 * @see \pocketmine\block\FlowerPot
 */
class FlowerPot extends Spawnable{
	private const TAG_ITEM = "item";
	private const TAG_ITEM_DATA = "mData";
	private const TAG_PLANT_BLOCK = "PlantBlock";

	private ?Block $plant = null;

	public function readSaveData(CompoundTag $nbt) : void{
		$blockStateData = null;

		$blockDataUpgrader = GlobalBlockStateHandlers::getUpgrader();
		if(($itemIdTag = $nbt->getTag(self::TAG_ITEM)) instanceof ShortTag && ($itemMetaTag = $nbt->getTag(self::TAG_ITEM_DATA)) instanceof IntTag){
			try{
				$blockStateData = $blockDataUpgrader->upgradeIntIdMeta($itemIdTag->getValue(), $itemMetaTag->getValue());
			}catch(BlockStateDeserializeException $e){
				throw new SavedDataLoadingException("Error loading legacy flower pot item data: " . $e->getMessage(), 0, $e);
			}
		}elseif(($plantBlockTag = $nbt->getCompoundTag(self::TAG_PLANT_BLOCK)) !== null){
			try{
				$blockStateData = $blockDataUpgrader->upgradeBlockStateNbt($plantBlockTag);
			}catch(BlockStateDeserializeException $e){
				throw new SavedDataLoadingException("Error loading " . self::TAG_PLANT_BLOCK . " tag for flower pot: " . $e->getMessage(), 0, $e);
			}
		}

		if($blockStateData !== null){
			try{
				$blockStateId = GlobalBlockStateHandlers::getDeserializer()->deserialize($blockStateData);
			}catch(BlockStateDeserializeException $e){
				throw new SavedDataLoadingException("Error deserializing plant for flower pot: " . $e->getMessage(), 0, $e);
			}
			$this->setPlant(RuntimeBlockStateRegistry::getInstance()->fromStateId($blockStateId));
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		if($this->plant !== null){
			$nbt->setTag(self::TAG_PLANT_BLOCK, GlobalBlockStateHandlers::getSerializer()->serialize($this->plant->getStateId())->toNbt());
		}
	}

	public function getPlant() : ?Block{
		return $this->plant !== null ? clone $this->plant : null;
	}

	public function setPlant(?Block $plant) : void{
		if($plant === null || $plant instanceof Air){
			$this->plant = null;
		}else{
			$this->plant = clone $plant;
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->plant !== null){
			$nbt->setTag(self::TAG_PLANT_BLOCK, TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkStateData($this->plant->getStateId())->toNbt());
		}
	}

	public function getRenderUpdateBugWorkaroundStateProperties(Block $block) : array{
		return [BlockStateNames::UPDATE_BIT => new ByteTag(1)];
	}
}
