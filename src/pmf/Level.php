<?php

/**
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

define("PMF_CURRENT_LEVEL_VERSION", 0x00);

class PMFLevel extends PMF{
	private $levelData = array();
	private $locationTable = array();
	public $isLoaded = true;
	private $log = 4;
	private $payloadOffset = 0;
	private $chunks = array();
	private $chunkChange = array();
	
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
	
	public function close(){
		$chunks = null;
		unset($chunks, $chunkChange, $locationTable);
		parent::close();
	}
	
	public function __construct($file, $blank = false){
		if(is_array($blank)){
			$this->create($file, 0);
			$this->levelData = $blank;
			$this->createBlank();
			$this->isLoaded = true;
			$this->log = (int) ((string) log($this->levelData["width"], 2));
		}else{
			if($this->load($file) !== false){
				$this->parseInfo();
				if($this->parseLevel() === false){
					$this->isLoaded = false;
				}else{
					$this->isLoaded = true;
					$this->log = (int) ((string) log($this->levelData["width"], 2));
				}
			}else{
				$this->isLoaded = false;
			}
		}
	}
	
	public function saveData($locationTable = true){
		$this->levelData["version"] = PMF_CURRENT_LEVEL_VERSION;
		@ftruncate($this->fp, 5);
		$this->seek(5);
		$this->write(chr($this->levelData["version"]));
		$this->write(Utils::writeShort(strlen($this->levelData["name"])).$this->levelData["name"]);
		$this->write(Utils::writeInt($this->levelData["seed"]));
		$this->write(Utils::writeInt($this->levelData["time"]));
		$this->write(Utils::writeFloat($this->levelData["spawnX"]));
		$this->write(Utils::writeFloat($this->levelData["spawnY"]));
		$this->write(Utils::writeFloat($this->levelData["spawnZ"]));
		$this->write(chr($this->levelData["width"]));
		$this->write(chr($this->levelData["height"]));
		$extra = gzdeflate($this->levelData["extra"], PMF_LEVEL_DEFLATE_LEVEL);
		$this->write(Utils::writeShort(strlen($extra)).$extra);
		$this->payloadOffset = ftell($this->fp);
		
		if($locationTable !== false){
			$this->writeLocationTable();
		}
	}
	
	private function createBlank(){
		$this->saveData(false);
		$this->locationTable = array();
		$cnt = pow($this->levelData["width"], 2);
		@mkdir(dirname($this->file)."/chunks/", 0755);
		for($index = 0; $index < $cnt; ++$index){
			$this->chunks[$index] = false;
			$this->chunkChange[$index] = false;
			$this->locationTable[$index] = array(
				0 => 0,
			);
			$this->write(Utils::writeShort(0));
			$X = $Z = null;
			$this->getXZ($index, $X, $Z);
			@file_put_contents($this->getChunkPath($X, $Z), gzdeflate("", PMF_LEVEL_DEFLATE_LEVEL));
		}
		if(!file_exists(dirname($this->file)."/entities.yml")){
			$entities = new Config(dirname($this->file)."/entities.yml", CONFIG_YAML);
			$entities->save();
		}
		if(!file_exists(dirname($this->file)."/tiles.yml")){
			$tiles = new Config(dirname($this->file)."/tiles.yml", CONFIG_YAML);
			$tiles->save();
		}
	}
	
	protected function parseLevel(){
		if($this->getType() !== 0x00){
			return false;
		}
		$this->seek(5);
		$this->levelData["version"] = ord($this->read(1));
		if($this->levelData["version"] > PMF_CURRENT_LEVEL_VERSION){
			return false;
		}
		$this->levelData["name"] = $this->read(Utils::readShort($this->read(2), false));
		$this->levelData["seed"] = Utils::readInt($this->read(4));
		$this->levelData["time"] = Utils::readInt($this->read(4));
		$this->levelData["spawnX"] = Utils::readFloat($this->read(4));
		$this->levelData["spawnY"] = Utils::readFloat($this->read(4));
		$this->levelData["spawnZ"] = Utils::readFloat($this->read(4));
		$this->levelData["width"] = ord($this->read(1));
		$this->levelData["height"] = ord($this->read(1));
		if(($this->levelData["width"] !== 16 and $this->levelData["width"] !== 32) or $this->levelData["height"] !== 8){
			return false;
		}
		$lastseek = ftell($this->fp);
		if(($len = $this->read(2)) === false or ($this->levelData["extra"] = @gzinflate($this->read(Utils::readShort($len, false)))) === false){ //Corruption protection
			console("[NOTICE] Empty/corrupt location table detected, forcing recovery");
			fseek($this->fp, $lastseek);
			$c = gzdeflate("");
			$this->write(Utils::writeShort(strlen($c)).$c);
			$this->payloadOffset = ftell($this->fp);
			$this->levelData["extra"] = "";
			$cnt = pow($this->levelData["width"], 2);			
			for($index = 0; $index < $cnt; ++$index){
				$this->write("\x00\xFF"); //Force index recreation
			}
			fseek($this->fp, $this->payloadOffset);
		}else{
			$this->payloadOffset = ftell($this->fp);
		}
		return $this->readLocationTable();
	}
	
	public function getIndex($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		return ($Z << $this->log) + $X;
	}
	
	public function getXZ($index, &$X = null, &$Z = null){
		$X = $index >> $this->log;
		$Z = $index & (pow($this->log, 2) - 1);
		return array($X, $Z);
	}
	
	private function readLocationTable(){
		$this->locationTable = array();
		$cnt = pow($this->levelData["width"], 2);
		$this->seek($this->payloadOffset);
		for($index = 0; $index < $cnt; ++$index){
			$this->chunks[$index] = false;
			$this->chunkChange[$index] = false;
			$this->locationTable[$index] = array(
				0 => Utils::readShort($this->read(2)), //16 bit flags
			);
		}
		return true;
	}
	
	private function writeLocationTable(){
		$cnt = pow($this->levelData["width"], 2);
		@ftruncate($this->fp, $this->payloadOffset);
		$this->seek($this->payloadOffset);
		for($index = 0; $index < $cnt; ++$index){
			$this->write(Utils::writeShort($this->locationTable[$index][0]));
		}
	}
	
	private function getChunkPath($X, $Z){
		return dirname($this->file)."/chunks/".$Z.".".$X.".pmc";
	}
	
	public function loadChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		$index = $this->getIndex($X, $Z);
		if($this->isChunkLoaded($X, $Z)){
			return true;
		}elseif(!isset($this->locationTable[$index])){
			return false;
		}
		$info = $this->locationTable[$index];
		$this->seek($info[0]);

		$chunk = @gzopen($this->getChunkPath($X, $Z), "rb");
		if($chunk === false){
			return false;
		}
		$this->chunks[$index] = array();
		$this->chunkChange[$index] = array(-1 => false);
		for($Y = 0; $Y < $this->levelData["height"]; ++$Y){
			$t = 1 << $Y;
			if(($info[0] & $t) === $t){
				// 4096 + 2048 + 2048, Block Data, Meta, Light
				if(strlen($this->chunks[$index][$Y] = gzread($chunk, 8192)) < 8192){
					console("[NOTICE] Empty corrupt chunk detected [$X,$Z,:$Y], recovering contents");
					$this->fillMiniChunk($X, $Z, $Y);
				}
			}else{
				$this->chunks[$index][$Y] = false;
			}
		}
		@gzclose($chunk);
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
		$index = $this->getIndex($X, $Z);
		$this->chunks[$index] = null;
		$this->chunkChange[$index] = null;
		unset($this->chunks[$index], $this->chunkChange[$index]);
		return true;
	}
	
	public function isChunkLoaded($X, $Z){
		$index = $this->getIndex($X, $Z);
		if(!isset($this->chunks[$index]) or $this->chunks[$index] === false){
			return false;
		}
		return true;
	}
	
	protected function isMiniChunkEmpty($X, $Z, $Y){
		$index = $this->getIndex($X, $Z);
		if($this->chunks[$index][$Y] !== false){
			if(substr_count($this->chunks[$index][$Y], "\x00") < 8192){
				return false;
			}
		}
		return true;
	}
	
	protected function fillMiniChunk($X, $Z, $Y){
		if($this->isChunkLoaded($X, $Z) === false){
			return false;
		}
		$index = $this->getIndex($X, $Z);
		$this->chunks[$index][$Y] = str_repeat("\x00", 8192);
		$this->chunkChange[$index][-1] = true;
		$this->chunkChange[$index][$Y] = 8192;
		$this->locationTable[$index][0] |= 1 << $Y;
		return true;
	}
	
	public function getMiniChunk($X, $Z, $Y){
		if($this->loadChunk($X, $Z) === false){
			return str_repeat("\x00", 8192);
		}
		$index = $this->getIndex($X, $Z);
		if(!isset($this->chunks[$index][$Y]) or $this->chunks[$index][$Y] === false){
			return str_repeat("\x00", 8192);
		}
		return $this->chunks[$index][$Y];
	}
	
	public function setMiniChunk($X, $Z, $Y, $data){
		if($this->isChunkLoaded($X, $Z) === false){
			$this->loadChunk($X, $Z);
		}
		if(strlen($data) !== 8192){
			return false;
		}
		$index = $this->getIndex($X, $Z);
		$this->chunks[$index][$Y] = (string) $data;
		$this->chunkChange[$index][-1] = true;
		$this->chunkChange[$index][$Y] = 8192;
		$this->locationTable[$index][0] |= 1 << $Y;
		return true;
	}
	
	public function getBlockID($x, $y, $z){
		if($y > 127 or $y < 0 or $x < 0 or $z < 0 or $x > 255 or $z > 255){
			return 0;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$index = $this->getIndex($X, $Z);
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$b = ord($this->chunks[$index][$Y]{(int) ($aY + ($aX << 5) + ($aZ << 9))});
		return $b;		
	}
	
	public function setBlockID($x, $y, $z, $block){
		if($y > 127 or $y < 0 or $x < 0 or $z < 0 or $x > 255 or $z > 255){
			return false;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$block &= 0xFF;
		if($X >= 32 or $Z >= 32 or $Y >= $this->levelData["height"] or $y < 0){
			return false;
		}
		$index = $this->getIndex($X, $Z);
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
		if($y > 127 or $y < 0 or $x < 0 or $z < 0 or $x > 255 or $z > 255){
			return 0;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$index = $this->getIndex($X, $Z);
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
		if($y > 127 or $y < 0 or $x < 0 or $z < 0 or $x > 255 or $z > 255){
			return false;
		}
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$damage &= 0x0F;
		if($X >= 32 or $Z >= 32 or $Y >= $this->levelData["height"] or $y < 0){
			return false;
		}
		$index = $this->getIndex($X, $Z);
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
		if($x < 0 or $z < 0 or $X >= $this->levelData["width"] or $Z >= $this->levelData["width"] or $Y >= $this->levelData["height"] or $y < 0){
			return array(AIR, 0);
		}
		$index = $this->getIndex($X, $Z);
		if(!isset($this->chunks[$index]) or $this->chunks[$index] === false){
			if($this->loadChunk($X, $Z) === false){
				return array(AIR, 0);
			}
		}elseif($this->chunks[$index][$Y] === false){
			return array(AIR, 0);
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
		$X = $x >> 4;
		$Z = $z >> 4;
		$Y = $y >> 4;
		$block &= 0xFF;
		$meta &= 0x0F;
		if($X >= 32 or $Z >= 32 or $Y >= $this->levelData["height"] or $y < 0){
			return false;
		}
		$index = $this->getIndex($X, $Z);
		if(!isset($this->chunks[$index]) or $this->chunks[$index] === false){
			if($this->loadChunk($X, $Z) === false){
				return false;
			}
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
			if($old_b instanceof LiquidBlock){
				$pos = new Position($x, $y, $z, $this->level);
				for($side = 0; $side <= 5; ++$side){
					$b = $pos->getSide($side);
					if($b instanceof LavaBlock){
						ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($b, 0, 0, $this->level), 40, BLOCK_UPDATE_NORMAL);
					}else{
						ServerAPI::request()->api->block->scheduleBlockUpdate(new Position($b, 0, 0, $this->level), 10, BLOCK_UPDATE_NORMAL);
					}
				}
			}
			return true;
		}
		return false;
	}
	
	public function saveChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		if(!$this->isChunkLoaded($X, $Z)){
			return false;
		}
		$index = $this->getIndex($X, $Z);
		if(!isset($this->chunkChange[$index]) or $this->chunkChange[$index][-1] === false){//No changes in chunk
			return true;
		}
		
		$chunk = @gzopen($this->getChunkPath($X, $Z), "wb".PMF_LEVEL_DEFLATE_LEVEL);
		$bitmap = 0;
		for($Y = 0; $Y < $this->levelData["height"]; ++$Y){
			if($this->chunks[$index][$Y] !== false and ((isset($this->chunkChange[$index][$Y]) and $this->chunkChange[$index][$Y] === 0) or !$this->isMiniChunkEmpty($X, $Z, $Y))){
				gzwrite($chunk, $this->chunks[$index][$Y]);
				$bitmap |= 1 << $Y;
			}else{
				$this->chunks[$index][$Y] = false;
			}
			$this->chunkChange[$index][$Y] = 0;
		}
		$this->chunkChange[$index][-1] = false;
		$this->locationTable[$index][0] = $bitmap;
		$this->seek($this->payloadOffset + ($index << 1));
		$this->write(Utils::writeShort($this->locationTable[$index][0]));
		return true;
	}
	
	public function doSaveRound(){
		foreach($this->chunks as $index => $chunk){
			$this->getXZ($index, $X, $Z);
			$this->saveChunk($X, $Z);
		}
	}

}
