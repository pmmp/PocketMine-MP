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

namespace PocketMine;

use PocketMine\Level\Level;
use PocketMine\Block\Block;
use PocketMine\Item\Item;
use PocketMine\Level\Position;
use PocketMine\NBT\Tag\Compound;
use PocketMine\NBT\Tag\Int;
use PocketMine\NBT\Tag\String;
use PocketMine\Network\Protocol\UpdateBlockPacket;
use PocketMine\Tile\Sign;

class BlockAPI{
	private $server;
	private $scheduledUpdates = array();
	private $randomUpdates = array();
	public static $creative = array(
		//Building
		[Item::STONE, 0],
		[Item::COBBLESTONE, 0],
		[Item::STONE_BRICKS, 0],
		[Item::STONE_BRICKS, 1],
		[Item::STONE_BRICKS, 2],
		[Item::MOSS_STONE, 0],
		[Item::WOODEN_PLANKS, 0],
		[Item::WOODEN_PLANKS, 1],
		[Item::WOODEN_PLANKS, 2],
		[Item::WOODEN_PLANKS, 3],
		[Item::BRICKS, 0],

		[Item::DIRT, 0],
		[Item::GRASS, 0],
		[Item::CLAY_BLOCK, 0],
		[Item::SANDSTONE, 0],
		[Item::SANDSTONE, 1],
		[Item::SANDSTONE, 2],
		[Item::SAND, 0],
		[Item::GRAVEL, 0],
		[Item::TRUNK, 0],
		[Item::TRUNK, 1],
		[Item::TRUNK, 2],
		[Item::TRUNK, 3],
		[Item::NETHER_BRICKS, 0],
		[Item::NETHERRACK, 0],
		[Item::BEDROCK, 0],
		[Item::COBBLESTONE_STAIRS, 0],
		[Item::OAK_WOODEN_STAIRS, 0],
		[Item::SPRUCE_WOODEN_STAIRS, 0],
		[Item::BIRCH_WOODEN_STAIRS, 0],
		[Item::JUNGLE_WOODEN_STAIRS, 0],
		[Item::BRICK_STAIRS, 0],
		[Item::SANDSTONE_STAIRS, 0],
		[Item::STONE_BRICK_STAIRS, 0],
		[Item::NETHER_BRICKS_STAIRS, 0],
		[Item::QUARTZ_STAIRS, 0],
		[Item::SLAB, 0],
		[Item::SLAB, 1],
		[Item::WOODEN_SLAB, 0],
		[Item::WOODEN_SLAB, 1],
		[Item::WOODEN_SLAB, 2],
		[Item::WOODEN_SLAB, 3],
		[Item::SLAB, 3],
		[Item::SLAB, 4],
		[Item::SLAB, 5],
		[Item::SLAB, 6],
		[Item::QUARTZ_BLOCK, 0],
		[Item::QUARTZ_BLOCK, 1],
		[Item::QUARTZ_BLOCK, 2],
		[Item::COAL_ORE, 0],
		[Item::IRON_ORE, 0],
		[Item::GOLD_ORE, 0],
		[Item::DIAMOND_ORE, 0],
		[Item::LAPIS_ORE, 0],
		[Item::REDSTONE_ORE, 0],
		[Item::OBSIDIAN, 0],
		[Item::ICE, 0],
		[Item::SNOW_BLOCK, 0],

		//Decoration
		[Item::COBBLESTONE_WALL, 0],
		[Item::COBBLESTONE_WALL, 1],
		[Item::GOLD_BLOCK, 0],
		[Item::IRON_BLOCK, 0],
		[Item::DIAMOND_BLOCK, 0],
		[Item::LAPIS_BLOCK, 0],
		[Item::COAL_BLOCK, 0],
		[Item::SNOW_LAYER, 0],
		[Item::GLASS, 0],
		[Item::GLOWSTONE_BLOCK, 0],
		[Item::NETHER_REACTOR, 0],
		[Item::WOOL, 0],
		[Item::WOOL, 7],
		[Item::WOOL, 6],
		[Item::WOOL, 5],
		[Item::WOOL, 4],
		[Item::WOOL, 3],
		[Item::WOOL, 2],
		[Item::WOOL, 1],
		[Item::WOOL, 15],
		[Item::WOOL, 14],
		[Item::WOOL, 13],
		[Item::WOOL, 12],
		[Item::WOOL, 11],
		[Item::WOOL, 10],
		[Item::WOOL, 9],
		[Item::WOOL, 8],
		[Item::LADDER, 0],
		[Item::SPONGE, 0],
		[Item::GLASS_PANE, 0],
		[Item::WOODEN_DOOR, 0],
		[Item::TRAPDOOR, 0],
		[Item::FENCE, 0],
		[Item::FENCE_GATE, 0],
		[Item::IRON_BARS, 0],
		[Item::BED, 0],
		[Item::BOOKSHELF, 0],
		[Item::PAINTING, 0],
		[Item::WORKBENCH, 0],
		[Item::STONECUTTER, 0],
		[Item::CHEST, 0],
		[Item::FURNACE, 0],
		[Item::DANDELION, 0],
		[Item::CYAN_FLOWER, 0],
		[Item::BROWN_MUSHROOM, 0],
		[Item::RED_MUSHROOM, 0],
		[Item::CACTUS, 0],
		[Item::MELON_BLOCK, 0],
		[Item::PUMPKIN, 0],
		[Item::LIT_PUMPKIN, 0],
		[Item::COBWEB, 0],
		[Item::HAY_BALE, 0],
		[Item::TALL_GRASS, 1],
		[Item::TALL_GRASS, 2],
		[Item::DEAD_BUSH, 0],
		[Item::SAPLING, 0],
		[Item::SAPLING, 1],
		[Item::SAPLING, 2],
		[Item::SAPLING, 3],
		[Item::LEAVES, 0],
		[Item::LEAVES, 1],
		[Item::LEAVES, 2],
		[Item::LEAVES, 3],
		[Item::CAKE, 0],
		[Item::SIGN, 0],
		[Item::CARPET, 0],
		[Item::CARPET, 7],
		[Item::CARPET, 6],
		[Item::CARPET, 5],
		[Item::CARPET, 4],
		[Item::CARPET, 3],
		[Item::CARPET, 2],
		[Item::CARPET, 1],
		[Item::CARPET, 15],
		[Item::CARPET, 14],
		[Item::CARPET, 13],
		[Item::CARPET, 12],
		[Item::CARPET, 11],
		[Item::CARPET, 10],
		[Item::CARPET, 9],
		[Item::CARPET, 8],

		//Tools
		//[Item::RAILS, 0],
		//[Item::POWERED_RAILS, 0],
		[Item::TORCH, 0],
		[Item::BUCKET, 0],
		[Item::BUCKET, 8],
		[Item::BUCKET, 10],
		[Item::TNT, 0],
		[Item::IRON_HOE, 0],
		[Item::IRON_SWORD, 0],
		[Item::BOW, 0],
		[Item::SHEARS, 0],
		[Item::FLINT_AND_STEEL, 0],
		[Item::CLOCK, 0],
		[Item::COMPASS, 0],
		[Item::MINECART, 0],
		[Item::SPAWN_EGG, MOB_CHICKEN],
		[Item::SPAWN_EGG, MOB_COW],
		[Item::SPAWN_EGG, MOB_PIG],
		[Item::SPAWN_EGG, MOB_SHEEP],

		//Seeds
		[Item::SUGARCANE, 0],
		[Item::WHEAT, 0],
		[Item::SEEDS, 0],
		[Item::MELON_SEEDS, 0],
		[Item::PUMPKIN_SEEDS, 0],
		[Item::CARROT, 0],
		[Item::POTATO, 0],
		[Item::BEETROOT_SEEDS, 0],
		[Item::EGG, 0],
		[Item::DYE, 0],
		[Item::DYE, 7],
		[Item::DYE, 6],
		[Item::DYE, 5],
		[Item::DYE, 4],
		[Item::DYE, 3],
		[Item::DYE, 2],
		[Item::DYE, 1],
		[Item::DYE, 15],
		[Item::DYE, 14],
		[Item::DYE, 13],
		[Item::DYE, 12],
		[Item::DYE, 11],
		[Item::DYE, 10],
		[Item::DYE, 9],
		[Item::DYE, 8],

	);

	public static function fromString($str, $multiple = false){
		if($multiple === true){
			$blocks = array();
			foreach(explode(",", $str) as $b){
				$blocks[] = BlockAPI::fromString($b, false);
			}

			return $blocks;
		} else{
			$b = explode(":", str_replace(" ", "_", trim($str)));
			if(!isset($b[1])){
				$meta = 0;
			} else{
				$meta = ((int) $b[1]) & 0xFFFF;
			}

			if(defined(strtoupper($b[0]))){
				$item = Item::get(constant(strtoupper($b[0])), $meta);
				if($item->getID() === AIR and strtoupper($b[0]) !== "AIR"){
					$item = Item::get(((int) $b[0]) & 0xFFFF, $meta);
				}
			} else{
				$item = Item::get(((int) $b[0]) & 0xFFFF, $meta);
			}

			return $item;
		}
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
				$player = Player::get($params[0]);
				$item = BlockAPI::fromString($params[1]);

				if(!isset($params[2])){
					$item->setCount($item->getMaxStackSize());
				} else{
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
					$player->addItem($item);
					$output .= "Giving " . $item->getCount() . " of " . $item->getName() . " (" . $item->getID() . ":" . $item->getMetadata() . ") to " . $player->getUsername() . "\n";
				} else{
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

	public function playerBlockBreak(Player $player, Math\Math\Vector3 $vector){

		$target = $player->level->getBlock($vector);
		$item = $player->getSlot($player->slot);

		if($this->server->api->dhandle("player.block.touch", array("type" => "break", "player" => $player, "target" => $target, "item" => $item)) === false){
			if($this->server->api->dhandle("player.block.break.bypass", array("player" => $player, "target" => $target, "item" => $item)) !== true){
				return $this->cancelAction($target, $player, false);
			}
		}

		if((!$target->isBreakable($item, $player) and $this->server->api->dhandle("player.block.break.invalid", array("player" => $player, "target" => $target, "item" => $item)) !== true) or ($player->gamemode & 0x02) === 0x02 or (($player->lastBreak - $player->getLag() / 1000) + $target->getBreakTime($item, $player) - 0.2) >= microtime(true)){
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
				$player->setSlot($player->slot, new Item(AIR, 0, 0));
			}
		} else{
			return $this->cancelAction($target, $player, false);
		}


		if(($player->gamemode & 0x01) === 0x00 and count($drops) > 0){
			foreach($drops as $drop){
				echo "I dropped something\n";
				//$this->server->api->entity->drop(new Position($target->x + 0.5, $target->y, $target->z + 0.5, $target->level), Item::get($drop[0] & 0xFFFF, $drop[1] & 0xFFFF, $drop[2]));
			}
		}

		return false;
	}

	public function playerBlockAction(Player $player, Math\Math\Vector3 $vector, $face, $fx, $fy, $fz){
		if($face < 0 or $face > 5){
			return false;
		}

		$target = $player->level->getBlock($vector);
		$block = $target->getSide($face);
		if(($player->getGamemode() & 0x01) === 0){
			$item = $player->getSlot($player->slot);
		} else{
			$item = Item::get(BlockAPI::$creative[$player->slot][0], BlockAPI::$creative[$player->slot][1], 1);
		}

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
		$this->blockUpdate($target, Level::BLOCK_UPDATE_TOUCH);

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
			if($item->getCount() <= 0){
				$player->setSlot($player->slot, Item::get(AIR, 0, 0));
			}

			return false;
		}

		if($item->isPlaceable()){
			$hand = $item->getBlock();
			$hand->position($block);
		} elseif($block->getID() === FIRE){
			$player->level->setBlock($block, new Block\AirBlock(), true, false, true);

			return false;
		} else{
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

		//Implement using Bounding Boxes
		/*if($hand->isSolid === true and $player->inBlock($block)){
			return $this->cancelAction($block, $player, false); //Entity in block
		}*/

		if($this->server->api->dhandle("player.block.place", array("player" => $player, "block" => $block, "target" => $target, "item" => $item)) === false){
			return $this->cancelAction($block, $player);
		} elseif($hand->place($item, $player, $block, $target, $face, $fx, $fy, $fz) === false){
			return $this->cancelAction($block, $player, false);
		}
		if($hand->getID() === SIGN_POST or $hand->getID() === WALL_SIGN){
			new Sign($player->level, new Compound(false, array(
				"id" => new String("id", Tile::Sign),
				"x" => new Int("x", $block->x),
				"y" => new Int("y", $block->y),
				"z" => new Int("z", $block->z),
				"Text1" => new String("Text1", ""),
				"Text2" => new String("Text2", ""),
				"Text3" => new String("Text3", ""),
				"Text4" => new String("Text4", ""),
				"creator" => new String("creator", $player->getUsername())
			)));
		}

		if(($player->getGamemode() & 0x01) === 0){
			$item->setCount($item->getCount() - 1);
			if($item->getCount() <= 0){
				$player->setSlot($player->slot, Item::get(AIR, 0, 0));
			}
		}

		return false;
	}

	public function blockUpdateAround(Position $pos, $type = Level::BLOCK_UPDATE_NORMAL, $delay = false){
		if($delay !== false){
			$this->scheduleBlockUpdate($pos->getSide(0), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(1), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(2), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(3), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(4), $delay, $type);
			$this->scheduleBlockUpdate($pos->getSide(5), $delay, $type);
		} else{
			$this->blockUpdate($pos->getSide(0), $type);
			$this->blockUpdate($pos->getSide(1), $type);
			$this->blockUpdate($pos->getSide(2), $type);
			$this->blockUpdate($pos->getSide(3), $type);
			$this->blockUpdate($pos->getSide(4), $type);
			$this->blockUpdate($pos->getSide(5), $type);
		}
	}

	public function blockUpdate(Position $pos, $type = Level::BLOCK_UPDATE_NORMAL){
		if(!($pos instanceof Block)){
			$block = $pos->level->getBlock($pos);
		} else{
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
