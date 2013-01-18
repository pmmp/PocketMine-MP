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
	public $id;
	protected $maxStackSize = 64;
	private $durability = 0;
	private $name = "Unknown";
	public function __construct($id){
		$this->id = (int) $id;
	}
	
	public function setMaxStackSize($size = 64){
		$this->maxStackSize = (int) $size;
	}
	
	public function getDestroySpeed(Item $item, Entity $entity){
		return 1;
	}
	
	public function getMaxStackSize(){
		return $this->maxStackSize;
	}
	
}