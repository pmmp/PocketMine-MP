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

use pocketmine\level\format\FullChunk;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\tile\Spawnable;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\ChunkException;

class Anvil extends McRegion{

	/** @var RegionLoader[] */
	protected $regions = [];

	/** @var Chunk[] */
	protected $chunks = [];

	public static function getProviderName(){
		return "anvil";
	}

	public static function getProviderOrder(){
		return self::ORDER_YZX;
	}

	public static function usesChunkSection(){
		return true;
	}

	public static function isValid($path){
		$isValid = (file_exists($path . "/level.dat") and is_dir($path . "/region/"));

		if($isValid){
			$files = glob($path . "/region/*.mc*");
			foreach($files as $f){
				if(strpos($f, ".mcr") !== false){ //McRegion
					$isValid = false;
					break;
				}
			}
		}

		return $isValid;
	}

	public function requestChunkTask($x, $z){
		$chunk = $this->getChunk($x, $z, false);
		if(!($chunk instanceof Chunk)){
			throw new ChunkException("Invalid Chunk sent");
		}

		$tiles = "";

		if(count($chunk->getTiles()) > 0){
			$nbt = new NBT(NBT::LITTLE_ENDIAN);
			$list = [];
			foreach($chunk->getTiles() as $tile){
				if($tile instanceof Spawnable){
					$list[] = $tile->getSpawnCompound();
				}
			}
			$nbt->setData($list);
			$tiles = $nbt->write(true);
		}

		$extraData = new BinaryStream();
		$extraData->putLInt(count($chunk->getBlockExtraDataArray()));
		foreach($chunk->getBlockExtraDataArray() as $key => $value){
			$extraData->putLInt($key);
			$extraData->putLShort($value);
		}

		$csections = $chunk->getSections();
		$orderedId = str_repeat("\x00", 4096);
		$orderedMeta = str_repeat("\x00", 2048);

		$sections = [];
		$sectionsCnt = 0;
		$wasEmpty = true;

		for($s = 7; $s >= 0; $s--){
			$id = $csections[$s]->getIdArray();
			$meta = $csections[$s]->getDataArray();
			if($wasEmpty === true)
				$empty = true;
			else
				$empty = false;

			for($xx = 0; $xx < 16; $xx++){
				for($yy = 0; $yy < 16; $yy++){
					for($zz = 0; $zz < 16; $zz++){
						$orderedId{($xx << 8) | ($zz << 4) | $yy} = $cid = $id{($yy << 8) | ($zz << 4) | $xx};
						$m = 0;
						if($cid !== "\x00"){
							if($empty === true)
								$empty = false;

							$m = ord($meta{($yy << 7) | ($zz << 3) | ($xx >> 1)});
							if(($xx & 1) === 0)
								$m &= 0x0f;
							else
								$m >>= 4;
						}
						$i = ($xx << 7) | ($zz << 3) | ($yy >> 1);
						if(($yy & 1) === 0)
							$orderedMeta{$i} = chr((ord($orderedMeta{$i}) & 0xf0) | $m);
						else
							$orderedMeta{$i} = chr($m << 4 | (ord($orderedMeta{$i}) & 0x0f));
					}
				}
			}

			if($empty === false){
				$wasEmpty = false;
				$sectionsCnt++;
				$sections[] =
					chr(0) .// ??
					$orderedId .
					$orderedMeta .
					str_repeat("\xff", 2048) .// SkyLight
					str_repeat("\xff", 2048);// Blocklight
			}

		}

		$ordered =
			chr($sectionsCnt).
			implode('', array_reverse($sections)).
			pack("C*", ...array_fill(0, 512, 127)).// ??
			pack('N*', ...array_fill(0, 256, 0)).// ??
			$extraData->getBuffer().
			$tiles;

		$this->getLevel()->chunkRequestCallback($x, $z, $ordered);

		return null;
	}

	/**
	 * @param $x
	 * @param $z
	 *
	 * @return RegionLoader
	 */
	protected function getRegion($x, $z){
		return $this->regions[Level::chunkHash($x, $z)] ?? null;
	}

	/**
	 * @param int  $chunkX
	 * @param int  $chunkZ
	 * @param bool $create
	 *
	 * @return Chunk
	 */
	public function getChunk($chunkX, $chunkZ, $create = false){
		return parent::getChunk($chunkX, $chunkZ, $create);
	}

	public function setChunk($chunkX, $chunkZ, FullChunk $chunk){
		if(!($chunk instanceof Chunk)){
			throw new ChunkException("Invalid Chunk class");
		}

		$chunk->setProvider($this);

		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ);

		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->chunks[Level::chunkHash($chunkX, $chunkZ)] = $chunk;
	}

	public function getEmptyChunk($chunkX, $chunkZ){
		return Chunk::getEmptyChunk($chunkX, $chunkZ, $this);
	}

	public static function createChunkSection($Y){
		return new ChunkSection(new CompoundTag("", [
			"Y" => new ByteTag("Y", $Y),
			"Blocks" => new ByteArrayTag("Blocks", str_repeat("\x00", 4096)),
			"Data" => new ByteArrayTag("Data", str_repeat("\x00", 2048)),
			"SkyLight" => new ByteArrayTag("SkyLight", str_repeat("\xff", 2048)),
			"BlockLight" => new ByteArrayTag("BlockLight", str_repeat("\x00", 2048))
		]));
	}

	public function isChunkGenerated($chunkX, $chunkZ){
		if(($region = $this->getRegion($chunkX >> 5, $chunkZ >> 5)) !== null){
			return $region->chunkExists($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32) and $this->getChunk($chunkX - $region->getX() * 32, $chunkZ - $region->getZ() * 32, true)->isGenerated();
		}

		return false;
	}

	protected function loadRegion($x, $z){
		if(isset($this->regions[$index = Level::chunkHash($x, $z)])){
			return true;
		}

		$this->regions[$index] = new RegionLoader($this, $x, $z);

		return true;
	}
}