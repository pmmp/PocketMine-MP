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
	private $horizontalKnockback;
	/** @var float|null */
	private $verticalKnockback;

	/**
	 * @param float[] $modifiers
	 */
	public function __construct(Entity $damager, Entity $entity, int $cause, float $damage, array $modifiers = [], float $horizontalKnockback = 0.4, ?float $verticalKnockback = null){
		$this->damagerEntityId = $damager->getId();
		$this->horizontalKnockback = $horizontalKnockback;
		$this->verticalKnockback = $verticalKnockback;
		parent::__construct($entity, $cause, $damage, $modifiers);
		$this->addAttackerModifiers($damager);
	}

	protected function addAttackerModifiers(Entity $damager) : void{
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
	 */
	public function getDamager() : ?Entity{
		return $this->getEntity()->getLevelNonNull()->getServer()->findEntity($this->damagerEntityId);
	}

	/**
	 * Returns the horizontal Knockback of the Event.
	 * Use the function getHorizontalKnockback().
	 
	 * @return float - The horizontal knockback.
	 * @deprecated
	 */
	public function getKnockBack() : float{
		return $this->horizontalKnockback;
	}
	
	/**
	 * Returns the horizontal knockback value.
	 * @return float - The horizontal knockback.
	 */
	public function getHorizontalKnockback(): float {
		return $this->horizontalKnockback;
	}
	
	/**
	 * Returns the vertical knockback value. If internal knockback value
	 * is null, it returns the horizontal knockback.
	 * @return float - The vertical knockback.
	 */
	public function getVerticalKnockback(): float {
		return $this->verticalKnockback ?? $this->horizontalKnockback;
	}

	/**
	 * Sets the horizontal and vertical knockback.
     * - If vertical knockback isn't filled, the vertical knockback
     *   is set to the horizontal knockback, which is the same functionality
     *   as before.
	 *
	 * @param float $horizontalKnockback - The horizontal knockback of the event.
	 * @param float|null $verticalKnockback - The vertical knockback of the event.
	 */
	public function setKnockBack(float $horizontalKnockback, ?float $verticalKnockback = null) : void{
		// Sets vertical and horizontal kb.
	    $this->horizontalKnockback = $horizontalKnockback;
		$this->verticalKnockback = ($verticalKnockback ?? $horizontalKnockback);
	}
	
	/**
	 * Sets the horizontal knockback of the event directly. Added for clarification purposes.
	 * @param float - The horizontal knockback of the event.
	 */
	public function setHorizontalKnockback(float $horizontalKnockback): void {
	    // Sets the vertical knockback to the previous horizontal knockback.
	    if($this->verticalKnockback === null)
        {
            $this->verticalKnockback = $this->horizontalKnockback;
        }
		$this->horizontalKnockback = $horizontalKnockback;
	}
	
	/** 
	 * Sets the vertical knockback of the event directly without needing
	 * to also set the horizontal knockback.
	 * @param $verticalKnockback - The vertical knockback.
	 */
	public function setVerticalKnockback(float $verticalKnockback): void {
		$this->verticalKnockback = $verticalKnockback;
	}
}
