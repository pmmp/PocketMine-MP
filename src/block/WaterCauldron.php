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

namespace pocketmine\block;

use pocketmine\block\tile\Cauldron as TileCauldron;
use pocketmine\block\utils\DyeColor;
use pocketmine\color\Color;
use pocketmine\entity\Entity;
use pocketmine\item\Armor;
use pocketmine\item\Banner;
use pocketmine\item\Dye;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Potion;
use pocketmine\item\PotionType;
use pocketmine\item\SplashPotion;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\CauldronAddDyeSound;
use pocketmine\world\sound\CauldronCleanItemSound;
use pocketmine\world\sound\CauldronDyeItemSound;
use pocketmine\world\sound\CauldronEmptyWaterSound;
use pocketmine\world\sound\CauldronFillWaterSound;
use pocketmine\world\sound\Sound;
use function array_pop;
use function assert;
use function count;

final class WaterCauldron extends FillableCauldron{

	public const WATER_BOTTLE_FILL_AMOUNT = 2;

	public const DYE_ARMOR_USE_AMOUNT = 1;
	public const CLEAN_ARMOR_USE_AMOUNT = 1;
	public const CLEAN_BANNER_USE_AMOUNT = 1;
	public const CLEAN_SHULKER_BOX_USE_AMOUNT = 1;

	//TODO: I'm not sure if this was intended to be 2 (to match java) but in Bedrock you can extinguish yourself 6 times ...
	public const ENTITY_EXTINGUISH_USE_AMOUNT = 1;

	private ?Color $customWaterColor = null;

	public function readStateFromWorld() : Block{
		$result = parent::readStateFromWorld();
		if($result !== $this){
			return $result;
		}

		$tile = $this->position->getWorld()->getTile($this->position);

		$potionItem = $tile instanceof TileCauldron ? $tile->getPotionItem() : null;
		if($potionItem !== null){
			//TODO: HACK! we keep potion cauldrons as a separate block type due to different behaviour, but in the
			//blockstate they are typically indistinguishable from water cauldrons. This hack converts cauldrons into
			//their appropriate type.
			return VanillaBlocks::POTION_CAULDRON()->setFillLevel($this->getFillLevel())->setPotionItem($potionItem);
		}

		$this->customWaterColor = $tile instanceof TileCauldron ? $tile->getCustomWaterColor() : null;

		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileCauldron);
		$tile->setCustomWaterColor($this->customWaterColor);
		$tile->setPotionItem(null);
	}

	public function getCustomWaterColor() : ?Color{ return $this->customWaterColor; }

	/** @return $this */
	public function setCustomWaterColor(?Color $customWaterColor) : self{
		$this->customWaterColor = $customWaterColor;
		return $this;
	}

	public function getFillSound() : Sound{
		return new CauldronFillWaterSound();
	}

	public function getEmptySound() : Sound{
		return new CauldronEmptyWaterSound();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		$world = $this->position->getWorld();
		if(($dyeColor = match($item->getTypeId()){
				ItemTypeIds::LAPIS_LAZULI => DyeColor::BLUE,
				ItemTypeIds::INK_SAC => DyeColor::BLACK,
				ItemTypeIds::COCOA_BEANS => DyeColor::BROWN,
				ItemTypeIds::BONE_MEAL => DyeColor::WHITE,
				ItemTypeIds::DYE => $item instanceof Dye ? $item->getColor() : null,
				default => null
			}) !== null && ($newColor = $dyeColor->getRgbValue())->toRGBA() !== $this->customWaterColor?->toRGBA()
		){
			$world->setBlock($this->position, $this->setCustomWaterColor($this->customWaterColor === null ? $newColor : Color::mix($this->customWaterColor, $newColor)));
			$world->addSound($this->position->add(0.5, 0.5, 0.5), new CauldronAddDyeSound());

			$item->pop();
		}elseif($item instanceof Potion || $item instanceof SplashPotion){ //TODO: lingering potion
			if($item->getType() === PotionType::WATER){
				$this->setCustomWaterColor(null)->addFillLevels(self::WATER_BOTTLE_FILL_AMOUNT, $item, VanillaItems::GLASS_BOTTLE(), $returnedItems);
			}else{
				$this->mix($item, VanillaItems::GLASS_BOTTLE(), $returnedItems);
			}
		}elseif($item instanceof Armor){
			if($this->customWaterColor !== null){
				if(match($item->getTypeId()){ //TODO: a DyeableArmor class would probably be a better idea, since not all types of armor are dyeable
					ItemTypeIds::LEATHER_CAP,
					ItemTypeIds::LEATHER_TUNIC,
					ItemTypeIds::LEATHER_PANTS,
					ItemTypeIds::LEATHER_BOOTS => true,
					default => false
				} && $item->getCustomColor()?->toRGBA() !== $this->customWaterColor->toRGBA()){
					$item->setCustomColor($this->customWaterColor);
					$world->setBlock($this->position, $this->withFillLevel($this->getFillLevel() - self::DYE_ARMOR_USE_AMOUNT));
					$world->addSound($this->position->add(0.5, 0.5, 0.5), new CauldronDyeItemSound());
				}
			}elseif($item->getCustomColor() !== null){
				$item->clearCustomColor();
				$world->setBlock($this->position, $this->withFillLevel($this->getFillLevel() - self::CLEAN_ARMOR_USE_AMOUNT));
				$world->addSound($this->position->add(0.5, 0.5, 0.5), new CauldronCleanItemSound());
			}
		}elseif($item instanceof Banner){
			$patterns = $item->getPatterns();
			if(count($patterns) > 0 && $this->customWaterColor === null){
				array_pop($patterns);
				$item->setPatterns($patterns);

				$world->setBlock($this->position, $this->withFillLevel($this->getFillLevel() - self::CLEAN_BANNER_USE_AMOUNT));
				$world->addSound($this->position->add(0.5, 0.5, 0.5), new CauldronCleanItemSound());
			}
		}elseif(ItemTypeIds::toBlockTypeId($item->getTypeId()) === BlockTypeIds::DYED_SHULKER_BOX){
			if($this->customWaterColor === null){
				$newItem = VanillaBlocks::SHULKER_BOX()->asItem();
				$newItem->setNamedTag($item->getNamedTag());

				$item->pop();
				$returnedItems[] = $newItem;

				$world->setBlock($this->position, $this->withFillLevel($this->getFillLevel() - self::CLEAN_SHULKER_BOX_USE_AMOUNT));
				$world->addSound($this->position->add(0.5, 0.5, 0.5), new CauldronCleanItemSound());
			}
		}else{
			match($item->getTypeId()){
				ItemTypeIds::WATER_BUCKET => $this->setCustomWaterColor(null)->addFillLevels(self::MAX_FILL_LEVEL, $item, VanillaItems::BUCKET(), $returnedItems),
				ItemTypeIds::BUCKET => $this->removeFillLevels(self::MAX_FILL_LEVEL, $item, VanillaItems::WATER_BUCKET(), $returnedItems),
				ItemTypeIds::GLASS_BOTTLE => $this->removeFillLevels(self::WATER_BOTTLE_FILL_AMOUNT, $item, VanillaItems::POTION()->setType(PotionType::WATER), $returnedItems),
				ItemTypeIds::LAVA_BUCKET, ItemTypeIds::POWDER_SNOW_BUCKET => $this->mix($item, VanillaItems::BUCKET(), $returnedItems),
				default => null
			};
		}

		return true;
	}

	public function hasEntityCollision() : bool{ return true; }

	public function onEntityInside(Entity $entity) : bool{
		if($entity->isOnFire()){
			$entity->extinguish();
			//TODO: particles

			$this->position->getWorld()->setBlock($this->position, $this->withFillLevel($this->getFillLevel() - self::ENTITY_EXTINGUISH_USE_AMOUNT));
		}

		return true;
	}

	public function onNearbyBlockChange() : void{
		$hasCustomWaterColor = $this->customWaterColor !== null;
		if($this->getFillLevel() < self::MAX_FILL_LEVEL || $hasCustomWaterColor){
			$world = $this->position->getWorld();
			if($world->getBlock($this->position->up())->getTypeId() === BlockTypeIds::WATER){
				if($hasCustomWaterColor){
					//TODO: particles
				}
				$world->setBlock($this->position, $this->setCustomWaterColor(null)->setFillLevel(FillableCauldron::MAX_FILL_LEVEL));
				$world->addSound($this->position->add(0.5, 0.5, 0.5), $this->getFillSound());
			}
		}
	}
}
