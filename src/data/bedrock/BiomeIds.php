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

namespace pocketmine\data\bedrock;

final class BiomeIds{

	private function __construct(){
		//NOOP
	}

	public const OCEAN = 0;
	public const PLAINS = 1;
	public const DESERT = 2;
	public const MOUNTAINS = 3;
	public const FOREST = 4;
	public const TAIGA = 5;
	public const SWAMP = 6;
	public const RIVER = 7;

	public const HELL = 8;

	public const ICE_PLAINS = 12;

	public const SMALL_MOUNTAINS = 20;

	public const BIRCH_FOREST = 27;
}
