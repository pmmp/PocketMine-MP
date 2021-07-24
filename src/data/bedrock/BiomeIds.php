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
	public const EXTREME_HILLS = 3;
	public const FOREST = 4;
	public const TAIGA = 5;
	public const SWAMPLAND = 6;
	public const RIVER = 7;
	public const HELL = 8;
	public const THE_END = 9;
	public const LEGACY_FROZEN_OCEAN = 10;
	public const FROZEN_RIVER = 11;
	public const ICE_PLAINS = 12;
	public const ICE_MOUNTAINS = 13;
	public const MUSHROOM_ISLAND = 14;
	public const MUSHROOM_ISLAND_SHORE = 15;
	public const BEACH = 16;
	public const DESERT_HILLS = 17;
	public const FOREST_HILLS = 18;
	public const TAIGA_HILLS = 19;
	public const EXTREME_HILLS_EDGE = 20;
	public const JUNGLE = 21;
	public const JUNGLE_HILLS = 22;
	public const JUNGLE_EDGE = 23;
	public const DEEP_OCEAN = 24;
	public const STONE_BEACH = 25;
	public const COLD_BEACH = 26;
	public const BIRCH_FOREST = 27;
	public const BIRCH_FOREST_HILLS = 28;
	public const ROOFED_FOREST = 29;
	public const COLD_TAIGA = 30;
	public const COLD_TAIGA_HILLS = 31;
	public const MEGA_TAIGA = 32;
	public const MEGA_TAIGA_HILLS = 33;
	public const EXTREME_HILLS_PLUS_TREES = 34;
	public const SAVANNA = 35;
	public const SAVANNA_PLATEAU = 36;
	public const MESA = 37;
	public const MESA_PLATEAU_STONE = 38;
	public const MESA_PLATEAU = 39;
	public const WARM_OCEAN = 40;
	public const DEEP_WARM_OCEAN = 41;
	public const LUKEWARM_OCEAN = 42;
	public const DEEP_LUKEWARM_OCEAN = 43;
	public const COLD_OCEAN = 44;
	public const DEEP_COLD_OCEAN = 45;
	public const FROZEN_OCEAN = 46;
	public const DEEP_FROZEN_OCEAN = 47;
	public const BAMBOO_JUNGLE = 48;
	public const BAMBOO_JUNGLE_HILLS = 49;

	public const SUNFLOWER_PLAINS = 129;
	public const DESERT_MUTATED = 130;
	public const EXTREME_HILLS_MUTATED = 131;
	public const FLOWER_FOREST = 132;
	public const TAIGA_MUTATED = 133;
	public const SWAMPLAND_MUTATED = 134;

	public const ICE_PLAINS_SPIKES = 140;

	public const JUNGLE_MUTATED = 149;

	public const JUNGLE_EDGE_MUTATED = 151;

	public const BIRCH_FOREST_MUTATED = 155;
	public const BIRCH_FOREST_HILLS_MUTATED = 156;
	public const ROOFED_FOREST_MUTATED = 157;
	public const COLD_TAIGA_MUTATED = 158;

	public const REDWOOD_TAIGA_MUTATED = 160;
	public const REDWOOD_TAIGA_HILLS_MUTATED = 161;
	public const EXTREME_HILLS_PLUS_TREES_MUTATED = 162;
	public const SAVANNA_MUTATED = 163;
	public const SAVANNA_PLATEAU_MUTATED = 164;
	public const MESA_BRYCE = 165;
	public const MESA_PLATEAU_STONE_MUTATED = 166;
	public const MESA_PLATEAU_MUTATED = 167;

	public const SOULSAND_VALLEY = 178;
	public const CRIMSON_FOREST = 179;
	public const WARPED_FOREST = 180;
	public const BASALT_DELTAS = 181;
}
