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

namespace pocketmine\world\format\io\region;

use PHPUnit\Framework\TestCase;
use pocketmine\world\format\ChunkException;
use function bin2hex;
use function clearstatcache;
use function file_exists;
use function random_bytes;
use function str_repeat;
use function sys_get_temp_dir;
use function unlink;
use const DIRECTORY_SEPARATOR;

class RegionLoaderTest extends TestCase{

	/** @var string */
	private $regionPath;
	/** @var RegionLoader */
	private $region;

	public function setUp() : void{
		$this->regionPath = sys_get_temp_dir() . '/test.testregion';
		if(file_exists($this->regionPath)){
			unlink($this->regionPath);
		}
		$this->region = RegionLoader::createNew($this->regionPath);
	}

	public function tearDown() : void{
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

		$r = RegionLoader::loadExisting($this->regionPath);
		self::assertSame($data, $r->readChunk(0, 0));
	}

	/**
	 * @return \Generator|int[][]
	 * @phpstan-return \Generator<int, array{int,int}, void, void>
	 */
	public function outOfBoundsCoordsProvider() : \Generator{
		yield [-1, -1];
		yield [32, 32];
		yield [-1, 32];
		yield [32, -1];
	}

	/**
	 * @dataProvider outOfBoundsCoordsProvider
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
	 *
	 * @throws \InvalidArgumentException
	 * @throws \pocketmine\world\format\io\exception\CorruptedChunkException
	 */
	public function testReadChunkOutOfBounds(int $x, int $z) : void{
		$this->expectException(\InvalidArgumentException::class);
		$this->region->readChunk($x, $z);
	}

	/**
	 * Test that cached filesize() values don't break validation of region headers
	 */
	public function testRegionHeaderCachedFilesizeRegression() : void{
		$this->region->close();
		$region = RegionLoader::loadExisting($this->regionPath); //now we have a region, so the header will be verified, triggering two filesize() calls
		$data = str_repeat("hello", 2000);
		$region->writeChunk(0, 0, $data); //add some data to the end of the file, to make the cached filesize invalid
		$region->close();
		$region = RegionLoader::loadExisting($this->regionPath);
		self::assertSame($data, $region->readChunk(0, 0));
	}

	public function testCreateNewWithExistingRegion() : void{
		$this->region->close();
		$this->expectException(\RuntimeException::class);
		RegionLoader::createNew($this->regionPath);
	}

	public function testLoadExistingWithMissingFile() : void{
		clearstatcache();

		do{
			$randfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6)) . ".mca";
		}while(file_exists($randfile));
		$this->expectException(\RuntimeException::class);
		RegionLoader::loadExisting($randfile);
	}
}
