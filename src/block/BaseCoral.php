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

use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\CoralTypeTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\event\block\BlockDeathEvent;
use pocketmine\item\Item;

abstract class BaseCoral extends Transparent{
	use CoralTypeTrait;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockBreakInfo $breakInfo){
		parent::__construct($idInfo, $name, $breakInfo);
		$this->coralType = CoralType::TUBE();
	}

	public function onNearbyBlockChange() : void{
		if(!$this->dead){
			$world = $this->position->getWorld();

			$hasWater = false;
			foreach($this->position->sides() as $vector3){
				if($world->getBlock($vector3) instanceof Water){
					$hasWater = true;
					break;
				}
			}

			//TODO: check water inside the block itself (not supported on the API yet)
			if(!$hasWater){
				$ev = new BlockDeathEvent($this, $this->setDead(true));
				$ev->call();
				if(!$ev->isCancelled()){
					$world->setBlock($this->position, $ev->getNewState());
				}
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function isSolid() : bool{ return false; }

	protected function recalculateCollisionBoxes() : array{ return []; }

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}
}
