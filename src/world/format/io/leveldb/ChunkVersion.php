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

namespace pocketmine\world\format\io\leveldb;

final class ChunkVersion{
	private function __construct(){
		//NOOP
	}

	public const v0_9_0 = 0;
	public const v0_9_2 = 1;
	public const v0_9_5 = 2;
	public const v1_0_0 = 3;
	public const v1_1_0 = 4;
	public const v1_1_0_converted_from_console = 5;
	public const v1_2_0_2_beta = 6;
	public const v1_2_0 = 7;
	public const v1_2_13 = 8;
	public const v1_8_0 = 9;
	public const v1_9_0 = 10;
	public const v1_11_0_1_beta = 11;
	public const v1_11_0_3_beta = 12;
	public const v1_11_0_4_beta = 13;
	public const v1_11_1 = 14;
	public const v1_12_0_4_beta = 15;
	public const v1_12_0_unused1 = 16; //possibly some beta version?
	public const v1_12_0_unused2 = 17; //possibly some beta version?
	public const v1_16_0_51_beta = 18;
	public const v1_16_0 = 19;
	public const v1_16_100_52_beta = 20;
	public const v1_16_100_57_beta = 21;
	public const v1_16_210 = 22;

	//Since this version they seem to skip every other version. Possibly the skipped ones are internal use.
	public const v1_16_220_50_beta_experimental_caves_cliffs = 23;
	public const v1_16_220_50_unused = 24;
	public const v1_16_230_50_beta_experimental_caves_cliffs = 25;
	public const v1_16_230_50_unused = 26;
	public const v1_17_30_23_beta_experimental_caves_cliffs = 27;
	public const v1_17_30_23_unused = 28;
	public const v1_17_30_25_beta_experimental_caves_cliffs = 29;
	public const v1_17_30_25_unused = 30;
	public const v1_17_40_20_beta_experimental_caves_cliffs = 31;
	public const v1_17_40_unused = 32;
	public const v1_18_0_20_beta = 33;
	public const v1_18_0_20_unused = 34;
	public const v1_18_0_22_beta = 35;
	public const v1_18_0_22_unused = 36;
	public const v1_18_0_24_beta = 37;
	public const v1_18_0_24_unused = 38;
	public const v1_18_0_25_beta = 39;
}
