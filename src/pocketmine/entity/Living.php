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

abstract class Living extends Entity implements Damageable{

	protected $gravity = 0.08;
	protected $drag = 0.02;

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

	public function attack($damage, $source = EntityDamageEvent::CAUSE_MAGIC){
		//TODO: attack tick limit
		$pk = new EntityEventPacket();
		$pk->eid = $this->getID();
		$pk->event = 2; //Ouch!
		Server::broadcastPacket($this->hasSpawned, $pk);
		$this->setLastDamageCause($source);
		$motion = new Vector3(0, 0, 0);
		if($source instanceof EntityDamageByEntityEvent){
			$e = $source->getDamager();
			$deltaX = $this->x - $e->x;
			$deltaZ = $this->z - $e->z;
			$yaw = atan2($deltaX, $deltaZ);
			$motion->x = sin($yaw) * 0.5;
			$motion->z = cos($yaw) * 0.5;
		}
		$this->setMotion($motion);
		$this->setHealth($this->getHealth() - $damage);

	}

	public function heal($amount){
		$this->server->getPluginManager()->callEvent($ev = new EntityRegainHealthEvent($this, $amount));
		if($ev->isCancelled()){
			return;
		}
		$this->setHealth($this->getHealth() + $amount);
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

	public function entityBaseTick(){
		Timings::$timerEntityBaseTick->startTiming();
		parent::entityBaseTick();
		Timings::$timerEntityBaseTick->stopTiming();
	}

	/**
	 * @return Item[]
	 */
	public function getDrops(){
		return [];
	}
}
