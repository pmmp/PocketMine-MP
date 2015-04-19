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

namespace pocketmine\level\format\anvil;

use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\tile\Spawnable;
use pocketmine\utils\Binary;
use pocketmine\utils\ChunkException;

class ChunkRequestTask extends AsyncTask{

	protected $levelId;
	protected $chunkX;
	protected $chunkZ;

	/** @var \pocketmine\level\format\ChunkSection[] */
	protected $sections;
	/** @var string[256] */
	protected $biomeIds;
	/** @var int[] */
	protected $biomeColors;

	protected $tiles;

	public function __construct(Anvil $level, $levelId, $chunkX, $chunkZ){
		$this->levelId = $levelId;
		$this->chunkX = $chunkX;
		$this->chunkZ = $chunkZ;
		$chunk = $level->getChunk($chunkX, $chunkZ, false);
		if(!($chunk instanceof Chunk)){
			throw new ChunkException("Invalid Chunk sent");
		}
		$this->biomeIds = $chunk->getBiomeIdArray();
		$this->biomeColors = $chunk->getBiomeColorArray();

		$this->sections = $chunk->getSections();

		$tiles = "";
		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		foreach($chunk->getTiles() as $tile){
			if($tile instanceof Spawnable){
				$nbt->setData($tile->getSpawnCompound());
				$tiles .= $nbt->write();
			}
		}

		$this->tiles = $tiles;

	}

	public function onRun(){
		$orderedIds = "";
		$orderedData = "";
		$orderedSkyLight = "";
		$orderedLight = "";

		$ids = "";
		$meta = "";
		$blockLight = "";
		$skyLight = "";

		foreach($this->sections as $section){
			$ids .= $section->getIdArray();
			$meta .= $section->getDataArray();
			$blockLight .= $section->getLightArray();
			$skyLight .= $section->getSkyLightArray();
		}

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$orderedIds .= $this->getColumn($ids, $x, $z);
				$orderedData .= $this->getHalfColumn($meta, $x, $z);
				$orderedSkyLight .= $this->getHalfColumn($skyLight, $x, $z);
				$orderedLight .= $this->getHalfColumn($blockLight, $x, $z);
			}
		}

		$biomeColors = pack("N*", ...$this->biomeColors);

		$ordered = $orderedIds . $orderedData . $orderedSkyLight . $orderedLight . $this->biomeIds . $biomeColors . $this->tiles;

		$this->setResult($ordered, false);
	}

	public function getColumn(&$data, $x, $z){
		$i = ($z << 4) + $x;
		$column = "";
		for($y = 0; $y < 128; ++$y){
			$column .= $data{($y << 8) + $i};
		}

		return $column;
	}

	public function getHalfColumn(&$data, $x, $z){
		$i = ($z << 3) + ($x >> 1);
		$column = "";
		if(($x & 1) === 0){
			for($y = 0; $y < 128; $y += 2){
				$column .= ($data{($y << 7) + $i} & "\x0f") | chr((ord($data{(($y + 1) << 7) + $i}) & 0x0f) << 4);
			}
		}else{
			for($y = 0; $y < 128; $y += 2){
				$column .= chr((ord($data{($y << 7) + $i}) & 0xf0) >> 4) | ($data{(($y + 1) << 7) + $i} & "\xf0");
			}
		}

		return $column;
	}

	public function onCompletion(Server $server){
		$level = $server->getLevel($this->levelId);
		if($level instanceof Level and $this->hasResult()){
			$result = $this->getResult();
			$level->chunkRequestCallback($this->chunkX, $this->chunkZ, $result);
		}
	}

}