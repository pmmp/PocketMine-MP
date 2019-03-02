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
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\Living;

/**
 * Interface implemented by objects that can be consumed by mobs.
 */
interface Consumable{

	/**
	 * Returns the leftover that this Consumable produces when it is consumed. For Items, this is usually air, but could
	 * be an Item to add to a Player's inventory afterwards (such as a bowl).
	 *
	 * @return Item|Block|mixed
	 */
	public function getResidue();

	/**
	 * @return EffectInstance[]
	 */
	public function getAdditionalEffects() : array;

	/**
	 * Called when this Consumable is consumed by mob, after standard resulting effects have been applied.
	 *
	 * @param Living $consumer
	 */
	public function onConsume(Living $consumer) : void;
}
