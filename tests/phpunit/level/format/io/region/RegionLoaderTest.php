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

namespace pocketmine\level\format\io\region;

use PHPUnit\Framework\TestCase;
use pocketmine\level\format\ChunkException;

class RegionLoaderTest extends TestCase{

	public function testChunkTooBig() : void{
		$r = new RegionLoader(sys_get_temp_dir() . '/chunk_too_big.testregion_' . bin2hex(random_bytes(4)));
		$r->open();

		$this->expectException(ChunkException::class);
		$r->writeChunk(0, 0, str_repeat("a", 1044476));
	}

	public function testChunkMaxSize() : void{
		$data = str_repeat("a", 1044475);
		$path = sys_get_temp_dir() . '/chunk_just_fits.testregion_' . bin2hex(random_bytes(4));
		$r = new RegionLoader($path);
		$r->open();

		$r->writeChunk(0, 0, $data);
		$r->close();

		$r = new RegionLoader($path);
		$r->open();
		self::assertSame($data, $r->readChunk(0, 0));
	}
}
