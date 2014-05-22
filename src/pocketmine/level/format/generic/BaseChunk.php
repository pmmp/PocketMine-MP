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

namespace pocketmine\level\format\generic;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkSection;
use pocketmine\level\Level;

abstract class BaseChunk implements Chunk{

	/** @var ChunkSection[] */
	protected $sections = array();
	protected $level;

	protected $x;
	protected $z;

	/**
	 * @param Level $level
	 * @param int $x
	 * @param int $z
	 * @param ChunkSection[] $sections
	 */
	public function __construct(Level $level, $x, $z, array $sections){
		$this->level = $level;
		$this->x = (int) $x;
		$this->z = (int) $z;
		foreach($sections as $Y => $section){
			if($section instanceof ChunkSection){
				$this->sections[$Y] = $section;
			}else{
				trigger_error("Received invalid ChunkSection instance", E_USER_ERROR);
				return;
			}

			if($section >= self::SECTION_COUNT){
				trigger_error("Invalid amount of chunks", E_USER_WARNING);
				return;
			}
		}
	}

	public function getX(){
		return $this->x;
	}

	public function getZ(){
		return $this->z;
	}

	public function getLevel(){
		return $this->level;
	}

	public function getBlock($x, $y, $z, &$blockId, &$meta = null){
		$this->sections[$y >> 4]->getBlock($x, $y - ($y >> 4), $z, $blockId, $meta);
	}

	public function setBlock($x, $y, $z, $blockId = null, $meta = null){
		$this->sections[$y >> 4]->setBlock($x, $y - ($y >> 4), $z, $blockId, $meta);
	}

	public function getBlockSkyLight($x, $y, $z){
		return $this->sections[$y >> 4]->getBlockSkyLight($x, $y - ($y >> 4), $z);
	}

	public function setBlockSkyLight($x, $y, $z, $data){
		$this->sections[$y >> 4]->getBlockSkyLight($x, $y - ($y >> 4), $z, $data);
	}

	public function getBlockLight($x, $y, $z){
		return $this->sections[$y >> 4]->getBlockSkyLight($x, $y - ($y >> 4), $z);
	}

	public function setBlockLight($x, $y, $z, $data){
		$this->sections[$y >> 4]->getBlockSkyLight($x, $y - ($y >> 4), $z, $data);
	}

	public function getHighestBlockAt($x, $z){
		for($Y = self::SECTION_COUNT; $Y >= 0; --$Y){
			if(!$this->isSectionEmpty($Y)){
				$column = $this->sections[$Y]->getBlockIdColumn($x, $z);
				for($y = 15; $y >= 0; --$y){
					if($column{$y} !== "\x00"){
						return $y + $Y << 4;
					}
				}
			}
		}

		return 0;
	}

	public function isSectionEmpty($fY){
		return $this->sections[(int) $fY] instanceof EmptyChunkSection;
	}

	public function getSection($fY){
		return $this->sections[(int) $fY];
	}

	public function setSection($fY, ChunkSection $section){
		$this->sections[(int) $fY] = $section;
	}
}