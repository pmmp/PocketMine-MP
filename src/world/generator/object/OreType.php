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

class OreType{
	public $material, $clusterCount, $clusterSize, $maxHeight, $minHeight;
	
	public function __construct(Block $material, $clusterCount, $clusterSize, $minHeight, $maxHeight){
		$this->material = $material;
		$this->clusterCount = (int) $clusterCount;
		$this->clusterSize = (int) $clusterSize;
		$this->maxHeight = (int) $maxHeight;
		$this->minHeight = (int) $minHeight;
	}
}