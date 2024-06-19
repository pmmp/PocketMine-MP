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

use pocketmine\block\Block;
use pocketmine\block\FillableCauldron;
use pocketmine\color\Color;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Potion;
use pocketmine\item\SplashPotion;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Binary;

final class Cauldron extends Spawnable{

	private const POTION_CONTAINER_TYPE_NONE = -1;
	private const POTION_CONTAINER_TYPE_NORMAL = 0;
	private const POTION_CONTAINER_TYPE_SPLASH = 1;
	private const POTION_CONTAINER_TYPE_LINGERING = 2;

	private const POTION_ID_NONE = -1;

	private const TAG_POTION_ID = "PotionId"; //TAG_Short
	private const TAG_POTION_CONTAINER_TYPE = "PotionType"; //TAG_Short
	private const TAG_CUSTOM_COLOR = "CustomColor"; //TAG_Int

	private ?Item $potionItem = null;
	private ?Color $customWaterColor = null;

	public function getPotionItem() : ?Item{ return $this->potionItem; }

	public function setPotionItem(?Item $potionItem) : void{
		$this->potionItem = $potionItem;
	}

	public function getCustomWaterColor() : ?Color{ return $this->customWaterColor; }

	public function setCustomWaterColor(?Color $customWaterColor) : void{
		$this->customWaterColor = $customWaterColor;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setShort(self::TAG_POTION_CONTAINER_TYPE, match($this->potionItem?->getTypeId()){
			ItemTypeIds::POTION => self::POTION_CONTAINER_TYPE_NORMAL,
			ItemTypeIds::SPLASH_POTION => self::POTION_CONTAINER_TYPE_SPLASH,
			ItemTypeIds::LINGERING_POTION => self::POTION_CONTAINER_TYPE_LINGERING,
			null => self::POTION_CONTAINER_TYPE_NONE,
			default => throw new AssumptionFailedError("Unexpected potion item type")
		});

		//TODO: lingering potion
		$type = $this->potionItem instanceof Potion || $this->potionItem instanceof SplashPotion ? $this->potionItem->getType() : null;
		$nbt->setShort(self::TAG_POTION_ID, $type === null ? self::POTION_ID_NONE : PotionTypeIdMap::getInstance()->toId($type));

		if($this->customWaterColor !== null){
			$nbt->setInt(self::TAG_CUSTOM_COLOR, Binary::signInt($this->customWaterColor->toARGB()));
		}
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$containerType = $nbt->getShort(self::TAG_POTION_CONTAINER_TYPE, self::POTION_CONTAINER_TYPE_NONE);
		$potionId = $nbt->getShort(self::TAG_POTION_ID, self::POTION_ID_NONE);
		if($containerType !== self::POTION_CONTAINER_TYPE_NONE && $potionId !== self::POTION_ID_NONE){
			$potionType = PotionTypeIdMap::getInstance()->fromId($potionId);
			if($potionType === null){
				throw new SavedDataLoadingException("Unknown potion type ID $potionId");
			}
			$this->potionItem = match($containerType){
				self::POTION_CONTAINER_TYPE_NORMAL => VanillaItems::POTION()->setType($potionType),
				self::POTION_CONTAINER_TYPE_SPLASH => VanillaItems::SPLASH_POTION()->setType($potionType),
				self::POTION_CONTAINER_TYPE_LINGERING => throw new SavedDataLoadingException("Not implemented"),
				default => throw new SavedDataLoadingException("Invalid potion container type ID $containerType")
			};
		}else{
			$this->potionItem = null;
		}

		$this->customWaterColor = ($customColorTag = $nbt->getTag(self::TAG_CUSTOM_COLOR)) instanceof IntTag ? Color::fromARGB(Binary::unsignInt($customColorTag->getValue())) : null;
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setShort(self::TAG_POTION_CONTAINER_TYPE, match($this->potionItem?->getTypeId()){
			ItemTypeIds::POTION => self::POTION_CONTAINER_TYPE_NORMAL,
			ItemTypeIds::SPLASH_POTION => self::POTION_CONTAINER_TYPE_SPLASH,
			ItemTypeIds::LINGERING_POTION => self::POTION_CONTAINER_TYPE_LINGERING,
			null => self::POTION_CONTAINER_TYPE_NONE,
			default => throw new AssumptionFailedError("Unexpected potion item type")
		});

		//TODO: lingering potion
		$type = $this->potionItem instanceof Potion || $this->potionItem instanceof SplashPotion ? $this->potionItem->getType() : null;
		$nbt->setShort(self::TAG_POTION_ID, $type === null ? self::POTION_ID_NONE : PotionTypeIdMap::getInstance()->toId($type));

		if($this->customWaterColor !== null){
			$nbt->setInt(self::TAG_CUSTOM_COLOR, Binary::signInt($this->customWaterColor->toARGB()));
		}
	}

	public function getRenderUpdateBugWorkaroundStateProperties(Block $block) : array{
		if($block instanceof FillableCauldron){
			$realFillLevel = $block->getFillLevel();
			return [BlockStateNames::FILL_LEVEL => new IntTag($realFillLevel === FillableCauldron::MAX_FILL_LEVEL ? FillableCauldron::MIN_FILL_LEVEL : $realFillLevel + 1)];
		}

		return [];
	}
}
