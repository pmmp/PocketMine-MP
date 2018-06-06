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

namespace pocketmine\event\entity;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;

/**
 * Called when an entity takes damage from another entity.
 */
class EntityDamageByEntityEvent extends EntityDamageEvent{
	/** @var int */
	private $damagerEntityId;
	/** @var float */
	private $knockBack;

	/**
	 * @param Entity  $damager
	 * @param Entity  $entity
	 * @param int     $cause
	 * @param float   $damage
	 * @param float[] $modifiers
	 * @param float   $knockBack
	 */
	public function __construct(Entity $damager, Entity $entity, int $cause, float $damage, array $modifiers = [], float $knockBack = 0.4){
		$this->damagerEntityId = $damager->getId();
		$this->knockBack = $knockBack;
		parent::__construct($entity, $cause, $damage, $modifiers);
		$this->addAttackerModifiers($damager);
	}

	protected function addAttackerModifiers(Entity $damager){
		if($damager instanceof Living){ //TODO: move this to entity classes
			if($damager->hasEffect(Effect::STRENGTH)){
				$this->setModifier($this->getBaseDamage() * 0.3 * $damager->getEffect(Effect::STRENGTH)->getEffectLevel(), self::MODIFIER_STRENGTH);
			}

			if($damager->hasEffect(Effect::WEAKNESS)){
				$this->setModifier(-($this->getBaseDamage() * 0.2 * $damager->getEffect(Effect::WEAKNESS)->getEffectLevel()), self::MODIFIER_WEAKNESS);
			}
		}
	}

	/**
	 * Returns the attacking entity, or null if the attacker has been killed or closed.
	 *
	 * @return Entity|null
	 */
	public function getDamager(){
		return $this->getEntity()->getLevel()->getServer()->findEntity($this->damagerEntityId, $this->getEntity()->getLevel());
	}

	/**
	 * @return float
	 */
	public function getKnockBack() : float{
		return $this->knockBack;
	}

	/**
	 * @param float $knockBack
	 */
	public function setKnockBack(float $knockBack){
		$this->knockBack = $knockBack;
	}
}
