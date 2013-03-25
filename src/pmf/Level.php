<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

/***REM_START***/
require_once(FILE_PATH."/src/pmf/PMF.php");
/***REM_END***/

define("PMF_CURRENT_LEVEL_VERSION", 0x00);

class PMFLevel extends PMF{
	private $levelData = array();
	private $locationTable = array();
	public $isLoaded = true;
	private $log = 4;
	private $payloadOffset = 0;
	private $chunks = array();
	private $chunkChange = array();
	
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
	
	private function createBlank(){			
		$this->levelData["version"] = PMF_CURRENT_LEVEL_VERSION;
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
		$this->write(gzdeflate("", 9));
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
			@file_put_contents($this->getChunkPath($X, $Z), gzdeflate("", 9));
		}
	}
	
	protected function parseLevel(){
		if($this->getType() !== 0x00){
			return false;
		}
		$this->seek(5);
		$this->levelData["version"] = ord($this->read(1));
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
		$this->levelData["extra"] = gzinflate($this->read(Utils::readShort($this->read(2), false))); //Additional custom plugin data
		if($this->levelData["extra"] === false){
			return false;
		}
		$this->payloadOffset = ftell($this->fp);
		return $this->readLocationTable();
	}
	
	public function getIndex($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		return $X << $this->log + $Z;
	}
	
	public function getXZ($index, &$X = null, &$Z = null){
		$X = $index >> $this->log;
		$Z = $index & (($this->log << 1) - 1);
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
	
	private function getChunkPath($X, $Z){
		return dirname($this->file)."/chunks/".$X.".".$Z.".pmc";
	}
	
	public function loadChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		if($this->isChunkLoaded($X, $Z)){
			return true;
		}
		$index = $this->getIndex($X, $Z);
		$info = $this->locationTable[$index];
		$this->seek($info[0]);

		$chunk = @gzopen($this->getChunkPath($X, $Z), "rb");
		if($chunk === false){
			return false;
		}
		$this->chunks[$index] = array();
		$this->chunkChange[$index] = array();
		for($Y = 0; $Y < $this->levelData["height"]; ++$Y){
			$t = 1 << $Y;
			if(($info[0] & $t) === $t){
				$this->chunks[$index][$Y] = gzread($chunk, 8192); // 4096 + 2048 + 2048, Block Data, Meta, Light
			}else{
				$this->chunks[$index][$Y] = false;
			}
		}		
		@gzclose($chunk);
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
			
		}
		return true;
	}
	
	public function getBlock($x, $y, $z){
		$X = $x << 4;
		$Z = $z << 4;
		$Y = $y << 4;
		if($X >= 32 or $Z >= 32){
			return array(AIR, 0);
		}
		$index = $this->getIndex($X, $Z);
		if($this->chunks[$index] === false){
			if($this->loadChunk($X, $Z) === false){
				return array(AIR, 0);
			}
		}elseif($this->chunks[$index][$Y] === false){
			return array(AIR, 0);
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$bindex = $aY + $aZ << 5 + $aX << 9;
		$mindex = $aY >> 1 + 16 + $aZ << 5 + $aX << 9;
		$b = ord($this->chunks[$index][$Y]{$bindex});
		$m = ord($this->chunks[$index][$Y]{$mindex});
		if(($y & 1) === 0){
			$m = $m & 0x0F;
		}else{
			$m = $m >> 4;
		}
		return array($b, $m);		
	}
	
	protected function fillMiniChunk($X, $Z, $Y){
		if($this->isChunkLoaded($X, $Z) === false){
			return false;
		}
		$this->chunks[$index][$Y] = str_repeat("\x00", 8192);
		$this->locationTable[$index][0] |= 1 << $Y;
		return true;
	}
	
	public function setBlock($x, $y, $z, $block, $meta = 0){
		$X = $x << 4;
		$Z = $z << 4;
		$Y = $y << 4;
		$block &= 0xFF;
		$meta &= 0x0F;
		if($X >= 32 or $Z >= 32){
			return false;
		}
		$index = $this->getIndex($X, $Z);
		if($this->chunks[$index] === false){
			if($this->loadChunk($X, $Z) === false){
				return false;
			}
		}elseif($this->chunks[$index][$Y] === false){
			$this->fillMiniChunk($X, $Z, $Y);
		}
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$aY = $y - ($Y << 4);
		$bindex = $aY + $aZ << 5 + $aX << 9;
		$mindex = $aY >> 1 + 16 + $aZ << 5 + $aX << 9;
		$old_b = $this->chunks[$index][$Y]{$bindex};		
		$old_m = ord($this->map[$X][$Z][1][$index]{$y >> 1});
		if(($y & 1) === 0){
			$old_m = $old_m & 0x0F;
			$m = ($old_m << 4) | ($meta & 0x0F);
		}else{
			$old_m = $old_m >> 4;
			$m = (($meta << 4) & 0xF0) | $old_m;
		}
		if($old_b !== $block or $old_m !== $meta){
			$this->chunks[$index][$Y]{$bindex} = chr($block & 0xFF);
			$this->chunks[$index][$Y]{$mindex} = chr($m);		
			if(!isset($this->chunkChange[$index][$Y])){
				$this->chunkChange[$index][$Y] = 1;
			}else{
				++$this->chunkChange[$index][$Y];
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
		$chunk = @gzopen($this->getChunkPath($X, $Z), "wb9");
		$bitmap = 0;
		for($Y = 0; $Y < $this->levelData["height"]; ++$Y){
			if($this->chunks[$index][$Y] !== false and !$this->isMiniChunkEmpty($X, $Z, $Y)){
				gzwrite($chunk, $this->chunks[$index][$Y]);
				$bitmap |= 1 << $Y;
			}else{
				$this->chunks[$index][$Y] = false;
			}
		}
		$this->locationTable[$index][0] = $bitmap;
		$this->seek($this->payloadOffset + $index << 2);
		$this->write(Utils::writeShort($this->locationTable[$index][0]));
	}

}