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

namespace pocketmine\level\format\mcregion;

use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\tile\Spawnable;
use pocketmine\utils\Binary;

class ChunkRequestTask extends AsyncTask{

	protected $levelId;
	protected $chunkX;
	protected $chunkZ;
	protected $compressionLevel;

	protected $blocks;
	protected $data;
	protected $skyLight;
	protected $blockLight;

	/** @var string[256] */
	protected $biomeIds;
	/** @var int[] */
	protected $biomeColors;

	protected $tiles;

	public function __construct(McRegion $level, $levelId, $chunkX, $chunkZ){
		$this->levelId = $levelId;
		$this->chunkX = $chunkX;
		$this->chunkZ = $chunkZ;
		$chunk = $level->getChunk($chunkX, $chunkZ, true);
		$this->blocks = $chunk->getBlockIdArray();
		$this->data = $chunk->getBlockDataArray();
		$this->skyLight = $chunk->getBlockSkyLightArray();
		$this->blockLight = $chunk->getBlockLightArray();
		$this->biomeIds = $chunk->getBiomeIdArray();
		$this->biomeColors = $chunk->getBiomeColorArray();

		$tiles = "";
		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		foreach($chunk->getTiles() as $tile){
			if($tile instanceof Spawnable){
				$nbt->setData($tile->getSpawnCompound());
				$tiles .= $nbt->write();
			}
		}

		$this->tiles = $tiles;

		$this->compressionLevel = Level::$COMPRESSION_LEVEL;

	}

	public function onRun(){
		$biomeColors = "";

		foreach($this->biomeColors as $color){
			$biomeColors .= Binary::writeInt($color);
		}

		$ordered = zlib_encode(Binary::writeLInt($this->chunkX) . Binary::writeLInt($this->chunkZ) . $this->blocks . $this->data . $this->skyLight . $this->blockLight . $this->biomeIds . $biomeColors . $this->tiles, ZLIB_ENCODING_DEFLATE, $this->compressionLevel);

		$this->setResult($ordered);
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
			$level->chunkRequestCallback($this->chunkX, $this->chunkZ, $this->getResult());
		}
	}

}