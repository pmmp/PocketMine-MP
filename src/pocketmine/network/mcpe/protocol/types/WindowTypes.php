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


namespace pocketmine\network\mcpe\protocol\types;


interface WindowTypes{

	const INVENTORY = -1;
	const CONTAINER = 0;
	const WORKBENCH = 1;
	const FURNACE = 2;
	const ENCHANTMENT = 3;
	const BREWING_STAND = 4;
	const ANVIL = 5;
	const DISPENSER = 6;
	const DROPPER = 7;
	const HOPPER = 8;
	const CAULDRON = 9;
	const MINECART_CHEST = 10;
	const MINECART_HOPPER = 11;
	const HORSE = 12;
	const BEACON = 13;
	const STRUCTURE_EDITOR = 14;
	const TRADING = 15;
	const COMMAND_BLOCK = 16;
	const JUKEBOX = 17;

}