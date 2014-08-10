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

namespace pocketmine\level\conversor\pmf;

use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\utils\Binary;
use pocketmine\utils\MainLogger;

class LevelFormat extends PMF{
	const VERSION = 2;
	const ZLIB_LEVEL = 6;
	const ZLIB_ENCODING = 15; //15 = zlib, -15 = raw deflate, 31 = gzip

	public $level;
	public $levelData = [];
	public $isLoaded = true;
	private $chunks = [];
	private $chunkChange = [];
	private $chunkInfo = [];
	public $isGenerating = 0;

	public function getData($index){
		if(!isset($this->levelData[$index])){
			return false;
		}

		return ($this->levelData[$index]);
	}

	public function setData($index, $data){
		if(!isset($this->levelData[$index])){
			return false;
		}
		$this->levelData[$index] = $data;

		return true;
	}

	public function closeLevel(){
		$this->chunks = null;
		unset($this->chunks, $this->chunkChange, $this->chunkInfo, $this->level);
		$this->close();
	}

	/**
	 * @param   string   $file
	 * @param bool|array $blank default false
	 */
	public function __construct($file, $blank = false){
		$this->chunks = [];
		$this->chunkChange = [];
		$this->chunkInfo = [];
		if(is_array($blank)){
			$this->create($file, 0);
			$this->levelData = $blank;
			$this->createBlank();
			$this->isLoaded = true;
		}else{
			if($this->load($file) !== false){
				$this->parseInfo();
				if($this->parseLevel() === false){
					$this->isLoaded = false;
				}else{
					$this->isLoaded = true;
				}
			}else{
				$this->isLoaded = false;
			}
		}
	}

	public function saveData(){
		$this->levelData["version"] = self::VERSION;
		@ftruncate($this->fp, 5);
		$this->seek(5);
		$this->write(chr($this->levelData["version"]));
		$this->write(Binary::writeShort(strlen($this->levelData["name"])) . $this->levelData["name"]);
		$this->write(Binary::writeInt($this->levelData["seed"]));
		$this->write(Binary::writeInt($this->levelData["time"]));
		$this->write(Binary::writeFloat($this->levelData["spawnX"]));
		$this->write(Binary::writeFloat($this->levelData["spawnY"]));
		$this->write(Binary::writeFloat($this->levelData["spawnZ"]));
		$this->write(chr($this->levelData["height"]));
		$this->write(Binary::writeShort(strlen($this->levelData["generator"])) . $this->levelData["generator"]);
		$settings = serialize($this->levelData["generatorSettings"]);
		$this->write(Binary::writeShort(strlen($settings)) . $settings);
		$extra = zlib_encode($this->levelData["extra"], self::ZLIB_ENCODING, self::ZLIB_LEVEL);
		$this->write(Binary::writeShort(strlen($extra)) . $extra);
	}

	private function createBlank(){
		$this->saveData();
		@mkdir(dirname($this->file) . "/chunks/", 0755);
	}

	protected function parseLevel(){
		if($this->getType() !== 0x00){
			return false;
		}
		$this->seek(5);
		$this->levelData["version"] = ord($this->read(1));
		if($this->levelData["version"] > self::VERSION){
			MainLogger::getLogger()->error("New unsupported PMF Level format version #" . $this->levelData["version"] . ", current version is #" . self::VERSION);

			return false;
		}
		$this->levelData["name"] = $this->read(Binary::readShort($this->read(2), false));
		$this->levelData["seed"] = Binary::readInt($this->read(4));
		$this->levelData["time"] = Binary::readInt($this->read(4));
		$this->levelData["spawnX"] = Binary::readFloat($this->read(4));
		$this->levelData["spawnY"] = Binary::readFloat($this->read(4));
		$this->levelData["spawnZ"] = Binary::readFloat($this->read(4));
		if($this->levelData["version"] === 0){
			$this->read(1);
			$this->levelData["height"] = ord($this->read(1));
		}else{
			$this->levelData["height"] = ord($this->read(1));
			if($this->levelData["height"] !== 8){
				return false;
			}
			$this->levelData["generator"] = $this->read(Binary::readShort($this->read(2), false));
			$this->levelData["generatorSettings"] = unserialize($this->read(Binary::readShort($this->read(2), false)));

		}
		$this->levelData["extra"] = @zlib_decode($this->read(Binary::readShort($this->read(2), false)));

		$upgrade = false;
		if($this->levelData["version"] === 0){
			$this->upgrade_From0_To1();
			$upgrade = true;
		}
		if($this->levelData["version"] === 1){
			$this->upgrade_From1_To2();
			$upgrade = true;
		}

		if($upgrade === true){
			$this->saveData();
		}
	}

	private function upgrade_From0_To1(){
		MainLogger::getLogger()->notice("Old PMF Level format version #0 detected, upgrading to version #1");
		for($index = 0; $index < 256; ++$index){
			$X = $index & 0x0F;
			$Z = $index >> 4;

			$bitflags = Binary::readShort($this->read(2));
			$oldPath = dirname($this->file) . "/chunks/" . $Z . "." . $X . ".pmc";
			$chunkOld = gzopen($oldPath, "rb");
			$newPath = dirname($this->file) . "/chunks/" . (($X ^ $Z) & 0xff) . "/" . $Z . "." . $X . ".pmc";
			@mkdir(dirname($newPath));
			$chunkNew = gzopen($newPath, "wb1");
			gzwrite($chunkNew, chr($bitflags) . "\x00\x00\x00\x01");
			while(gzeof($chunkOld) === false){
				gzwrite($chunkNew, gzread($chunkOld, 65535));
			}
			gzclose($chunkNew);
			gzclose($chunkOld);
			@unlink($oldPath);
		}
		$this->levelData["version"] = 1;
		$this->levelData["generator"] = "default";
		$this->levelData["generatorSettings"] = "";
	}

	private function upgrade_From1_To2(){
		MainLogger::getLogger()->notice("Old PMF Level format version #1 detected, upgrading to version #2");
		$nbt = new Compound("", array(
			new Enum("Entities", []),
			new Enum("TileEntities", [])
		));
		$nbt->Entities->setTagType(NBT::TAG_Compound);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);
		$nbtCodec = new NBT(NBT::BIG_ENDIAN);
		$nbtCodec->setData($nbt);
		$namedtag = $nbtCodec->write();
		$namedtag = Binary::writeInt(strlen($namedtag)) . $namedtag;
		foreach(glob(dirname($this->file) . "/chunks/*/*.*.pmc") as $chunkFile){
			$oldChunk = zlib_decode(file_get_contents($chunkFile));
			$newChunk = substr($oldChunk, 0, 5);
			$newChunk .= $namedtag;
			$newChunk .= str_repeat("\x01", 256); //Biome indexes (all Plains)
			$newChunk .= substr($oldChunk, 5);
			file_put_contents($chunkFile, zlib_encode($newChunk, self::ZLIB_ENCODING, self::ZLIB_LEVEL));
		}
		$this->levelData["version"] = 2;
	}

	public static function getIndex($X, $Z){
		return ($Z << 16) | ($X < 0 ? (~--$X & 0x7fff) | 0x8000 : $X & 0x7fff);
	}

	public static function getXZ($index, &$X = null, &$Z = null){
		$Z = $index >> 16;
		$X = ($index & 0x8000) === 0x8000 ? -($index & 0x7fff) : $index & 0x7fff;

		return array($X, $Z);
	}

	private function getChunkPath($X, $Z){
		return dirname($this->file) . "/chunks/" . (((int) $X ^ (int) $Z) & 0xff) . "/" . $Z . "." . $X . ".pmc";
	}

	public function generateChunk($X, $Z){
		$path = $this->getChunkPath($X, $Z);
		if(!file_exists(dirname($path))){
			@mkdir(dirname($path), 0755);
		}
		$this->initCleanChunk($X, $Z);
		if($this->level instanceof Level){
			$ret = $this->level->generateChunk($X, $Z);
			$this->saveChunk($X, $Z);
			$this->populateChunk($X - 1, $Z);
			$this->populateChunk($X + 1, $Z);
			$this->populateChunk($X, $Z - 1);
			$this->populateChunk($X, $Z + 1);
			$this->populateChunk($X + 1, $Z + 1);
			$this->populateChunk($X + 1, $Z - 1);
			$this->populateChunk($X - 1, $Z - 1);
			$this->populateChunk($X - 1, $Z + 1);

			return $ret;
		}
	}

	public function populateChunk($X, $Z){
		if($this->level instanceof Level){
			if($this->isGenerating === 0 and
				$this->isChunkLoaded($X, $Z) and
				!$this->isPopulated($X, $Z) and
				$this->isGenerated($X - 1, $Z) and
				$this->isGenerated($X, $Z - 1) and
				$this->isGenerated($X + 1, $Z) and
				$this->isGenerated($X, $Z + 1) and
				$this->isGenerated($X + 1, $Z + 1) and
				$this->isGenerated($X - 1, $Z - 1) and
				$this->isGenerated($X + 1, $Z - 1) and
				$this->isGenerated($X - 1, $Z + 1)
			){
				$this->level->populateChunk($X, $Z);
				$this->saveChunk($X, $Z);
			}
		}
	}

	public function loadChunk($X, $Z){
		if($this->isChunkLoaded($X, $Z)){
			return true;
		}
		$index = self::getIndex($X, $Z);
		$path = $this->getChunkPath($X, $Z);
		if(!file_exists($path)){
			if($this->generateChunk($X, $Z) === false){
				return false;
			}
			if($this->isGenerating === 0){
				$this->populateChunk($X, $Z);
			}

			return true;
		}

		$chunk = file_get_contents($path);
		if($chunk === false){
			return false;
		}
		$chunk = zlib_decode($chunk);
		$offset = 0;

		$this->chunkInfo[$index] = array(
			0 => ord($chunk{0}),
			1 => Binary::readInt(substr($chunk, 1, 4)),
		);
		$offset += 5;
		$len = Binary::readInt(substr($chunk, $offset, 4));
		$offset += 4;
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$nbt->read(substr($chunk, $offset, $len));
		$this->chunkInfo[$index][2] = $nbt->getData();
		$offset += $len;
		$this->chunks[$index] = [];
		$this->chunkChange[$index] = array(-1 => false);
		$this->chunkInfo[$index][3] = substr($chunk, $offset, 256); //Biome data
		$offset += 256;
		for($Y = 0; $Y < 8; ++$Y){
			if(($this->chunkInfo[$index][0] & (1 << $Y)) !== 0){
				// 4096 + 2048 + 2048, Block Data, Meta, Light
				if(strlen($this->chunks[$index][$Y] = substr($chunk, $offset, 8192)) < 8192){
					MainLogger::getLogger()->notice("Empty corrupt chunk detected [$X,$Z,:$Y], recovering contents");
					$this->fillMiniChunk($X, $Z, $Y);
				}
				$offset += 8192;
			}else{
				$this->chunks[$index][$Y] = false;
			}
		}
		if($this->isGenerating === 0 and !$this->isPopulated($X, $Z)){
			$this->populateChunk($X, $Z);
		}

		return true;
	}

	public function unloadChunk($X, $Z, $save = true){
		$X = (int) $X;
		$Z = (int) $Z;
		if(!$this->isChunkLoaded($X, $Z)){
			return false;
		}elseif($save !== false){
			$this->saveChunk($X, $Z);
		}
		$index = self::getIndex($X, $Z);
		$this->chunks[$index] = null;
		$this->chunkChange[$index] = null;
		$this->chunkInfo[$index] = null;
		unset($this->chunks[$index], $this->chunkChange[$index], $this->chunkInfo[$index]);

		return true;
	}

	public function isChunkLoaded($X, $Z){
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index])){
			return false;
		}

		return true;
	}

	protected function cleanChunk($X, $Z){
		$index = self::getIndex($X, $Z);
		if(isset($this->chunks[$index])){
			for($Y = 0; $Y < 8; ++$Y){
				if($this->chunks[$index][$Y] !== false and substr_count($this->chunks[$index][$Y], "\x00") === 8192){
					$this->chunks[$index][$Y] = false;
					$this->chunkInfo[$index][0] &= ~(1 << $Y);
				}
			}
		}
	}

	public function isMiniChunkEmpty($X, $Z, $Y){
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) or $this->chunks[$index][$Y] === false){
			return true;
		}

		return false;
	}

	protected function fillMiniChunk($X, $Z, $Y){
		if($this->isChunkLoaded($X, $Z) === false){
			return false;
		}
		$index = self::getIndex($X, $Z);
		$this->chunks[$index][$Y] = str_repeat("\x00", 8192);
		$this->chunkChange[$index][-1] = true;
		$this->chunkChange[$index][$Y] = 8192;
		$this->chunkInfo[$index][0] |= 1 << $Y;

		return true;
	}

	public function getMiniChunk($X, $Z, $Y){
		if($this->isChunkLoaded($X, $Z) === false and $this->loadChunk($X, $Z) === false){
			return str_repeat("\x00", 8192);
		}
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index][$Y]) or $this->chunks[$index][$Y] === false){
			return str_repeat("\x00", 8192);
		}

		return $this->chunks[$index][$Y];
	}

	public function initCleanChunk($X, $Z){
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index])){
			$this->chunks[$index] = array(
				0 => false,
				1 => false,
				2 => false,
				3 => false,
				4 => false,
				5 => false,
				6 => false,
				7 => false,
			);
			$this->chunkChange[$index] = array(
				-1 => true,
				0 => 8192,
				1 => 8192,
				2 => 8192,
				3 => 8192,
				4 => 8192,
				5 => 8192,
				6 => 8192,
				7 => 8192,
			);
			$nbt = new Compound("", array(
				new Enum("Entities", []),
				new Enum("TileEntities", [])
			));
			$nbt->Entities->setTagType(NBT::TAG_Compound);
			$nbt->TileEntities->setTagType(NBT::TAG_Compound);
			$this->chunkInfo[$index] = array(
				0 => 0,
				1 => 0,
				2 => $nbt,
				3 => str_repeat("\x00", 256),
			);
		}
	}

	public function setMiniChunk($X, $Z, $Y, $data){
		if($this->isGenerating > 0){
			$this->initCleanChunk($X, $Z);
		}elseif($this->isChunkLoaded($X, $Z) === false){
			$this->loadChunk($X, $Z);
		}
		if(strlen($data) !== 8192){
			return false;
		}
		$index = self::getIndex($X, $Z);
		$this->chunks[$index][$Y] = (string) $data;
		$this->chunkChange[$index][-1] = true;
		$this->chunkChange[$index][$Y] = 8192;
		$this->chunkInfo[$index][0] |= 1 << $Y;

		return true;
	}

	public function getBlockID($x, $y, $z){
		if($y > 127 or $y < 0){
			return 0;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index])){
			return 0;
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$b = ord($this->chunks[$index][$Y]{(int) ($aY + ($aX << 5) + ($aZ << 9))});

		return $b;
	}


	public function getBiome($x, $z){
		$X = $x >> 4;
		$Z = $z >> 4;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index])){
			return 0;
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);

		return ord($this->chunkInfo[$index][3]{$aX + ($aZ << 4)});
	}

	public function setBiome($x, $z, $biome){
		$X = $x >> 4;
		$Z = $z >> 4;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index])){
			return false;
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$this->chunkInfo[$index][3]{$aX + ($aZ << 4)} = chr((int) $biome);

		return true;
	}

	public function setBlockID($x, $y, $z, $block){
		if($y > 127 or $y < 0){
			return false;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$block &= 0xFF;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index])){
			return false;
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$this->chunks[$index][$Y]{(int) ($aY + ($aX << 5) + ($aZ << 9))} = chr($block);
		if(!isset($this->chunkChange[$index][$Y])){
			$this->chunkChange[$index][$Y] = 1;
		}else{
			++$this->chunkChange[$index][$Y];
		}
		$this->chunkChange[$index][-1] = true;

		return true;
	}

	public function getBlockDamage($x, $y, $z){
		if($y > 127 or $y < 0){
			return 0;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index])){
			return 0;
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$m = ord($this->chunks[$index][$Y]{(int) (($aY >> 1) + 16 + ($aX << 5) + ($aZ << 9))});
		if(($y & 1) === 0){
			$m = $m & 0x0F;
		}else{
			$m = $m >> 4;
		}

		return $m;
	}

	public function setBlockDamage($x, $y, $z, $damage){
		if($y > 127 or $y < 0){
			return false;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$damage &= 0x0F;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index])){
			return false;
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$mindex = (int) (($aY >> 1) + 16 + ($aX << 5) + ($aZ << 9));
		$old_m = ord($this->chunks[$index][$Y]{$mindex});
		if(($y & 1) === 0){
			$m = ($old_m & 0xF0) | $damage;
		}else{
			$m = ($damage << 4) | ($old_m & 0x0F);
		}

		if($old_m != $m){
			$this->chunks[$index][$Y]{$mindex} = chr($m);
			if(!isset($this->chunkChange[$index][$Y])){
				$this->chunkChange[$index][$Y] = 1;
			}else{
				++$this->chunkChange[$index][$Y];
			}
			$this->chunkChange[$index][-1] = true;

			return true;
		}

		return false;
	}

	public function getBlock($x, $y, $z){
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		if($y < 0 or $y > 127){
			return array(0, 0);
		}
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) and $this->loadChunk($X, $Z) === false){
			return array(0, 0);
		}elseif($this->chunks[$index][$Y] === false){
			return array(0, 0);
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$b = ord($this->chunks[$index][$Y]{(int) ($aY + ($aX << 5) + ($aZ << 9))});
		$m = ord($this->chunks[$index][$Y]{(int) (($aY >> 1) + 16 + ($aX << 5) + ($aZ << 9))});
		if(($y & 1) === 0){
			$m = $m & 0x0F;
		}else{
			$m = $m >> 4;
		}

		return array($b, $m);
	}

	public function setBlock($x, $y, $z, $block, $meta = 0){
		if($y > 127 or $y < 0){
			return false;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$block &= 0xFF;
		$meta &= 0x0F;
		$index = self::getIndex($X, $Z);
		if(!isset($this->chunks[$index]) and $this->loadChunk($X, $Z) === false){
			return false;
		}elseif($this->chunks[$index][$Y] === false){
			$this->fillMiniChunk($X, $Z, $Y);
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$bindex = (int) ($aY + ($aX << 5) + ($aZ << 9));
		$mindex = (int) (($aY >> 1) + 16 + ($aX << 5) + ($aZ << 9));
		$old_b = ord($this->chunks[$index][$Y]{$bindex});
		$old_m = ord($this->chunks[$index][$Y]{$mindex});
		if(($y & 1) === 0){
			$m = ($old_m & 0xF0) | $meta;
		}else{
			$m = ($meta << 4) | ($old_m & 0x0F);
		}

		if($old_b !== $block or $old_m !== $m){
			$this->chunks[$index][$Y]{$bindex} = chr($block);
			$this->chunks[$index][$Y]{$mindex} = chr($m);
			if(!isset($this->chunkChange[$index][$Y])){
				$this->chunkChange[$index][$Y] = 1;
			}else{
				++$this->chunkChange[$index][$Y];
			}
			$this->chunkChange[$index][-1] = true;

			return true;
		}

		return false;
	}

	public function getChunkNBT($X, $Z){
		if(!$this->isChunkLoaded($X, $Z) and $this->loadChunk($X, $Z) === false){
			return false;
		}
		$index = self::getIndex($X, $Z);

		return $this->chunkInfo[$index][2];
	}

	public function setChunkNBT($X, $Z, Compound $nbt){
		if(!$this->isChunkLoaded($X, $Z) and $this->loadChunk($X, $Z) === false){
			return false;
		}
		$index = self::getIndex($X, $Z);
		$this->chunkChange[$index][-1] = true;
		$this->chunkInfo[$index][2] = $nbt;
	}

	public function saveChunk($X, $Z, $force = false){
		$X = (int) $X;
		$Z = (int) $Z;
		if(!$this->isChunkLoaded($X, $Z)){
			return false;
		}
		$index = self::getIndex($X, $Z);
		if($force !== true and (!isset($this->chunkChange[$index]) or $this->chunkChange[$index][-1] === false)){ //No changes in chunk
			return true;
		}

		$path = $this->getChunkPath($X, $Z);
		if(!file_exists(dirname($path))){
			@mkdir(dirname($path), 0755);
		}
		$bitmap = 0;
		$this->cleanChunk($X, $Z);
		for($Y = 0; $Y < 8; ++$Y){
			if($this->chunks[$index][$Y] !== false and ((isset($this->chunkChange[$index][$Y]) and $this->chunkChange[$index][$Y] === 0) or !$this->isMiniChunkEmpty($X, $Z, $Y))){
				$bitmap |= 1 << $Y;
			}else{
				$this->chunks[$index][$Y] = false;
			}
			$this->chunkChange[$index][$Y] = 0;
		}
		$this->chunkInfo[$index][0] = $bitmap;
		$this->chunkChange[$index][-1] = false;
		$chunk = "";
		$chunk .= chr($bitmap);
		$chunk .= Binary::writeInt($this->chunkInfo[$index][1]);
		$namedtag = new NBT(NBT::BIG_ENDIAN);
		$namedtag->setData($this->chunkInfo[$index][2]);
		$namedtag = $namedtag->write();
		$chunk .= Binary::writeInt(strlen($namedtag)) . $namedtag;
		$chunk .= $this->chunkInfo[$index][3]; //biomes
		for($Y = 0; $Y < 8; ++$Y){
			$t = 1 << $Y;
			if(($bitmap & $t) === $t){
				$chunk .= $this->chunks[$index][$Y];
			}
		}
		file_put_contents($path, zlib_encode($chunk, self::ZLIB_ENCODING, self::ZLIB_LEVEL));

		return true;
	}

	public function setPopulated($X, $Z){
		if(!$this->isChunkLoaded($X, $Z)){
			return false;
		}
		$index = self::getIndex($X, $Z);
		$this->chunkInfo[$index][1] |= 0b00000000000000000000000000000001;
	}

	public function unsetPopulated($X, $Z){
		if(!$this->isChunkLoaded($X, $Z)){
			return false;
		}
		$index = self::getIndex($X, $Z);
		$this->chunkInfo[$index][1] &= ~0b00000000000000000000000000000001;
	}

	public function isPopulated($X, $Z){
		if(!$this->isChunkLoaded($X, $Z)){
			return false;
		}
		$index = self::getIndex($X, $Z);

		return ($this->chunkInfo[$index][1] & 0b00000000000000000000000000000001) > 0;
	}

	public function isGenerated($X, $Z){
		return file_exists($this->getChunkPath($X, $Z));
	}

	public function doSaveRound($force = false){
		foreach($this->chunks as $index => $chunk){
			self::getXZ($index, $X, $Z);
			$this->saveChunk($X, $Z, $force);
		}
	}

}