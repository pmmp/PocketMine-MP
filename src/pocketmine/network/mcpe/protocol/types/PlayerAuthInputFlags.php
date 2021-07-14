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

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;

/**
 * These flags are used in PlayerAuthInputPacket's inputFlags field.
 * The flags should be written as
 * `flags |= (1 << flag)`
 * and read as
 * `(flags & (1 << flag)) !== 0`
 *
 * @see PlayerAuthInputPacket
 */
final class PlayerAuthInputFlags{
	public const ASCEND = 0;
	public const DESCEND = 1;
	public const NORTH_JUMP = 2;
	public const JUMP_DOWN = 3;
	public const SPRINT_DOWN = 4;
	public const CHANGE_HEIGHT = 5;
	public const JUMPING = 6;
	public const AUTO_JUMPING_IN_WATER = 7;
	public const SNEAKING = 8;
	public const SNEAK_DOWN = 9;
	public const UP = 10;
	public const DOWN = 11;
	public const LEFT = 12;
	public const RIGHT = 13;
	public const UP_LEFT = 14;
	public const UP_RIGHT = 15;
	public const WANT_UP = 16;
	public const WANT_DOWN = 17;
	public const WANT_DOWN_SLOW = 18;
	public const WANT_UP_SLOW = 19;
	public const SPRINTING = 20;
	public const ASCEND_BLOCK = 21;
	public const DESCEND_BLOCK = 22;
	public const SNEAK_TOGGLE_DOWN = 23;
	public const PERSIST_SNEAK = 24;
	public const START_SPRINTING = 25;
	public const STOP_SPRINTING = 26;
	public const START_SNEAKING = 27;
	public const STOP_SNEAKING = 28;
	public const START_SWIMMING = 29;
	public const STOP_SWIMMING = 30;
	public const START_JUMPING = 31;
	public const START_GLIDING = 32;
	public const STOP_GLIDING = 33;
	public const PERFORM_ITEM_INTERACTION = 34;
	public const PERFORM_BLOCK_ACTIONS = 35;
	public const PERFORM_ITEM_STACK_REQUEST = 36;
}
