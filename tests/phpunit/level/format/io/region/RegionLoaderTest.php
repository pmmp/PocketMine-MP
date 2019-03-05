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
use function file_exists;
use function random_bytes;
use function str_repeat;
use function sys_get_temp_dir;
use function unlink;

class RegionLoaderTest extends TestCase{

	/** @var string */
	private $regionPath;
	/** @var RegionLoader */
	private $region;

	public function setUp(){
		$this->regionPath = sys_get_temp_dir() . '/test.testregion';
		if(file_exists($this->regionPath)){
			unlink($this->regionPath);
		}
		$this->region = new RegionLoader($this->regionPath);
		$this->region->open();
	}

	public function tearDown(){
		$this->region->close();
		if(file_exists($this->regionPath)){
			unlink($this->regionPath);
		}
	}

	public function testChunkTooBig() : void{
		$this->expectException(ChunkException::class);
		$this->region->writeChunk(0, 0, str_repeat("a", 1044476));
	}

	public function testChunkMaxSize() : void{
		$data = str_repeat("a", 1044475);
		$this->region->writeChunk(0, 0, $data);
		$this->region->close();

		$r = new RegionLoader($this->regionPath);
		$r->open();
		self::assertSame($data, $r->readChunk(0, 0));
	}

	public function outOfBoundsCoordsProvider() : \Generator{
		yield [-1, -1];
		yield [32, 32];
		yield [-1, 32];
		yield [32, -1];
	}

	/**
	 * @dataProvider outOfBoundsCoordsProvider
	 * @param int $x
	 * @param int $z
	 *
	 * @throws ChunkException
	 * @throws \InvalidArgumentException
	 */
	public function testWriteChunkOutOfBounds(int $x, int $z) : void{
		$this->expectException(\InvalidArgumentException::class);
		$this->region->writeChunk($x, $z, str_repeat("\x00", 1000));
	}

	public function testReadWriteChunkInBounds() : void{
		$dat = random_bytes(1000);
		for($x = 0; $x < 32; ++$x){
			for($z = 0; $z < 32; ++$z){
				$this->region->writeChunk($x, $z, $dat);
			}
		}
		for($x = 0; $x < 32; ++$x){
			for($z = 0; $z < 32; ++$z){
				self::assertSame($dat, $this->region->readChunk($x, $z));
			}
		}
	}

	/**
	 * @dataProvider outOfBoundsCoordsProvider
	 * @param int $x
	 * @param int $z
	 *
	 * @throws \InvalidArgumentException
	 * @throws \pocketmine\level\format\io\exception\CorruptedChunkException
	 */
	public function testReadChunkOutOfBounds(int $x, int $z) : void{
		$this->expectException(\InvalidArgumentException::class);
		$this->region->readChunk($x, $z);
	}
}
