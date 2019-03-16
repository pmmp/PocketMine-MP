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

interface PlatformTypes{

	public const ANDROID = 1;
	public const IOS = 2;
	public const OSX = 3;
	public const FIRE = 4;
	public const GEARVR = 5;
	public const HOLOLENS = 6;
	public const WIN10 = 7;
	public const WIN32 = 8;
	public const DEDICATED = 9;
	public const TVOS = 10;
	public const ORBIS = 11;
	public const NX = 12;
	public const UNKNOWN = -1;

}
