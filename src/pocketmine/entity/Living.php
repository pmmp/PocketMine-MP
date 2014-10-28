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
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Short;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\Server;
use pocketmine\utils\BlockIterator;

abstract class Living extends Entity implements Damageable{

	protected $gravity = 0.08;
	protected $drag = 0.02;

	protected $attackTime = 0;

	protected function initEntity(){
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

	public function attack($damage, $source = EntityDamageEvent::CAUSE_MAGIC){
		if($this->attackTime > 0){
			$lastCause = $this->getLastDamageCause();
			if($lastCause instanceof EntityDamageEvent and $lastCause->getDamage() >= $damage){
				return;
			}
		}

		$pk = EntityEventPacket::getFromPool();
		$pk->eid = $this->getID();
		$pk->event = 2; //Ouch!
		Server::broadcastPacket($this->hasSpawned, $pk);
		$this->setLastDamageCause($source);

		if($source instanceof EntityDamageByEntityEvent){
			$e = $source->getDamager();
			$deltaX = $this->x - $e->x;
			$deltaZ = $this->z - $e->z;
			$yaw = atan2($deltaX, $deltaZ);
			$this->knockBack($e, $damage, sin($yaw), cos($yaw));
		}

		$this->setHealth($this->getHealth() - $damage);

		$this->attackTime = 10; //0.5 seconds cooldown
	}

	public function knockBack(Entity $attacker, $damage, $x, $z){
		$f = sqrt($x ** 2 + $z ** 2);
		$base = 0.4;

		$motion = Vector3::createVector($this->motionX, $this->motionY, $this->motionZ);

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

	public function heal($amount, $source = EntityRegainHealthEvent::CAUSE_MAGIC){
		$this->setHealth($this->getHealth() + $amount);
	}

	public function kill(){
		if($this->dead){
			return;
		}
		parent::kill();
		$this->server->getPluginManager()->callEvent($ev = EntityDeathEvent::createEvent($this, $this->getDrops()));
		foreach($ev->getDrops() as $item){
			$this->getLevel()->dropItem($this, $item);
		}
	}

	public function entityBaseTick($tickDiff = 1){
		Timings::$timerEntityBaseTick->startTiming();
		parent::entityBaseTick();

		if($this->dead !== true and $this->isInsideOfSolid()){
			$this->server->getPluginManager()->callEvent($ev = EntityDamageEvent::createEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 1));
			if(!$ev->isCancelled()){
				$this->attack($ev->getFinalDamage(), $ev);
			}
		}

		if($this->dead !== true and $this->isInsideOfWater()){
			$this->airTicks -= $tickDiff;
			if($this->airTicks <= -20){
				$this->airTicks = 0;

				$this->server->getPluginManager()->callEvent($ev = EntityDamageEvent::createEvent($this, EntityDamageEvent::CAUSE_DROWNING, 2));
				if(!$ev->isCancelled()){
					$this->attack($ev->getFinalDamage(), $ev);
				}
			}
		}else{
			$this->airTicks = 300;
		}

		if($this->attackTime > 0){
			$this->attackTime -= $tickDiff;
		}

		Timings::$timerEntityBaseTick->stopTiming();
	}

	/**
	 * @return Item[]
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
		$itr = new BlockIterator($this->level, $this->getPosition(), $this->getDirectionVector(), $this->getEyeHeight(), $maxDistance);

		while($itr->valid()){
			$itr->next();
			$block = $itr->current();
			$blocks[] = $block;

			if($maxLength !== 0 and count($blocks) > $maxLength){
				array_shift($blocks);
			}

			$id = $block->getID();

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
		$block = array_shift($this->getLineOfSight($maxDistance, 1, $transparent));
		if($block instanceof Block){
			return $block;
		}

		return null;
	}
}
