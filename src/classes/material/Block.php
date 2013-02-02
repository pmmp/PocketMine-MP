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


abstract class Block{

	protected $id;
	protected $meta;
	protected $shortname = "";
	protected $name = "";
	public $isActivable = false;
	public $isBreakable = true;
	public $isFlowable = false;
	public $isTransparent = false;
	public $isReplaceable = false;
	public $isPlaceable = true;
	public $inWorld = false;
	public $hasPhysics = false;
	public $v = false;
	
	public function __construct($id, $meta = 0, $name = "Unknown"){
		$this->id = (int) $id;
		$this->meta = (int) $meta;
		$this->name = $name;
		$this->shortname = strtolower(str_replace(" ", "_", $name));
	}
	
	public function getName(){
		return $this->name;
	}
	
	final public function getID(){
		return $id;
	}
	
	final public function getMetadata(){
		return $meta & 0x0F;
	}
	
	final public function position(Vector3 $v){
		$this->inWorld = true;
		$this->position = new Vector3((int) $v->x, (int) $v->y, (int) $v->z);
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array($this->id, $this->meta, 1),
		);
	}
	
	abstract function onActivate(LevelAPI $level, Item $item, Player $player);
	
	abstract function onUpdate(LevelAPI $level, $type);
}

require_once("block/GenericBlock.php");
require_once("block/SolidBlock.php");
require_once("block/TransparentBlock.php");
require_once("block/FallableBlock.php");