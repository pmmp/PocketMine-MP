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

namespace pocketmine\entity;


use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Timings;
use pocketmine\item\Item as ItemItem;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Short;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\BlockIterator;

abstract class Living extends Entity implements Damageable{

	protected $gravity = 0.08;
	protected $drag = 0.02;

	protected $attackTime = 0;
	
	protected $invisible = false;

	protected function initEntity(){
		parent::initEntity();

		if(isset($this->namedtag->HealF)){
			$this->namedtag->Health = new Short("Health", (int) $this->namedtag["HealF"]);
			unset($this->namedtag->HealF);
		}

		if(!isset($this->namedtag->Health) or !($this->namedtag->Health instanceof Short)){
			$this->namedtag->Health = new Short("Health", $this->getMaxHealth());
		}

		$this->setHealth($this->namedtag["Health"]);
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->Health = new Short("Health", $this->getHealth());
	}

	public abstract function getName();

	public function hasLineOfSight(Entity $entity){
		//TODO: head height
		return true;
		//return $this->getLevel()->rayTraceBlocks(Vector3::createVector($this->x, $this->y + $this->height, $this->z), Vector3::createVector($entity->x, $entity->y + $entity->height, $entity->z)) === null;
	}

	public function attack($damage, EntityDamageEvent $source){
		if($this->attackTime > 0 or $this->noDamageTicks > 0){
			$lastCause = $this->getLastDamageCause();
			if($lastCause !== null and $lastCause->getDamage() >= $damage){
                $source->setCancelled();
			}
		}

        parent::attack($damage, $source);

        if($source->isCancelled()){
            return;
        }

		if($source instanceof EntityDamageByEntityEvent){
			$e = $source->getDamager();
			$deltaX = $this->x - $e->x;
			$deltaZ = $this->z - $e->z;
			$yaw = atan2($deltaX, $deltaZ);
			$this->knockBack($e, $damage, sin($yaw), cos($yaw), $source->getKnockBack());
		}

		$pk = new EntityEventPacket();
		$pk->eid = $this->getId();
		$pk->event = $this->getHealth() <= 0 ? 3 : 2; //Ouch!
		Server::broadcastPacket($this->hasSpawned, $pk);

		$this->attackTime = 10; //0.5 seconds cooldown
	}

	public function knockBack(Entity $attacker, $damage, $x, $z, $base = 0.4){
		$f = sqrt($x ** 2 + $z ** 2);

		$motion = new Vector3($this->motionX, $this->motionY, $this->motionZ);

		$motion->x /= 2;
		$motion->y /= 2;
		$motion->z /= 2;
		$motion->x += ($x / $f) * $base;
		$motion->y += $base;
		$motion->z += ($z / $f) * $base;

		if($motion->y > $base){
			$motion->y = $base;
		}

		$this->setMotion($motion);
	}

	public function kill(){
		if($this->dead){
			return;
		}
		parent::kill();
		$this->server->getPluginManager()->callEvent($ev = new EntityDeathEvent($this, $this->getDrops()));
		foreach($ev->getDrops() as $item){
			$this->getLevel()->dropItem($this, $item);
		}
	}

	public function entityBaseTick($tickDiff = 1){
		Timings::$timerEntityBaseTick->startTiming();

		if($this->dead === true){
			++$this->deadTicks;
			if($this->deadTicks >= 10){
				$this->despawnFromAll();
				if(!($this instanceof Player)){
					$this->close();
				}
			}

			Timings::$timerEntityBaseTick->stopTiming();
			return $this->deadTicks < 10;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->dead !== true and $this->isInsideOfSolid()){
			$hasUpdate = true;
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 1);
			$this->attack($ev->getFinalDamage(), $ev);
		}

		if($this->dead !== true and !$this->hasEffect(Effect::WATER_BREATHING) and $this->isInsideOfWater()){
			$hasUpdate = true;
			$airTicks = $this->getDataProperty(self::DATA_AIR) - $tickDiff;
			if($airTicks <= -20){
				$airTicks = 0;

				$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 2);
				$this->attack($ev->getFinalDamage(), $ev);
			}
			$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $airTicks);
		}else{
			$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, 300);
		}

		if($this->attackTime > 0){
			$this->attackTime -= $tickDiff;
		}

		Timings::$timerEntityBaseTick->stopTiming();

		return $hasUpdate;
	}

	/**
	 * @return ItemItem[]
	 */
	public function getDrops(){
		return [];
	}

	/**
	 * @param int   $maxDistance
	 * @param int   $maxLength
	 * @param array $transparent
	 *
	 * @return Block[]
	 */
	public function getLineOfSight($maxDistance, $maxLength = 0, array $transparent = []){
		if($maxDistance > 120){
			$maxDistance = 120;
		}

		if(count($transparent) === 0){
			$transparent = null;
		}

		$blocks = [];
		$nextIndex = 0;

		$itr = new BlockIterator($this->level, $this->getPosition(), $this->getDirectionVector(), $this->getEyeHeight(), $maxDistance);

		while($itr->valid()){
			$itr->next();
			$block = $itr->current();
			$blocks[$nextIndex++] = $block;

			if($maxLength !== 0 and count($blocks) > $maxLength){
				array_shift($blocks);
				--$nextIndex;
			}

			$id = $block->getId();

			if($transparent === null){
				if($id !== 0){
					break;
				}
			}else{
				if(!isset($transparent[$id])){
					break;
				}
			}
		}

		return $blocks;
	}

	/**
	 * @param int   $maxDistance
	 * @param array $transparent
	 *
	 * @return Block
	 */
	public function getTargetBlock($maxDistance, array $transparent = []){
		try{
			$block = $this->getLineOfSight($maxDistance, 1, $transparent)[0];
			if($block instanceof Block){
				return $block;
			}
		}catch (\ArrayOutOfBoundsException $e){

		}

		return null;
	}
}
