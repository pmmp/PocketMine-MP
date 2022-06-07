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

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;

/**
 * Called when an entity takes damage from another entity.
 */
class EntityDamageByEntityEvent extends EntityDamageEvent{
	private int $damagerEntityId;

	/**
	 * @param float[] $modifiers
	 */
	public function __construct(Entity $damager, Entity $entity, int $cause, float $damage, array $modifiers = [], private float $knockBack = 0.4){
		$this->damagerEntityId = $damager->getId();
		parent::__construct($entity, $cause, $damage, $modifiers);
		$this->addAttackerModifiers($damager);
	}

	protected function addAttackerModifiers(Entity $damager) : void{
		if($damager instanceof Living){ //TODO: move this to entity classes
			$effects = $damager->getEffects();
			if(($strength = $effects->get(VanillaEffects::STRENGTH())) !== null){
				$this->setModifier($this->getBaseDamage() * 0.3 * $strength->getEffectLevel(), self::MODIFIER_STRENGTH);
			}

			if(($weakness = $effects->get(VanillaEffects::WEAKNESS())) !== null){
				$this->setModifier(-($this->getBaseDamage() * 0.2 * $weakness->getEffectLevel()), self::MODIFIER_WEAKNESS);
			}
		}
	}

	/**
	 * Returns the attacking entity, or null if the attacker has been killed or closed.
	 */
	public function getDamager() : ?Entity{
		return $this->getEntity()->getWorld()->getServer()->getWorldManager()->findEntity($this->damagerEntityId);
	}

	public function getKnockBack() : float{
		return $this->knockBack;
	}

	public function setKnockBack(float $knockBack) : void{
		$this->knockBack = $knockBack;
	}
}
