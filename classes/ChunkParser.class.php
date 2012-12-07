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

define("MAP_WIDTH", 256);
define("MAP_HEIGHT", 128);

class ChunkParser{
	private $raw = b"";
	var $sectorLenght = 4096; //16 * 16 * 16
	var $chunkLenght = 86016; //21 * $sectorLenght
	
	function __construct(){
		
	}
	
	public function loadFile($file){
		if(!file_exists($file)){
			return false;
		}
		$this->raw = file_get_contents($file);
		$this->chunkLenght = $this->sectorLenght * ord($this->raw{0});
		return true;
	}
	
	private function getOffsetPosition($X, $Z){
        $data = substr($this->raw, ($X << 2) + ($Z << 7), 4); //$X * 4 + $Z * 128
		return array(ord($data{0}), ord($data{1}), ord($data{2}), ord($data{3}));
    }
	
	private function getOffset($X, $Z){
        $info = $this->getOffsetPosition($X, $Z);		
		return 4096 + (($info[1] * $info[0]) << 12) + (($info[2] * $data[0]) << 16);
    }
	
	public function getChunk($X, $Z, $header = true){
		$X = (int) $X;
		$Z = (int) $Z;
		if($header === false){
			$add = 4;
		}else{
			$add = 0;
		}
		return substr($this->raw, $this->getOffset($X, $Z) + $add, $this->chunkLenght - $add);
	}
	
	public function getColumn($X, $Z){
	
	}
	
	public function getBlock($x, $y, $z){
		$x = (int) $x;
		$y = (int) $y;
		$z = (int) $z;
		$X = $x >> 4;
		$Z = $z >> 4;
		$block = $this->getOffset($X, $Z) + 4 + (($x << 6) + $y + ($z << 10));
		$meta = $this->getOffset($X, $Z) + 4 + (($x << 6) + $y + ($z << 10));
	}

}