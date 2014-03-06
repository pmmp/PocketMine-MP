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

use PocketMine\Block\Block as Block;
use PocketMine\Block\GenericBlock as GenericBlock;
use PocketMine\Item\Item as Item;
use PocketMine\Level\Position as Position;
use PocketMine\NBT\Tag\Compound as Compound;
use PocketMine\NBT\Tag\Int as Int;
use PocketMine\NBT\Tag\String as String;
use PocketMine\Network\Protocol\UpdateBlockPacket as UpdateBlockPacket;
use PocketMine\Tile\Sign as Sign;

class BlockAPI{
	private $server;
	private $scheduledUpdates = array();
	private $randomUpdates = array();
	public static $creative = array(
		//Building
		[Item\Item::STONE, 0],
		[Item\Item::COBBLESTONE, 0],
		[Item\Item::STONE_BRICKS, 0],
		[Item\Item::STONE_BRICKS, 1],
		[Item\Item::STONE_BRICKS, 2],
		[Item\Item::MOSS_STONE, 0],
		[Item\Item::WOODEN_PLANKS, 0],
		[Item\Item::WOODEN_PLANKS, 1],
		[Item\Item::WOODEN_PLANKS, 2],
		[Item\Item::WOODEN_PLANKS, 3],
		[Item\Item::BRICKS, 0],

		[Item\Item::DIRT, 0],
		[Item\Item::GRASS, 0],
		[Item\Item::CLAY_BLOCK, 0],
		[Item\Item::SANDSTONE, 0],
		[Item\Item::SANDSTONE, 1],
		[Item\Item::SANDSTONE, 2],
		[Item\Item::SAND, 0],
		[Item\Item::GRAVEL, 0],
		[Item\Item::TRUNK, 0],
		[Item\Item::TRUNK, 1],
		[Item\Item::TRUNK, 2],
		[Item\Item::TRUNK, 3],
		[Item\Item::NETHER_BRICKS, 0],
		[Item\Item::NETHERRACK, 0],
		[Item\Item::BEDROCK, 0],
		[Item\Item::COBBLESTONE_STAIRS, 0],
		[Item\Item::OAK_WOODEN_STAIRS, 0],
		[Item\Item::SPRUCE_WOODEN_STAIRS, 0],
		[Item\Item::BIRCH_WOODEN_STAIRS, 0],
		[Item\Item::JUNGLE_WOODEN_STAIRS, 0],
		[Item\Item::BRICK_STAIRS, 0],
		[Item\Item::SANDSTONE_STAIRS, 0],
		[Item\Item::STONE_BRICK_STAIRS, 0],
		[Item\Item::NETHER_BRICKS_STAIRS, 0],
		[Item\Item::QUARTZ_STAIRS, 0],
		[Item\Item::SLAB, 0],
		[Item\Item::SLAB, 1],
		[Item\Item::WOODEN_SLAB, 0],
		[Item\Item::WOODEN_SLAB, 1],
		[Item\Item::WOODEN_SLAB, 2],
		[Item\Item::WOODEN_SLAB, 3],
		[Item\Item::SLAB, 3],
		[Item\Item::SLAB, 4],
		[Item\Item::SLAB, 5],
		[Item\Item::SLAB, 6],
		[Item\Item::QUARTZ_BLOCK, 0],
		[Item\Item::QUARTZ_BLOCK, 1],
		[Item\Item::QUARTZ_BLOCK, 2],
		[Item\Item::COAL_ORE, 0],
		[Item\Item::IRON_ORE, 0],
		[Item\Item::GOLD_ORE, 0],
		[Item\Item::DIAMOND_ORE, 0],
		[Item\Item::LAPIS_ORE, 0],
		[Item\Item::REDSTONE_ORE, 0],
		[Item\Item::OBSIDIAN, 0],
		[Item\Item::ICE, 0],
		[Item\Item::SNOW_BLOCK, 0],

		//Decoration
		[Item\Item::COBBLESTONE_WALL, 0],
		[Item\Item::COBBLESTONE_WALL, 1],
		[Item\Item::GOLD_BLOCK, 0],
		[Item\Item::IRON_BLOCK, 0],
		[Item\Item::DIAMOND_BLOCK, 0],
		[Item\Item::LAPIS_BLOCK, 0],
		[Item\Item::COAL_BLOCK, 0],
		[Item\Item::SNOW_LAYER, 0],
		[Item\Item::GLASS, 0],
		[Item\Item::GLOWSTONE_BLOCK, 0],
		[Item\Item::NETHER_REACTOR, 0],
		[Item\Item::WOOL, 0],
		[Item\Item::WOOL, 7],
		[Item\Item::WOOL, 6],
		[Item\Item::WOOL, 5],
		[Item\Item::WOOL, 4],
		[Item\Item::WOOL, 3],
		[Item\Item::WOOL, 2],
		[Item\Item::WOOL, 1],
		[Item\Item::WOOL, 15],
		[Item\Item::WOOL, 14],
		[Item\Item::WOOL, 13],
		[Item\Item::WOOL, 12],
		[Item\Item::WOOL, 11],
		[Item\Item::WOOL, 10],
		[Item\Item::WOOL, 9],
		[Item\Item::WOOL, 8],
		[Item\Item::LADDER, 0],
		[Item\Item::SPONGE, 0],
		[Item\Item::GLASS_PANE, 0],
		[Item\Item::WOODEN_DOOR, 0],
		[Item\Item::TRAPDOOR, 0],
		[Item\Item::FENCE, 0],
		[Item\Item::FENCE_GATE, 0],
		[Item\Item::IRON_BARS, 0],
		[Item\Item::BED, 0],
		[Item\Item::BOOKSHELF, 0],
		[Item\Item::PAINTING, 0],
		[Item\Item::WORKBENCH, 0],
		[Item\Item::STONECUTTER, 0],
		[Item\Item::CHEST, 0],
		[Item\Item::FURNACE, 0],
		[Item\Item::DANDELION, 0],
		[Item\Item::CYAN_FLOWER, 0],
		[Item\Item::BROWN_MUSHROOM, 0],
		[Item\Item::RED_MUSHROOM, 0],
		[Item\Item::CACTUS, 0],
		[Item\Item::MELON_BLOCK, 0],
		[Item\Item::PUMPKIN, 0],
		[Item\Item::LIT_PUMPKIN, 0],
		[Item\Item::COBWEB, 0],
		[Item\Item::HAY_BALE, 0],
		[Item\Item::TALL_GRASS, 1],
		[Item\Item::TALL_GRASS, 2],
		[Item\Item::DEAD_BUSH, 0],
		[Item\Item::SAPLING, 0],
		[Item\Item::SAPLING, 1],
		[Item\Item::SAPLING, 2],
		[Item\Item::SAPLING, 3],
		[Item\Item::LEAVES, 0],
		[Item\Item::LEAVES, 1],
		[Item\Item::LEAVES, 2],
		[Item\Item::LEAVES, 3],
		[Item\Item::CAKE, 0],
		[Item\Item::SIGN, 0],
		[Item\Item::CARPET, 0],
		[Item\Item::CARPET, 7],
		[Item\Item::CARPET, 6],
		[Item\Item::CARPET, 5],
		[Item\Item::CARPET, 4],
		[Item\Item::CARPET, 3],
		[Item\Item::CARPET, 2],
		[Item\Item::CARPET, 1],
		[Item\Item::CARPET, 15],
		[Item\Item::CARPET, 14],
		[Item\Item::CARPET, 13],
		[Item\Item::CARPET, 12],
		[Item\Item::CARPET, 11],
		[Item\Item::CARPET, 10],
		[Item\Item::CARPET, 9],
		[Item\Item::CARPET, 8],

		//Tools
		//array(RAILS, 0),
		//array(POWERED_RAILS, 0),
		[Item\Item::TORCH, 0],
		[Item\Item::BUCKET, 0],
		[Item\Item::BUCKET, 8],
		[Item\Item::BUCKET, 10],
		[Item\Item::TNT, 0],
		[Item\Item::IRON_HOE, 0],
		[Item\Item::IRON_SWORD, 0],
		[Item\Item::BOW, 0],
		[Item\Item::SHEARS, 0],
		[Item\Item::FLINT_AND_STEEL, 0],
		[Item\Item::CLOCK, 0],
		[Item\Item::COMPASS, 0],
		[Item\Item::MINECART, 0],
		array(SPAWN_EGG, MOB_CHICKEN),
		array(SPAWN_EGG, MOB_COW),
		array(SPAWN_EGG, MOB_PIG),
		array(SPAWN_EGG, MOB_SHEEP),

		//Seeds
		[Item\Item::SUGARCANE, 0],
		[Item\Item::WHEAT, 0],
		[Item\Item::SEEDS, 0],
		[Item\Item::MELON_SEEDS, 0],
		[Item\Item::PUMPKIN_SEEDS, 0],
		[Item\Item::CARROT, 0],
		[Item\Item::POTATO, 0],
		[Item\Item::BEETROOT_SEEDS, 0],
		[Item\Item::EGG, 0],
		[Item\Item::DYE, 0],
		[Item\Item::DYE, 7],
		[Item\Item::DYE, 6],
		[Item\Item::DYE, 5],
		[Item\Item::DYE, 4],
		[Item\Item::DYE, 3],
		[Item\Item::DYE, 2],
		[Item\Item::DYE, 1],
		[Item\Item::DYE, 15],
		[Item\Item::DYE, 14],
		[Item\Item::DYE, 13],
		[Item\Item::DYE, 12],
		[Item\Item::DYE, 11],
		[Item\Item::DYE, 10],
		[Item\Item::DYE, 9],
		[Item\Item::DYE, 8],

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
