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
use pocketmine\block\utils\SupportType;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Potion;
use pocketmine\item\PotionType;
use pocketmine\item\SplashPotion;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function assert;

final class Cauldron extends Transparent{

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileCauldron);

		//empty cauldrons don't use this information
		$tile->setCustomWaterColor(null);
		$tile->setPotionItem(null);
	}

	protected function recalculateCollisionBoxes() : array{
		$result = [
			AxisAlignedBB::one()->trim(Facing::UP, 11 / 16) //bottom of the cauldron
		];

		foreach(Facing::HORIZONTAL as $f){ //add the frame parts around the bowl
			$result[] = AxisAlignedBB::one()->trim($f, 14 / 16);
		}
		return $result;
	}

	public function getSupportType(int $facing) : SupportType{
		return $facing === Facing::UP ? SupportType::EDGE : SupportType::NONE;
	}

	/**
	 * @param Item[] &$returnedItems
	 */
	private function fill(int $amount, FillableCauldron $result, Item $usedItem, Item $returnedItem, array &$returnedItems) : void{
		$this->position->getWorld()->setBlock($this->position, $result->setFillLevel($amount));
		$this->position->getWorld()->addSound($this->position->add(0.5, 0.5, 0.5), $result->getFillSound());

		$usedItem->pop();
		$returnedItems[] = $returnedItem;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item->getTypeId() === ItemTypeIds::WATER_BUCKET){
			$this->fill(FillableCauldron::MAX_FILL_LEVEL, VanillaBlocks::WATER_CAULDRON(), $item, VanillaItems::BUCKET(), $returnedItems);
		}elseif($item->getTypeId() === ItemTypeIds::LAVA_BUCKET){
			$this->fill(FillableCauldron::MAX_FILL_LEVEL, VanillaBlocks::LAVA_CAULDRON(), $item, VanillaItems::BUCKET(), $returnedItems);
		}elseif($item->getTypeId() === ItemTypeIds::POWDER_SNOW_BUCKET){
			//TODO: powder snow cauldron
		}elseif($item instanceof Potion || $item instanceof SplashPotion){ //TODO: lingering potion
			if($item->getType() === PotionType::WATER){
				$this->fill(WaterCauldron::WATER_BOTTLE_FILL_AMOUNT, VanillaBlocks::WATER_CAULDRON(), $item, VanillaItems::GLASS_BOTTLE(), $returnedItems);
			}else{
				$this->fill(PotionCauldron::POTION_FILL_AMOUNT, VanillaBlocks::POTION_CAULDRON()->setPotionItem($item), $item, VanillaItems::GLASS_BOTTLE(), $returnedItems);
			}
		}

		return true;
	}

	public function onNearbyBlockChange() : void{
		$world = $this->position->getWorld();
		if($world->getBlock($this->position->up())->getTypeId() === BlockTypeIds::WATER){
			$cauldron = VanillaBlocks::WATER_CAULDRON()->setFillLevel(FillableCauldron::MAX_FILL_LEVEL);
			$world->setBlock($this->position, $cauldron);
			$world->addSound($this->position->add(0.5, 0.5, 0.5), $cauldron->getFillSound());
		}
	}
}
