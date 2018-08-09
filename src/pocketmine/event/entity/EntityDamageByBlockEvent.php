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

use pocketmine\block\Block;
use pocketmine\entity\Entity;

/**
 * Called when an entity takes damage from a block.
 */
class EntityDamageByBlockEvent extends EntityDamageEvent{
	/** @var Block */
	private $damager;

	/**
	 * @param Block   $damager
	 * @param Entity  $entity
	 * @param int     $cause
	 * @param float   $damage
	 * @param float[] $modifiers
	 */
	public function __construct(Block $damager, Entity $entity, int $cause, float $damage, array $modifiers = []){
		$this->damager = $damager;
		parent::__construct($entity, $cause, $damage, $modifiers);
	}

	/**
	 * @return Block
	 */
	public function getDamager() : Block{
		return $this->damager;
	}

}