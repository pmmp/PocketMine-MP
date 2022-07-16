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

	private function fill(int $amount, Item $item, ?Player $player, FillableCauldron $result, Item $returnedItem) : void{
		$this->position->getWorld()->setBlock($this->position, $result->setFillLevel($amount));
		//TODO: sounds

		$item->pop();
		$player?->getInventory()->addItem($returnedItem);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item->getTypeId() === ItemTypeIds::WATER_BUCKET){
			$this->fill(FillableCauldron::MAX_FILL_LEVEL, $item, $player, VanillaBlocks::WATER_CAULDRON(), VanillaItems::BUCKET());
		}elseif($item->getTypeId() === ItemTypeIds::LAVA_BUCKET){
			$this->fill(FillableCauldron::MAX_FILL_LEVEL, $item, $player, VanillaBlocks::LAVA_CAULDRON(), VanillaItems::BUCKET());
		}elseif($item->getTypeId() === ItemTypeIds::POWDER_SNOW_BUCKET){
			//TODO: powder snow cauldron
		}elseif($item instanceof Potion || $item instanceof SplashPotion){ //TODO: lingering potion
			if($item->getType()->equals(PotionType::WATER())){
				$this->fill(WaterCauldron::WATER_BOTTLE_FILL_AMOUNT, $item, $player, VanillaBlocks::WATER_CAULDRON(), VanillaItems::GLASS_BOTTLE());
			}else{
				$this->fill(PotionCauldron::POTION_FILL_AMOUNT, $item, $player, VanillaBlocks::POTION_CAULDRON()->setPotionItem($item), VanillaItems::GLASS_BOTTLE());
			}
		}

		return true;
	}
}
