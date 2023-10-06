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

namespace pocketmine\block;

/**
 * This interface is used to mark blocks that can be interacted with by hoppers.
 * This is used to prevent hoppers from trying to interact with blocks that don't support it.
 * If you want to make a block interactable by hoppers, implement this interface.
 */
interface HopperInteractable{
	/**
	 * Returns true/false if a hopper was successfully able to
	 * push an item into this block (e.g. an item was successfully added to the block inventory)
	 */
	public function doHopperPush(Hopper $hopperBlock) : bool;

	/**
	 * Returns true/false if a hopper was successfully able to
	 * pull an item from this block (e.g. an item was successfully removed from the block inventory)
	 */
	public function doHopperPull(Hopper $hopperBlock) : bool;
}
