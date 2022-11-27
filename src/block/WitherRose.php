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

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class WitherRose extends Flowable{

	private function canBeSupportedBy(Block $block) : bool{
		return
			$block->hasTypeTag(BlockTypeTags::DIRT) ||
			$block->hasTypeTag(BlockTypeTags::MUD) ||
			match($block->getTypeId()){
				BlockTypeIds::NETHERRACK,
				BlockTypeIds::SOUL_SAND,
				BlockTypeIds::SOUL_SOIL => true,
				default => false
			};
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedBy($blockReplace->getSide(Facing::DOWN))){
			return false;
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function hasEntityCollision() : bool{ return true; }

	public function onEntityInside(Entity $entity) : bool{
		if($entity instanceof Living && !$entity->getEffects()->has(VanillaEffects::WITHER())){
			$entity->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 40));
		}
		return true;
	}

	public function getFlameEncouragement() : int{ return 60; }

	public function getFlammability() : int{ return 100; }
}
