<?php

/*

           -
         /   \
      /         \
   /    POCKET     \
/    MINECRAFT PHP    \
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

class ChunkParser{
	private $location, $raw = b"", $file;
	var $sectorLenght = 4096; //16 * 16 * 16
	var $chunkLenght = 86016; //21 * $sectorLenght
	var $map;
	
	function __construct(){
		$map = array();
	}
	
	private function loadLocationTable(){
		$this->location = array();
		console("[DEBUG] Loading Chunk Location table...", true, true, 2);
		$chunkCnt = 0;
		for($offset = 0; $offset < 0x1000; $offset += 4){
			$data = substr($this->raw, $offset, 4);
			$sectors = ord($data{0});
			if($sectors === 0){
				continue;
			}
			$x = ord($data{1});
			$z = ord($data{2});
			$X = $chunkCnt % 16;
			$Z = $chunkCnt >> 4;
			//$unused = ord($data{3});
			if(!isset($this->location[$X])){
				$this->location[$X] = array();
			}
			$this->location[$X][$Z] = $this->getOffset($X, $Z, $sectors);
			++$chunkCnt;
		}
	}
	
	public function loadFile($file){
		if(!file_exists($file)){
			return false;
		}
		$this->file = $file;
		$this->raw = file_get_contents($file);
		$this->chunkLenght = $this->sectorLenght * ord($this->raw{0});
		return true;
	}
	
	private function getOffsetPosition($X, $Z){
        $data = substr($this->raw, ($X << 2) + ($Z << 7), 4); //$X * 4 + $Z * 128
		return array(ord($data{0}), ord($data{1}), ord($data{2}), ord($data{3}));
    }
	
	private function getOffset($X, $Z, $sectors = 21){
		return 0x1000 + (($Z * $sectors) << 12) + (($X * $sectors) << 16);
    }
	
	public function getChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		return substr($this->raw, $this->getOffset($X, $Z), $this->chunkLenght);
	}
	
	public function writeChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		if(!isset($this->map[$X][$Z])){
			return false;
		}
		$chunk = "";
		foreach($this->map[$X][$Z] as $section => $data){
			for($i = 0; $i < 256; ++$i){
					$chunk .= $data[$i];
			}
		}
		return Utils::writeLInt(strlen($chunk)).$chunk;
	}
	
	public function parseChunk($X, $Z){
		$X = (int) $X;
		$Z = (int) $Z;
		$offset = $this->location[$X][$Z];
		$len = Utils::readLInt(substr($this->raw, $offset, 4));
		$offset += 4;
		$chunk = array(
			0 => array(), //Block
			1 => array(), //Data
			2 => array(), //SkyLight
			3 => array(), //BlockLight
		);
		foreach($chunk as $section => &$data){
			$l = $section === 0 ? 128:64;
			for($i = 0; $i < 256; ++$i){
				$data[] = substr($this->raw, $offset, $l);
				$offset += $l;
			}
		}
		return $chunk;
	}

	public function getRawColumn($offset, $l){
		$data = "";
		if($l === 128){
			$data = substr($this->raw, $offset, $l);
		}elseif($l === 64){
			for($i = 0; $i < $l; ++$i){
				$d = ord($this->raw{$offset + $i});
				$data .= chr($d >> 4);
				$data .= chr($d & 0x0F);
			}
		}
		return $data;
	}
	
	public function parseColumn($offset, $l){
		$data = array();
		if($l === 128){
			for($i = 0; $i < $l; ++$i){
				$data[] = ord($this->raw{$offset + $i});
			}
		}elseif($l === 64){
			for($i = 0; $i < $l; ++$i){
				$d = ord($this->raw{$offset + $i});
				$data[] = $d >> 4;
				$data[] = $d & 0x0F;
			}
		}
		return $data;
	}
	
	public function loadMap(){
		$this->loadLocationTable();
		console("[DEBUG] Loading chunks...", true, true, 2);
		for($x = 0; $x < 16; ++$x){
			$this->map[$x] = array();
			for($z = 0; $z < 16; ++$z){
				$this->map[$x][$z] = $this->parseChunk($x, $z);
			}		
		}
		console("[DEBUG] Chunks loaded!", true, true, 2);
	}
	
	public function saveMap(){
		console("[DEBUG] Saving chunks...", true, true, 2);
		$fp = fopen($this->file, "r+b");
		flock($fp, LOCK_EX);
		foreach($this->map as $x => $d){
			foreach($d as $z => $chunk){
				fseek($fp, $this->getOffset($x, $z));
				fwrite($fp, $this->writeChunk($x, $z), $this->chunkLenght);
			}
		}
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	
	public function getBlock($x, $y, $z){
		$x = (int) $x;
		$y = (int) $y;
		$z = (int) $z;
		$X = $x >> 4;
		$Z = $z >> 4;
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$index = $aX + ($aZ << 4);
		$block = ord($this->map[$X][$Z][0][$index]{$y});
		$meta = ord($this->map[$X][$Z][1][$index]{$y >> 1});
		if(($y & 1) === 0){
			$meta = $meta & 0x0F;
		}else{
			$meta = $meta >> 4;
		}
		return array($block, $meta);
	}
	
	public function setBlock($x, $y, $z, $block, $meta = 0){
		$x = (int) $x;
		$y = (int) $y;
		$z = (int) $z;
		$X = $x >> 4;
		$Z = $z >> 4;
		$aX = $x - ($X << 4);
		$aZ = $z - ($Z << 4);
		$index = $aX + ($aZ << 4);
		$this->map[$X][$Z][0][$index]{$y} = chr($block);
		$old_meta = ord($this->map[$X][$Z][1][$index]{$y >> 1});
		if(($y & 1) === 0){
			$meta = ($old_meta & 0xF0) | ($meta & 0x0F);
		}else{
			$meta = (($meta << 4) & 0xF0) | ($old_meta & 0x0F);
		}
		$this->map[$X][$Z][1][$index]{$y >> 1} = chr($meta);
	}

}