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


class GenericBlock extends Block{
    /**
     * @param int $id
     * @param int $meta
     * @param string $name
     */
    public function __construct($id, $meta = 0, $name = "Unknown"){
		parent::__construct($id, $meta, $name);
	}

    /**
     * @param Item $item
     * @param Player $player
     * @param Block $block
     * @param Block $target
     * @param integer $face
     * @param integer $fx
     * @param integer $fy
     * @param integer $fz
     *
     * @return mixed
     */
    public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		return $this->level->setBlock($this, $this, true, false, true);
	}

    /**
     * @param Item $item
     * @param Player $player
     *
     * @return boolean
     */
    public function isBreakable(Item $item, Player $player){
		return ($this->breakable);
	}

    /**
     * @param Item $item
     * @param Player $player
     *
     * @return mixed
     */
    public function onBreak(Item $item, Player $player){
		return $this->level->setBlock($this, new AirBlock(), true, false, true);
	}

    /**
     * @param integer $type
     *
     * @return boolean
     */
    public function onUpdate($type){
		if($this->hasPhysics === true and $type === BLOCK_UPDATE_NORMAL){
			$down = $this->getSide(0);
			if($down->getID() === AIR or ($down instanceof LiquidBlock)){
				$data = array(
					"x" => $this->x + 0.5,
					"y" => $this->y + 0.5,
					"z" => $this->z + 0.5,
					"Tile" => $this->id,
				);
				$server = ServerAPI::request();
				$this->level->setBlock($this, new AirBlock(), false, false, true);
				$e = $server->api->entity->add($this->level, ENTITY_FALLING, FALLING_SAND, $data);
				$server->api->entity->spawnToAll($e);
				$server->api->block->blockUpdateAround(clone $this, BLOCK_UPDATE_NORMAL, 1);
			}
			return false;
		}
		return false;
	}

    /**
     * @param Item $item
     * @param Player $player
     *
     * @return boolean
     */
    public function onActivate(Item $item, Player $player){
		return $this->isActivable;
	}
}