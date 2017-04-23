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


namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;

interface Consumable{

	/**
	 * Returns the result of eating the material.
	 * This may return an Item for eating an item, or a Block for eating things like cake.
	 *
	 * @return Item|Block|mixed
	 */
	public function getResidue();

	/**
	 * Returns effects to be applied to the eater.
	 *
	 * @return Effect[]
	 */
	public function getAdditionalEffects() : array;

	/**
	 * Returns whether the target entity can consume this material.
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function canBeConsumedBy(Entity $entity) : bool;

	/**
	 * Performs any additional actions necessary when this consumable is consumed.
	 * @param Entity $consumer
	 */
	public function onConsume(Entity $consumer);
}