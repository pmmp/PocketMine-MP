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

use PocketMine\Level\Position as Position;
use PocketMine\Block\GenericBlock as GenericBlock;
use PocketMine\Item\Item as Item;
use PocketMine\ServerAPI as ServerAPI;
use PocketMine\Player as Player;
use PocketMine\Block\Block as Block;
use PocketMine\Tile\Sign as Sign;
use PocketMine\NBT\Tag\Compound as Compound;
use PocketMine\NBT\Tag\String as String;
use PocketMine\NBT\Tag\Int as Int;

class BlockAPI{
	private $server;
	private $scheduledUpdates = array();
	private $randomUpdates = array();
	public static $creative = array(
		//Building
		[Block\STONE, 0],
		[Block\COBBLESTONE, 0],
		[Block\STONE_BRICKS, 0],
		[Block\STONE_BRICKS, 1],
		[Block\STONE_BRICKS, 2],
		[Block\MOSS_STONE, 0],
		[Block\WOODEN_PLANKS, 0],
		[Block\WOODEN_PLANKS, 1],
		[Block\WOODEN_PLANKS, 2],
		[Block\WOODEN_PLANKS, 3],
		[Block\BRICKS, 0],

		[Block\DIRT, 0],
		[Block\GRASS, 0],
		[Block\CLAY_BLOCK, 0],
		[Block\SANDSTONE, 0],
		[Block\SANDSTONE, 1],
		[Block\SANDSTONE, 2],
		[Block\SAND, 0],
		[Block\GRAVEL, 0],
		[Block\TRUNK, 0],
		[Block\TRUNK, 1],
		[Block\TRUNK, 2],
		[Block\TRUNK, 3],
		[Block\NETHER_BRICKS, 0],
		[Block\NETHERRACK, 0],
		[Block\BEDROCK, 0],
		[Block\COBBLESTONE_STAIRS, 0],
		[Block\OAK_WOODEN_STAIRS, 0],
		[Block\SPRUCE_WOODEN_STAIRS, 0],
		[Block\BIRCH_WOODEN_STAIRS, 0],
		[Block\JUNGLE_WOODEN_STAIRS, 0],
		[Block\BRICK_STAIRS, 0],
		[Block\SANDSTONE_STAIRS, 0],
		[Block\STONE_BRICK_STAIRS, 0],
		[Block\NETHER_BRICKS_STAIRS, 0],
		[Block\QUARTZ_STAIRS, 0],
		[Block\SLAB, 0],
		[Block\SLAB, 1],
		[Block\WOODEN_SLAB, 0],
		[Block\WOODEN_SLAB, 1],
		[Block\WOODEN_SLAB, 2],
		[Block\WOODEN_SLAB, 3],
		[Block\SLAB, 3],
		[Block\SLAB, 4],
		[Block\SLAB, 5],
		[Block\SLAB, 6],
		[Block\QUARTZ_BLOCK, 0],
		[Block\QUARTZ_BLOCK, 1],
		[Block\QUARTZ_BLOCK, 2],
		[Block\COAL_ORE, 0],
		[Block\IRON_ORE, 0],
		[Block\GOLD_ORE, 0],
		[Block\DIAMOND_ORE, 0],
		[Block\LAPIS_ORE, 0],
		[Block\REDSTONE_ORE, 0],
		[Block\OBSIDIAN, 0],
		[Block\ICE, 0],
		[Block\SNOW_BLOCK, 0],

		//Decoration
		[Block\COBBLESTONE_WALL, 0],
		[Block\COBBLESTONE_WALL, 1],
		[Block\GOLD_BLOCK, 0],
		[Block\IRON_BLOCK, 0],
		[Block\DIAMOND_BLOCK, 0],
		[Block\LAPIS_BLOCK, 0],
		[Block\COAL_BLOCK, 0],
		[Block\SNOW_LAYER, 0],
		[Block\GLASS, 0],
		[Block\GLOWSTONE_BLOCK, 0],
		[Block\NETHER_REACTOR, 0],
		[Block\WOOL, 0],
		[Block\WOOL, 7],
		[Block\WOOL, 6],
		[Block\WOOL, 5],
		[Block\WOOL, 4],
		[Block\WOOL, 3],
		[Block\WOOL, 2],
		[Block\WOOL, 1],
		[Block\WOOL, 15],
		[Block\WOOL, 14],
		[Block\WOOL, 13],
		[Block\WOOL, 12],
		[Block\WOOL, 11],
		[Block\WOOL, 10],
		[Block\WOOL, 9],
		[Block\WOOL, 8],
		[Block\LADDER, 0],
		[Block\SPONGE, 0],
		[Block\GLASS_PANE, 0],
		[Block\WOODEN_DOOR, 0],
		[Block\TRAPDOOR, 0],
		[Block\FENCE, 0],
		[Block\FENCE_GATE, 0],
		[Block\IRON_BARS, 0],
		[Block\BED, 0],
		[Block\BOOKSHELF, 0],
		[Block\PAINTING, 0],
		[Block\WORKBENCH, 0],
		[Block\STONECUTTER, 0],
		[Block\CHEST, 0],
		[Block\FURNACE, 0],
		[Block\DANDELION, 0],
		[Block\CYAN_FLOWER, 0],
		[Block\BROWN_MUSHROOM, 0],
		[Block\RED_MUSHROOM, 0],
		[Block\CACTUS, 0],
		[Block\MELON_BLOCK, 0],
		[Block\PUMPKIN, 0],
		[Block\LIT_PUMPKIN, 0],
		[Block\COBWEB, 0],
		[Block\HAY_BALE, 0],
		[Block\TALL_GRASS, 1],
		[Block\TALL_GRASS, 2],
		[Block\DEAD_BUSH, 0],
		[Block\SAPLING, 0],
		[Block\SAPLING, 1],
		[Block\SAPLING, 2],
		[Block\SAPLING, 3],
		[Block\LEAVES, 0],
		[Block\LEAVES, 1],
		[Block\LEAVES, 2],
		[Block\LEAVES, 3],
		[Block\CAKE, 0],
		[Block\SIGN, 0],
		[Block\CARPET, 0],
		[Block\CARPET, 7],
		[Block\CARPET, 6],
		[Block\CARPET, 5],
		[Block\CARPET, 4],
		[Block\CARPET, 3],
		[Block\CARPET, 2],
		[Block\CARPET, 1],
		[Block\CARPET, 15],
		[Block\CARPET, 14],
		[Block\CARPET, 13],
		[Block\CARPET, 12],
		[Block\CARPET, 11],
		[Block\CARPET, 10],
		[Block\CARPET, 9],
		[Block\CARPET, 8],

		//Tools
		//array(RAILS, 0),
		//array(POWERED_RAILS, 0),
		[Block\TORCH, 0],
		[Block\BUCKET, 0],
		[Block\BUCKET, 8],
		[Block\BUCKET, 10],
		[Block\TNT, 0],
		[Block\IRON_HOE, 0],
		[Block\IRON_SWORD, 0],
		[Block\BOW, 0],
		[Block\SHEARS, 0],
		[Block\FLINT_AND_STEEL, 0],
		[Block\CLOCK, 0],
		[Block\COMPASS, 0],
		[Block\MINECART, 0],
		array(SPAWN_EGG, MOB_CHICKEN),
		array(SPAWN_EGG, MOB_COW),
		array(SPAWN_EGG, MOB_PIG),
		array(SPAWN_EGG, MOB_SHEEP),

		//Seeds
		[Block\SUGARCANE, 0],
		[Block\WHEAT, 0],
		[Block\SEEDS, 0],
		[Block\MELON_SEEDS, 0],
		[Block\PUMPKIN_SEEDS, 0],
		[Block\CARROT, 0],
		[Block\POTATO, 0],
		[Block\BEETROOT_SEEDS, 0],
		[Block\EGG, 0],
		[Block\DYE, 0],
		[Block\DYE, 7],
		[Block\DYE, 6],
		[Block\DYE, 5],
		[Block\DYE, 4],
		[Block\DYE, 3],
		[Block\DYE, 2],
		[Block\DYE, 1],
		[Block\DYE, 15],
		[Block\DYE, 14],
		[Block\DYE, 13],
		[Block\DYE, 12],
		[Block\DYE, 11],
		[Block\DYE, 10],
		[Block\DYE, 9],
		[Block\DYE, 8],

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
		$pk = new Network\Protocol\UpdateBlockPacket;
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

	public function blockUpdateAround(Position $pos, $type = BLOCK_UPDATE_NORMAL, $delay = false){
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

	public function blockUpdate(Position $pos, $type = BLOCK_UPDATE_NORMAL){
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
		if($level === BLOCK_UPDATE_NORMAL){
			$this->blockUpdateAround($block, $level);
		}

		return $level;
	}

	public function scheduleBlockUpdate(Position $pos, $delay, $type = BLOCK_UPDATE_SCHEDULED){
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
