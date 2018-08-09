<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;

/**
 * Called when an entity takes damage from an entity sourced from another entity, for example being hit by a snowball thrown by a Player.
 */
class EntityDamageByChildEntityEvent extends EntityDamageByEntityEvent{
	/** @var int */
	private $childEntityEid;

	/**
	 * @param Entity  $damager
	 * @param Entity  $childEntity
	 * @param Entity  $entity
	 * @param int     $cause
	 * @param float   $damage
	 * @param float[] $modifiers
	 */
	public function __construct(Entity $damager, Entity $childEntity, Entity $entity, int $cause, float $damage, array $modifiers = []){
		$this->childEntityEid = $childEntity->getId();
		parent::__construct($damager, $entity, $cause, $damage, $modifiers);
	}

	/**
	 * Returns the entity which caused the damage, or null if the entity has been killed or closed.
	 *
	 * @return Entity|null
	 */
	public function getChild() : ?Entity{
		return $this->getEntity()->getLevel()->getServer()->findEntity($this->childEntityEid, $this->getEntity()->getLevel());
	}
}