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

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Facing;
use pocketmine\world\sound\BucketEmptyLavaSound;
use pocketmine\world\sound\BucketFillLavaSound;
use pocketmine\world\sound\Sound;

class Lava extends Liquid{

	public function getLightLevel() : int{
		return 15;
	}

	public function getBucketFillSound() : Sound{
		return new BucketFillLavaSound();
	}

	public function getBucketEmptySound() : Sound{
		return new BucketEmptyLavaSound();
	}

	public function tickRate() : int{
		return 30;
	}

	public function getFlowDecayPerBlock() : int{
		return 2; //TODO: this is 1 in the nether
	}

	protected function checkForHarden() : bool{
		if($this->falling){
			return false;
		}
		$colliding = null;
		foreach(Facing::ALL as $side){
			if($side === Facing::DOWN){
				continue;
			}
			$blockSide = $this->getSide($side);
			if($blockSide instanceof Water){
				$colliding = $blockSide;
				break;
			}
		}

		if($colliding !== null){
			if($this->decay === 0){
				$this->liquidCollide($colliding, VanillaBlocks::OBSIDIAN());
				return true;
			}elseif($this->decay <= 4){
				$this->liquidCollide($colliding, VanillaBlocks::COBBLESTONE());
				return true;
			}
		}
		return false;
	}

	protected function flowIntoBlock(Block $block, int $newFlowDecay, bool $falling) : void{
		if($block instanceof Water){
			$block->liquidCollide($this, VanillaBlocks::STONE());
		}else{
			parent::flowIntoBlock($block, $newFlowDecay, $falling);
		}
	}

	public function onEntityInside(Entity $entity) : bool{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_LAVA, 4);
		$entity->attack($ev);

		//in java burns entities for 15 seconds - seems to be a parity issue in bedrock
		$ev = new EntityCombustByBlockEvent($this, $entity, 8);
		$ev->call();
		if(!$ev->isCancelled()){
			$entity->setOnFire($ev->getDuration());
		}

		$entity->resetFallDistance();
		return true;
	}
}
