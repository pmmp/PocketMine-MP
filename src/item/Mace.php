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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\sound\MaceSmashAirSound;
use pocketmine\world\sound\MaceSmashGroundSound;

class Mace extends TieredTool{

	public const MAX_DURABILITY = 501;
	private ?Entity $holder = null;
	private const NORMAL_ATTACK_DAMAGE = 5;
	private const SMASH_ATTACK_DAMAGE = 10;
	private const SMASH_ATTACK_FALL_HEIGHT = 1.5;

	public function getBlockToolType() : int{
		return BlockToolType::NONE;
	}

	public function setHolder(Entity $entity) : void {
		$this->holder = $entity;
	}

	public function getHolder() : ?Entity {
		return $this->holder;
	}

	public function getBlockToolHarvestLevel() : int{
		return $this->tier->getHarvestLevel();
	}

	public function getMaxDurability() : int{
		return self::MAX_DURABILITY;
	}

	public function getAttackPoints() : int{
		return $this->tier->getBaseAttackPoints() - 1;
	}

	public function onDestroyBlock(Block $block, array &$returnedItems) : bool{
		$world = $block->getPosition()->getWorld();
		$position = $block->getPosition();

		if(!$block->getBreakInfo()->breaksInstantly()){
			$world->addSound($position, new MaceSmashAirSound());
			return $this->applyDamage(1);
		}
		return false;
	}

	public function onAttackEntity(Entity $victim, array &$returnedItems) : bool{
		$world = $victim->getWorld();
		$position = $victim->getPosition();
		$holder = $this->getHolder();

		if($holder instanceof Player){
			$fallDistance = $holder->getFallDistance();

			if($fallDistance >= self::SMASH_ATTACK_FALL_HEIGHT){

				$damage = self::SMASH_ATTACK_DAMAGE + (int)($fallDistance * 2);
				$world->addSound($position, new MaceSmashGroundSound());
				$world->addParticle($position, new HugeExplodeParticle());
				$holder->resetFallDistance();

				foreach($victim->getWorld()->getNearbyEntities($victim->getBoundingBox()->expandedCopy(3, 3, 3)) as $nearbyEntity){
					if($nearbyEntity instanceof Living && $nearbyEntity !== $holder){
						$knockbackVector = $nearbyEntity->getPosition()->subtract($holder->getPosition())->normalize()->multiply(0.4);
						$nearbyEntity->knockBack($knockbackVector->x, $knockbackVector->z, 0.4);
					}
				}
				return $this->applyDamage(1);
			}
		}
		return $this->applyDamage(1);
	}
}
