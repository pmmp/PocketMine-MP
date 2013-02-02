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

class Item{
	protected $id;
	protected $meta;
	protected $maxStackSize = 64;
	protected $durability = 0;
	protected $name = "Unknown";
	
	public function __construct($id, $meta = 0){
		$this->id = (int) $id;
		$this->meta = (int) $meta;
	}
	
	
	
	public function getMaxStackSize(){
		return $this->maxStackSize;
	}
	
	public function getDestroySpeed(Item $item, Player $player){
		return 1;
	}
	
}