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

namespace pocketmine;

use pocketmine\Block;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;

class BlockAPI{
	private $server;
	private $scheduledUpdates = array();


	function __construct(){
		$this->server = Server::getInstance();
	}

	public function init(){
		$this->server->schedule(1, array($this, "blockUpdateTick"), array(), true);
		$this->server->api->console->register("give", "<player> <item[:damage]> [amount]", array($this, "commandHandler"));
	}

	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "give":
				if(!isset($params[0]) or !isset($params[1])){
					$output .= "Usage: /give <player> <item[:damage]> [amount]\n";
					break;
				}
				$player = Player::get($params[0]);
				$item = Item::fromString($params[1]);

				if(!isset($params[2])){
					$item->setCount($item->getMaxStackSize());
				}else{
					$item->setCount((int) $params[2]);
				}

				if($player instanceof Player){
					if(($player->gamemode & 0x01) === 0x01){
						$output .= "Player is in creative mode.\n";
						break;
					}
					if($item->getID() == 0){
						$output .= "You cannot give an air block to a player.\n";
						break;
					}
					$player->addItem(clone $item);
					$output .= "Giving " . $item->getCount() . " of " . $item->getName() . " (" . $item->getID() . ":" . $item->getMetadata() . ") to " . $player->getName() . "\n";
				}else{
					$output .= "Unknown player.\n";
				}

				break;
		}

		return $output;
	}

	public function blockUpdateAround(Position $pos, $type = Level::BLOCK_UPDATE_NORMAL, $delay = false){
		if($delay !== false){
			$this->scheduleBlockUpdate($pos->getSide(0), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(1), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(2), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(3), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(4), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(5), $delay, $type);
		}else{
			$this->blockUpdate($pos->getSide(0), $type);
			$this->blockUpdate($pos->getSide(1), $type);
			$this->blockUpdate($pos->getSide(2), $type);
			$this->blockUpdate($pos->getSide(3), $type);
			$this->blockUpdate($pos->getSide(4), $type);
			$this->blockUpdate($pos->getSide(5), $type);
		}
	}

	public function blockUpdate(Position $pos, $type = Level::BLOCK_UPDATE_NORMAL){
		if(!($pos instanceof block\Block)){
			$block = $pos->level->getBlock($pos);
		}else{
			$pos = new Position($pos->x, $pos->y, $pos->z, $pos->level);
			$block = $pos->level->getBlock($pos);
		}
		if($block === false){
			return false;
		}

		$level = $block->onUpdate($type);
		if($level === Level::BLOCK_UPDATE_NORMAL){
			$this->blockUpdateAround($block, $level);
		}

		return $level;
	}

	public function scheduleBlockUpdate(Position $pos, $delay, $type = Level::BLOCK_UPDATE_SCHEDULED){
		$type = (int) $type;
		if($delay < 0){
			return false;
		}

		$index = $pos->x . "." . $pos->y . "." . $pos->z . "." . $pos->level->getName() . "." . $type;
		$delay = microtime(true) + $delay * 0.05;
		if(!isset($this->scheduledUpdates[$index])){
			$this->scheduledUpdates[$index] = $pos;
			$this->server->query("INSERT INTO blockUpdates (x, y, z, level, type, delay) VALUES (" . $pos->x . ", " . $pos->y . ", " . $pos->z . ", '" . $pos->level->getName() . "', " . $type . ", " . $delay . ");");

			return true;
		}

		return false;
	}

	public function blockUpdateTick(){
		$time = microtime(true);
		if(count($this->scheduledUpdates) > 0){
			$update = $this->server->query("SELECT x,y,z,level,type FROM blockUpdates WHERE delay <= " . $time . ";");
			if($update instanceof \SQLite3Result){
				$upp = array();
				while(($up = $update->fetchArray(SQLITE3_ASSOC)) !== false){
					$index = $up["x"] . "." . $up["y"] . "." . $up["z"] . "." . $up["level"] . "." . $up["type"];
					if(isset($this->scheduledUpdates[$index])){
						$upp[] = array((int) $up["type"], $this->scheduledUpdates[$index]);
						unset($this->scheduledUpdates[$index]);
					}
				}
				$this->server->query("DELETE FROM blockUpdates WHERE delay <= " . $time . ";");
				foreach($upp as $b){
					$this->blockUpdate($b[1], $b[0]);
				}
			}
		}
	}

}
