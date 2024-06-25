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

use pocketmine\block\utils\AgeableTrait;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\SupportType;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\math\Facing;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use function intdiv;
use function max;
use function min;
use function mt_rand;

class Fire extends BaseFire{
	use AgeableTrait;

	public const MAX_AGE = 15;

	protected function getFireDamage() : int{
		return 1;
	}

	private function canBeSupportedBy(Block $block) : bool{
		return $block->getSupportType(Facing::UP) === SupportType::FULL;
	}

	public function onNearbyBlockChange() : void{
		$world = $this->position->getWorld();
		$down = $this->getSide(Facing::DOWN);
		if(SoulFire::canBeSupportedBy($down)){
			$world->setBlock($this->position, VanillaBlocks::SOUL_FIRE());
		}elseif(!$this->canBeSupportedBy($this->getSide(Facing::DOWN)) && !$this->hasAdjacentFlammableBlocks()){
			$world->setBlock($this->position, VanillaBlocks::AIR());
		}else{
			$world->scheduleDelayedBlockUpdate($this->position, mt_rand(30, 40));
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$down = $this->getSide(Facing::DOWN);

		$result = null;
		if($this->age < self::MAX_AGE && mt_rand(0, 2) === 0){
			$this->age++;
			$result = $this;
		}
		$canSpread = true;

		if(!$down->burnsForever()){
			//TODO: check rain
			if($this->age === self::MAX_AGE){
				if(!$down->isFlammable() && mt_rand(0, 3) === 3){ //1/4 chance to extinguish
					$canSpread = false;
					$result = VanillaBlocks::AIR();
				}
			}elseif(!$this->hasAdjacentFlammableBlocks()){
				$canSpread = false;
				if($down->isTransparent() || $this->age > 3){
					$result = VanillaBlocks::AIR();
				}
			}
		}

		$world = $this->position->getWorld();
		if($result !== null){
			$world->setBlock($this->position, $result);
		}

		$world->scheduleDelayedBlockUpdate($this->position, mt_rand(30, 40));

		if($canSpread){
			$this->burnBlocksAround();
			$this->spreadFire();
		}
	}

	public function onScheduledUpdate() : void{
		$this->onRandomTick();
	}

	private function hasAdjacentFlammableBlocks() : bool{
		foreach(Facing::ALL as $face){
			if($this->getSide($face)->isFlammable()){
				return true;
			}
		}

		return false;
	}

	private function burnBlocksAround() : void{
		//TODO: raise upper bound for chance in humid biomes

		foreach($this->getHorizontalSides() as $side){
			$this->burnBlock($side, 300);
		}

		//vanilla uses a 250 upper bound here, but I don't think they intended to increase the chance of incineration
		$this->burnBlock($this->getSide(Facing::UP), 350);
		$this->burnBlock($this->getSide(Facing::DOWN), 350);
	}

	private function burnBlock(Block $block, int $chanceBound) : void{
		if(mt_rand(0, $chanceBound) < $block->getFlammability()){
			$cancelled = false;
			if(BlockBurnEvent::hasHandlers()){
				$ev = new BlockBurnEvent($block, $this);
				$ev->call();
				$cancelled = $ev->isCancelled();
			}
			if(!$cancelled){
				$block->onIncinerate();

				$world = $this->position->getWorld();
				if($world->getBlock($block->position)->isSameState($block)){
					$spreadedFire = false;
					if(mt_rand(0, $this->age + 9) < 5){ //TODO: check rain
						$fire = clone $this;
						$fire->age = min(self::MAX_AGE, $fire->age + (mt_rand(0, 4) >> 2));
						$spreadedFire = $this->spreadBlock($block, $fire);
					}
					if(!$spreadedFire){
						$world->setBlock($block->position, VanillaBlocks::AIR());
					}
				}
			}
		}
	}

	private function spreadFire() : void{
		$world = $this->position->getWorld();
		$difficultyChanceIncrease = $world->getDifficulty() * 7;
		$ageDivisor = $this->age + 30;

		for($y = -1; $y <= 4; ++$y){
			$targetY = $y + (int) $this->position->y;
			if($targetY < World::Y_MIN || $targetY >= World::Y_MAX){
				continue;
			}
			//Higher blocks have a lower chance of catching fire
			$randomBound = 100 + ($y > 1 ? ($y - 1) * 100 : 0);

			for($z = -1; $z <= 1; ++$z){
				$targetZ = $z + (int) $this->position->z;
				for($x = -1; $x <= 1; ++$x){
					if($x === 0 && $y === 0 && $z === 0){
						continue;
					}
					$targetX = $x + (int) $this->position->x;
					if(!$world->isInWorld($targetX, $targetY, $targetZ)){
						continue;
					}

					if(!$world->isChunkLoaded($targetX >> Chunk::COORD_BIT_SIZE, $targetZ >> Chunk::COORD_BIT_SIZE)){
						continue;
					}
					$block = $world->getBlockAt($targetX, $targetY, $targetZ);
					if($block->getTypeId() !== BlockTypeIds::AIR){
						continue;
					}

					//TODO: fire can't spread if it's raining in any horizontally adjacent block, or the current one

					$encouragement = 0;
					foreach($block->position->sides() as $vector3){
						if($world->isInWorld($vector3->x, $vector3->y, $vector3->z)){
							$encouragement = max($encouragement, $world->getBlockAt($vector3->x, $vector3->y, $vector3->z)->getFlameEncouragement());
						}
					}

					if($encouragement <= 0){
						continue;
					}

					$maxChance = intdiv($encouragement + 40 + $difficultyChanceIncrease, $ageDivisor);
					//TODO: max chance is lowered by half in humid biomes

					if($maxChance > 0 && mt_rand(0, $randomBound - 1) <= $maxChance){
						$new = clone $this;
						$new->age = min(self::MAX_AGE, $this->age + (mt_rand(0, 4) >> 2));
						$this->spreadBlock($block, $new);
					}
				}
			}
		}
	}

	private function spreadBlock(Block $block, Block $newState) : bool{
		return BlockEventHelper::spread($block, $newState, $this);
	}
}
