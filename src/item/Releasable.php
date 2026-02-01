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

use pocketmine\player\Player;

/**
 * Implemented by items which can be used by pressing and holding the "use item" button in-game.
 * The player's arm will appear to be raised and the "using item" flag will be set.
 * Examples of this type of behaviour include bows, food and spyglasses.
 *
 * @see Player::isUsingItem()
 * @see Player::getItemUseDuration()
 */
interface Releasable{

	/**
	 * Returns whether the player can currently trigger the press-and-hold behaviour of the item.
	 * For example, bows return whether the player has an arrow that can be fired.
	 */
	public function canStartUsingItem(Player $player) : bool;

}
