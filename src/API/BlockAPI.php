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

class BlockAPI{
	private $server;
	private $scheduledUpdates = array();
	private $randomUpdates = array();
	public static $creative = array(
	
		//Building
		array(STONE, 0),
		array(COBBLESTONE, 0),
		array(STONE_BRICKS, 0),
		array(STONE_BRICKS, 1),
		array(STONE_BRICKS, 2),
		array(MOSS_STONE, 0),
		array(WOODEN_PLANKS, 0),
		array(WOODEN_PLANKS, 1),
		array(WOODEN_PLANKS, 2),
		array(WOODEN_PLANKS, 3),
		array(BRICKS, 0),
		
		array(DIRT, 0),
		array(GRASS, 0),
		array(CLAY_BLOCK, 0),
		array(SANDSTONE, 0),
		array(SANDSTONE, 1),
		array(SANDSTONE, 2),
		array(SAND, 0),
		array(GRAVEL, 0),
		array(TRUNK, 0),
		array(TRUNK, 1),
		array(TRUNK, 2),
		array(TRUNK, 3),
		array(NETHER_BRICKS, 0),
		array(NETHERRACK, 0),
		array(BEDROCK, 0),
		array(COBBLESTONE_STAIRS, 0),
		array(OAK_WOODEN_STAIRS, 0),
		array(SPRUCE_WOODEN_STAIRS, 0),
		array(BIRCH_WOODEN_STAIRS, 0),
		array(JUNGLE_WOODEN_STAIRS, 0),
		array(BRICK_STAIRS, 0),
		array(SANDSTONE_STAIRS, 0),
		array(STONE_BRICK_STAIRS, 0),
		array(NETHER_BRICKS_STAIRS, 0),
		array(QUARTZ_STAIRS, 0),
		array(SLAB, 0),
		array(SLAB, 1),
		array(WOODEN_SLAB, 0),
		array(WOODEN_SLAB, 1),
		array(WOODEN_SLAB, 2),
		array(WOODEN_SLAB, 3),
		array(SLAB, 3),
		array(SLAB, 4),
		array(SLAB, 5),
		array(SLAB, 6),
		array(QUARTZ_BLOCK, 0),
		array(QUARTZ_BLOCK, 1),
		array(QUARTZ_BLOCK, 2),
		array(COAL_ORE, 0),
		array(IRON_ORE, 0),
		array(GOLD_ORE, 0),
		array(DIAMOND_ORE, 0),
		array(LAPIS_ORE, 0),
		array(REDSTONE_ORE, 0),
		array(OBSIDIAN, 0),
		array(ICE, 0),
		array(SNOW_BLOCK, 0),
		
		//Decoration
		array(COBBLESTONE_WALL, 0),
		array(COBBLESTONE_WALL, 1),
		array(GOLD_BLOCK, 0),
		array(IRON_BLOCK, 0),
		array(DIAMOND_BLOCK, 0),
		array(LAPIS_BLOCK, 0),
		array(COAL_BLOCK, 0),
		array(SNOW_LAYER, 0),
		array(GLASS, 0),
		array(GLOWSTONE_BLOCK, 0),
		array(NETHER_REACTOR, 0),
		array(WOOL, 0),
		array(WOOL, 7),
		array(WOOL, 6),
		array(WOOL, 5),
		array(WOOL, 4),
		array(WOOL, 3),
		array(WOOL, 2),
		array(WOOL, 1),
		array(WOOL, 15),
		array(WOOL, 14),
		array(WOOL, 13),
		array(WOOL, 12),
		array(WOOL, 11),
		array(WOOL, 10),
		array(WOOL, 9),
		array(WOOL, 8),
		array(LADDER, 0),
		array(SPONGE, 0),
		array(GLASS_PANE, 0),
		array(WOODEN_DOOR, 0),
		array(TRAPDOOR, 0),
		array(FENCE, 0),
		array(FENCE_GATE, 0),
		array(IRON_BARS, 0),
		array(BED, 0),
		array(BOOKSHELF, 0),
		array(PAINTING, 0),
		array(WORKBENCH, 0),
		array(STONECUTTER, 0),
		array(CHEST, 0),
		array(FURNACE, 0),
		array(DANDELION, 0),
		array(CYAN_FLOWER, 0),
		array(BROWN_MUSHROOM, 0),
		array(RED_MUSHROOM, 0),
		array(CACTUS, 0),
		array(MELON_BLOCK, 0),
		array(PUMPKIN, 0),
		array(LIT_PUMPKIN, 0),
		array(COBWEB, 0),
		array(HAY_BALE, 0),
		array(TALL_GRASS, 1),
		array(TALL_GRASS, 2),
		array(DEAD_BUSH, 0),
		array(SAPLING, 0),
		array(SAPLING, 1),
		array(SAPLING, 2),
		array(SAPLING, 3),
		array(LEAVES, 0),
		array(LEAVES, 1),
		array(LEAVES, 2),
		array(LEAVES, 3),
		array(CAKE, 0),
		array(SIGN, 0),
		array(CARPET, 0),
		array(CARPET, 7),
		array(CARPET, 6),
		array(CARPET, 5),
		array(CARPET, 4),
		array(CARPET, 3),
		array(CARPET, 2),
		array(CARPET, 1),
		array(CARPET, 15),
		array(CARPET, 14),
		array(CARPET, 13),
		array(CARPET, 12),
		array(CARPET, 11),
		array(CARPET, 10),
		array(CARPET, 9),
		array(CARPET, 8),
		
		//Tools
		//array(RAILS, 0),
		//array(POWERED_RAILS, 0),
		array(TORCH, 0),		
		array(BUCKET, 0),
		array(BUCKET, 8),
		array(BUCKET, 10),
		array(TNT, 0),
		array(IRON_HOE, 0),
		array(IRON_SWORD, 0),
		array(BOW, 0),
		array(SHEARS, 0),
		array(FLINT_AND_STEEL, 0),
		array(CLOCK, 0),
		array(COMPASS, 0),
		array(MINECART, 0),
		array(SPAWN_EGG, MOB_CHICKEN),
		array(SPAWN_EGG, MOB_COW),
		array(SPAWN_EGG, MOB_PIG),
		array(SPAWN_EGG, MOB_SHEEP),
		
		//Seeds
		array(SUGARCANE, 0),
		array(WHEAT, 0),
		array(SEEDS, 0),
		array(MELON_SEEDS, 0),
		array(PUMPKIN_SEEDS, 0),
		array(CARROT, 0),
		array(POTATO, 0),
		array(BEETROOT_SEEDS, 0),
		array(EGG, 0),
		array(DYE, 0),
		array(DYE, 7),
		array(DYE, 6),
		array(DYE, 5),
		array(DYE, 4),
		array(DYE, 3),
		array(DYE, 2),
		array(DYE, 1),
		array(DYE, 15),
		array(DYE, 14),
		array(DYE, 13),
		array(DYE, 12),
		array(DYE, 11),
		array(DYE, 10),
		array(DYE, 9),
		array(DYE, 8),
		
	);
	
	public static function fromString($str, $multiple = false){
		if($multiple === true){
			$blocks = array();
			foreach(explode(",",$str) as $b){
				$blocks[] = BlockAPI::fromString($b, false);
			}
			return $blocks;
		}else{
			$b = explode(":", str_replace(" ", "_", trim($str)));
			if(!isset($b[1])){
				$meta = 0;
			}else{
				$meta = ((int) $b[1]) & 0xFFFF;
			}
			
			if(defined(strtoupper($b[0]))){
				$item = BlockAPI::getItem(constant(strtoupper($b[0])), $meta);
				if($item->getID() === AIR and strtoupper($b[0]) !== "AIR"){
					$item = BlockAPI::getItem(((int) $b[0]) & 0xFFFF, $meta);
				}
			}else{
				$item = BlockAPI::getItem(((int) $b[0]) & 0xFFFF, $meta);
			}
			return $item;
		}
	}

	public static function get($id, $meta = 0, $v = false){
		if(isset(Block::$class[$id])){
			$classname = Block::$class[$id];
			$b = new $classname($meta);
		}else{
			$b = new GenericBlock((int) $id, $meta);
		}
		if($v instanceof Position){
			$b->position($v);
		}
		return $b;
	}
	
	public static function getItem($id, $meta = 0, $count = 1){
		$id = (int) $id;
		if(isset(Item::$class[$id])){
			$classname = Item::$class[$id];
			$i = new $classname($meta, $count);
		}else{
			$i = new Item($id, $meta, $count);
		}
		return $i;
	}
	
	function __construct(){
		$this->server = ServerAPI::request();
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
				$player = $this->server->api->player->get($params[0]);
				$item = BlockAPI::fromString($params[1]);
				
				if(!isset($params[2])){
					$item->count = $item->getMaxStackSize();
				}else{
					$item->count = (int) $params[2];
				}

				if($player instanceof Player){
					if(($player->gamemode & 0x01) === 0x01){
						$output .= "Player is in creative mode.\n";
						break;
					}
					if($item->getID() == 0) {
						$output .= "You cannot give an air block to a player.\n";
						break;
					}
					$player->addItem($item->getID(), $item->getMetadata(), $item->count);
					$output .= "Giving ".$item->count." of ".$item->getName()." (".$item->getID().":".$item->getMetadata().") to ".$player->username."\n";
				}else{
					$output .= "Unknown player.\n";
				}

				break;
		}
		return $output;
	}

	private function cancelAction(Block $block, Player $player, $send = true){
		$pk = new UpdateBlockPacket;
		$pk->x = $block->x;
		$pk->y = $block->y;
		$pk->z = $block->z;
		$pk->block = $block->getID();
		$pk->meta = $block->getMetadata();
		$player->dataPacket($pk);
		if($send === true){
			$player->sendInventorySlot($player->slot);
		}
		return false;
	}

	public function playerBlockBreak(Player $player, Vector3 $vector){

		$target = $player->level->getBlock($vector);
		$item = $player->getSlot($player->slot);
		
		if($this->server->api->dhandle("player.block.touch", array("type" => "break", "player" => $player, "target" => $target, "item" => $item)) === false){
			if($this->server->api->dhandle("player.block.break.bypass", array("player" => $player, "target" => $target, "item" => $item)) !== true){
				return $this->cancelAction($target, $player, false);
			}
		}
		
		if((!$target->isBreakable($item, $player) and $this->server->api->dhandle("player.block.break.invalid", array("player" => $player, "target" => $target, "item" => $item)) !== true) or ($player->gamemode & 0x02) === 0x02 or (($player->lastBreak - $player->getLag() / 1000) + $target->getBreakTime($item, $player) - 0.1) >= microtime(true)){
			if($this->server->api->dhandle("player.block.break.bypass", array("player" => $player, "target" => $target, "item" => $item)) !== true){
				return $this->cancelAction($target, $player, false);			
			}
		}
		$player->lastBreak = microtime(true);
		
		if($this->server->api->dhandle("player.block.break", array("player" => $player, "target" => $target, "item" => $item)) !== false){
			$drops = $target->getDrops($item, $player);
			if($target->onBreak($item, $player) === false){
				return $this->cancelAction($target, $player, false);
			}
			if(($player->gamemode & 0x01) === 0 and $item->useOn($target) and $item->getMetadata() >= $item->getMaxDurability()){
				$player->setSlot($player->slot, new Item(AIR, 0, 0), false);
			}
		}else{
			return $this->cancelAction($target, $player, false);
		}
		
		
		if(($player->gamemode & 0x01) === 0x00 and count($drops) > 0){
			foreach($drops as $drop){
				$this->server->api->entity->drop(new Position($target->x + 0.5, $target->y, $target->z + 0.5, $target->level), BlockAPI::getItem($drop[0] & 0xFFFF, $drop[1] & 0xFFFF, $drop[2]));
			}
		}
		return false;
	}

	public function playerBlockAction(Player $player, Vector3 $vector, $face, $fx, $fy, $fz){
		if($face < 0 or $face > 5){
			return false;
		}

		$target = $player->level->getBlock($vector);		
		$block = $target->getSide($face);
		$item = $player->getSlot($player->slot);
		
		if($target->getID() === AIR and $this->server->api->dhandle("player.block.place.invalid", array("player" => $player, "block" => $block, "target" => $target, "item" => $item)) !== true){ //If no block exists or not allowed in CREATIVE
			if($this->server->api->dhandle("player.block.place.bypass", array("player" => $player, "block" => $block, "target" => $target, "item" => $item)) !== true){
				$this->cancelAction($target, $player);
				return $this->cancelAction($block, $player);
			}
		}
		
		if($this->server->api->dhandle("player.block.touch", array("type" => "place", "player" => $player, "block" => $block, "target" => $target, "item" => $item)) === false){
			if($this->server->api->dhandle("player.block.place.bypass", array("player" => $player, "block" => $block, "target" => $target, "item" => $item)) !== true){
				return $this->cancelAction($block, $player);
			}
		}
		$this->blockUpdate($target, BLOCK_UPDATE_TOUCH);

		if($target->isActivable === true){
			if($this->server->api->dhandle("player.block.activate", array("player" => $player, "block" => $block, "target" => $target, "item" => $item)) !== false and $target->onActivate($item, $player) === true){
				return false;
			}
		}
		
		if(($player->gamemode & 0x02) === 0x02){ //Adventure mode!!
			if($this->server->api->dhandle("player.block.place.bypass", array("player" => $player, "block" => $block, "target" => $target, "item" => $item)) !== true){
				return $this->cancelAction($block, $player, false);
			}
		}

		if($block->y > 127 or $block->y < 0){
			return false;
		}
		
		if($item->isActivable === true and $item->onActivate($player->level, $player, $block, $target, $face, $fx, $fy, $fz) === true){
			if($item->count <= 0){
				$player->setSlot($player->slot, BlockAPI::getItem(AIR, 0, 0), false);
			}
			return false;
		}

		if($item->isPlaceable()){
			$hand = $item->getBlock();
			$hand->position($block);
		}elseif($block->getID() === FIRE){
			$player->level->setBlock($block, new AirBlock(), true, false, true);
			return false;
		}else{
			return $this->cancelAction($block, $player, false);
		}
		
		if(!($block->isReplaceable === true or ($hand->getID() === SLAB and $block->getID() === SLAB))){
			return $this->cancelAction($block, $player, false);
		}
		
		if($target->isReplaceable === true){
			$block = $target;
			$hand->position($block);			
			//$face = -1;
		}
		
		if($hand->isSolid === true and $player->entity->inBlock($block)){
			return $this->cancelAction($block, $player, false); //Entity in block
		}
		
		if($this->server->api->dhandle("player.block.place", array("player" => $player, "block" => $block, "target" => $target, "item" => $item)) === false){
			return $this->cancelAction($block, $player);
		}elseif($hand->place($item, $player, $block, $target, $face, $fx, $fy, $fz) === false){
			return $this->cancelAction($block, $player, false);
		}
		if($hand->getID() === SIGN_POST or $hand->getID() === WALL_SIGN){
			$t = $this->server->api->tile->addSign($player->level, $block->x, $block->y, $block->z);
			$t->data["creator"] = $player->username;
		}

		if(($player->gamemode & 0x01) === 0x00){
			--$item->count;
			if($item->count <= 0){
				$player->setSlot($player->slot, BlockAPI::getItem(AIR, 0, 0), false);
			}
		}

		return false;
	}

	public function blockUpdateAround(Position $pos, $type = BLOCK_UPDATE_NORMAL, $delay = false){		
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
	
	public function blockUpdate(Position $pos, $type = BLOCK_UPDATE_NORMAL){
		if(!($pos instanceof Block)){
			$block = $pos->level->getBlock($pos);
		}else{
			$pos = new Position($pos->x, $pos->y, $pos->z, $pos->level);
			$block = $pos->level->getBlock($pos);
		}
		if($block === false){
			return false;
		}
		
		$level = $block->onUpdate($type);
		if($level === BLOCK_UPDATE_NORMAL){
			$this->blockUpdateAround($block, $level);
			$this->server->api->entity->updateRadius($pos, 1);
		}elseif($level === BLOCK_UPDATE_RANDOM){
			$this->nextRandomUpdate($pos);
		}
		return $level;
	}
	
	public function scheduleBlockUpdate(Position $pos, $delay, $type = BLOCK_UPDATE_SCHEDULED){
		$type = (int) $type;
		if($delay < 0){
			return false;
		}

		$index = $pos->x.".".$pos->y.".".$pos->z.".".$pos->level->getName().".".$type;
		$delay = microtime(true) + $delay * 0.05;		
		if(!isset($this->scheduledUpdates[$index])){
			$this->scheduledUpdates[$index] = $pos;
			$this->server->query("INSERT INTO blockUpdates (x, y, z, level, type, delay) VALUES (".$pos->x.", ".$pos->y.", ".$pos->z.", '".$pos->level->getName()."', ".$type.", ".$delay.");");
			return true;
		}
		return false;
	}
	
	public function nextRandomUpdate(Position $pos){
		if(!isset($this->scheduledUpdates[$pos->x.".".$pos->y.".".$pos->z.".".$pos->level->getName().".".BLOCK_UPDATE_RANDOM])){
			$X = (($pos->x >> 4) << 4);
			$Y = (($pos->y >> 4) << 4);
			$Z = (($pos->z >> 4) << 4);
			$time = microtime(true);
			$i = 0;
			$offset = 0;
			while(true){
				$t = $offset + Utils::getRandomUpdateTicks() * 0.05;
				$update = $this->server->query("SELECT COUNT(*) FROM blockUpdates WHERE level = '".$pos->level->getName()."' AND type = ".BLOCK_UPDATE_RANDOM." AND delay >= ".($time + $t - 1)." AND delay <= ".($time + $t + 1).";");
				if($update instanceof SQLite3Result){
					$update = $update->fetchArray(SQLITE3_NUM);
					if($update[0] < 3){
						break;
					}
				}else{
					break;
				}
				$offset += mt_rand(25, 75);
			}
			$this->scheduleBlockUpdate($pos, $t / 0.05, BLOCK_UPDATE_RANDOM);
		}
	}
	
	public function blockUpdateTick(){
		$time = microtime(true);
		if(count($this->scheduledUpdates) > 0){
			$update = $this->server->query("SELECT x,y,z,level,type FROM blockUpdates WHERE delay <= ".$time.";");
			if($update instanceof SQLite3Result){
				$upp = array();
				while(($up = $update->fetchArray(SQLITE3_ASSOC)) !== false){
					$index = $up["x"].".".$up["y"].".".$up["z"].".".$up["level"].".".$up["type"];
					if(isset($this->scheduledUpdates[$index])){
						$upp[] = array((int) $up["type"], $this->scheduledUpdates[$index]);
						unset($this->scheduledUpdates[$index]);
					}
				}
				$this->server->query("DELETE FROM blockUpdates WHERE delay <= ".$time.";");
				foreach($upp as $b){
					$this->blockUpdate($b[1], $b[0]);
				}
			}
		}
	}

}
