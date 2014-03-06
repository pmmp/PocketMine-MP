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
		[ItemItem::STONE, 0],
		[ItemItem::COBBLESTONE, 0],
		[ItemItem::STONE_BRICKS, 0],
		[ItemItem::STONE_BRICKS, 1],
		[ItemItem::STONE_BRICKS, 2],
		[ItemItem::MOSS_STONE, 0],
		[ItemItem::WOODEN_PLANKS, 0],
		[ItemItem::WOODEN_PLANKS, 1],
		[ItemItem::WOODEN_PLANKS, 2],
		[ItemItem::WOODEN_PLANKS, 3],
		[ItemItem::BRICKS, 0],

		[ItemItem::DIRT, 0],
		[ItemItem::GRASS, 0],
		[ItemItem::CLAY_BLOCK, 0],
		[ItemItem::SANDSTONE, 0],
		[ItemItem::SANDSTONE, 1],
		[ItemItem::SANDSTONE, 2],
		[ItemItem::SAND, 0],
		[ItemItem::GRAVEL, 0],
		[ItemItem::TRUNK, 0],
		[ItemItem::TRUNK, 1],
		[ItemItem::TRUNK, 2],
		[ItemItem::TRUNK, 3],
		[ItemItem::NETHER_BRICKS, 0],
		[ItemItem::NETHERRACK, 0],
		[ItemItem::BEDROCK, 0],
		[ItemItem::COBBLESTONE_STAIRS, 0],
		[ItemItem::OAK_WOODEN_STAIRS, 0],
		[ItemItem::SPRUCE_WOODEN_STAIRS, 0],
		[ItemItem::BIRCH_WOODEN_STAIRS, 0],
		[ItemItem::JUNGLE_WOODEN_STAIRS, 0],
		[ItemItem::BRICK_STAIRS, 0],
		[ItemItem::SANDSTONE_STAIRS, 0],
		[ItemItem::STONE_BRICK_STAIRS, 0],
		[ItemItem::NETHER_BRICKS_STAIRS, 0],
		[ItemItem::QUARTZ_STAIRS, 0],
		[ItemItem::SLAB, 0],
		[ItemItem::SLAB, 1],
		[ItemItem::WOODEN_SLAB, 0],
		[ItemItem::WOODEN_SLAB, 1],
		[ItemItem::WOODEN_SLAB, 2],
		[ItemItem::WOODEN_SLAB, 3],
		[ItemItem::SLAB, 3],
		[ItemItem::SLAB, 4],
		[ItemItem::SLAB, 5],
		[ItemItem::SLAB, 6],
		[ItemItem::QUARTZ_BLOCK, 0],
		[ItemItem::QUARTZ_BLOCK, 1],
		[ItemItem::QUARTZ_BLOCK, 2],
		[ItemItem::COAL_ORE, 0],
		[ItemItem::IRON_ORE, 0],
		[ItemItem::GOLD_ORE, 0],
		[ItemItem::DIAMOND_ORE, 0],
		[ItemItem::LAPIS_ORE, 0],
		[ItemItem::REDSTONE_ORE, 0],
		[ItemItem::OBSIDIAN, 0],
		[ItemItem::ICE, 0],
		[ItemItem::SNOW_BLOCK, 0],

		//Decoration
		[ItemItem::COBBLESTONE_WALL, 0],
		[ItemItem::COBBLESTONE_WALL, 1],
		[ItemItem::GOLD_BLOCK, 0],
		[ItemItem::IRON_BLOCK, 0],
		[ItemItem::DIAMOND_BLOCK, 0],
		[ItemItem::LAPIS_BLOCK, 0],
		[ItemItem::COAL_BLOCK, 0],
		[ItemItem::SNOW_LAYER, 0],
		[ItemItem::GLASS, 0],
		[ItemItem::GLOWSTONE_BLOCK, 0],
		[ItemItem::NETHER_REACTOR, 0],
		[ItemItem::WOOL, 0],
		[ItemItem::WOOL, 7],
		[ItemItem::WOOL, 6],
		[ItemItem::WOOL, 5],
		[ItemItem::WOOL, 4],
		[ItemItem::WOOL, 3],
		[ItemItem::WOOL, 2],
		[ItemItem::WOOL, 1],
		[ItemItem::WOOL, 15],
		[ItemItem::WOOL, 14],
		[ItemItem::WOOL, 13],
		[ItemItem::WOOL, 12],
		[ItemItem::WOOL, 11],
		[ItemItem::WOOL, 10],
		[ItemItem::WOOL, 9],
		[ItemItem::WOOL, 8],
		[ItemItem::LADDER, 0],
		[ItemItem::SPONGE, 0],
		[ItemItem::GLASS_PANE, 0],
		[ItemItem::WOODEN_DOOR, 0],
		[ItemItem::TRAPDOOR, 0],
		[ItemItem::FENCE, 0],
		[ItemItem::FENCE_GATE, 0],
		[ItemItem::IRON_BARS, 0],
		[ItemItem::BED, 0],
		[ItemItem::BOOKSHELF, 0],
		[ItemItem::PAINTING, 0],
		[ItemItem::WORKBENCH, 0],
		[ItemItem::STONECUTTER, 0],
		[ItemItem::CHEST, 0],
		[ItemItem::FURNACE, 0],
		[ItemItem::DANDELION, 0],
		[ItemItem::CYAN_FLOWER, 0],
		[ItemItem::BROWN_MUSHROOM, 0],
		[ItemItem::RED_MUSHROOM, 0],
		[ItemItem::CACTUS, 0],
		[ItemItem::MELON_BLOCK, 0],
		[ItemItem::PUMPKIN, 0],
		[ItemItem::LIT_PUMPKIN, 0],
		[ItemItem::COBWEB, 0],
		[ItemItem::HAY_BALE, 0],
		[ItemItem::TALL_GRASS, 1],
		[ItemItem::TALL_GRASS, 2],
		[ItemItem::DEAD_BUSH, 0],
		[ItemItem::SAPLING, 0],
		[ItemItem::SAPLING, 1],
		[ItemItem::SAPLING, 2],
		[ItemItem::SAPLING, 3],
		[ItemItem::LEAVES, 0],
		[ItemItem::LEAVES, 1],
		[ItemItem::LEAVES, 2],
		[ItemItem::LEAVES, 3],
		[ItemItem::CAKE, 0],
		[ItemItem::SIGN, 0],
		[ItemItem::CARPET, 0],
		[ItemItem::CARPET, 7],
		[ItemItem::CARPET, 6],
		[ItemItem::CARPET, 5],
		[ItemItem::CARPET, 4],
		[ItemItem::CARPET, 3],
		[ItemItem::CARPET, 2],
		[ItemItem::CARPET, 1],
		[ItemItem::CARPET, 15],
		[ItemItem::CARPET, 14],
		[ItemItem::CARPET, 13],
		[ItemItem::CARPET, 12],
		[ItemItem::CARPET, 11],
		[ItemItem::CARPET, 10],
		[ItemItem::CARPET, 9],
		[ItemItem::CARPET, 8],

		//Tools
		//array(RAILS, 0),
		//array(POWERED_RAILS, 0),
		[ItemItem::TORCH, 0],
		[ItemItem::BUCKET, 0],
		[ItemItem::BUCKET, 8],
		[ItemItem::BUCKET, 10],
		[ItemItem::TNT, 0],
		[ItemItem::IRON_HOE, 0],
		[ItemItem::IRON_SWORD, 0],
		[ItemItem::BOW, 0],
		[ItemItem::SHEARS, 0],
		[ItemItem::FLINT_AND_STEEL, 0],
		[ItemItem::CLOCK, 0],
		[ItemItem::COMPASS, 0],
		[ItemItem::MINECART, 0],
		array(SPAWN_EGG, MOB_CHICKEN),
		array(SPAWN_EGG, MOB_COW),
		array(SPAWN_EGG, MOB_PIG),
		array(SPAWN_EGG, MOB_SHEEP),

		//Seeds
		[ItemItem::SUGARCANE, 0],
		[ItemItem::WHEAT, 0],
		[ItemItem::SEEDS, 0],
		[ItemItem::MELON_SEEDS, 0],
		[ItemItem::PUMPKIN_SEEDS, 0],
		[ItemItem::CARROT, 0],
		[ItemItem::POTATO, 0],
		[ItemItem::BEETROOT_SEEDS, 0],
		[ItemItem::EGG, 0],
		[ItemItem::DYE, 0],
		[ItemItem::DYE, 7],
		[ItemItem::DYE, 6],
		[ItemItem::DYE, 5],
		[ItemItem::DYE, 4],
		[ItemItem::DYE, 3],
		[ItemItem::DYE, 2],
		[ItemItem::DYE, 1],
		[ItemItem::DYE, 15],
		[ItemItem::DYE, 14],
		[ItemItem::DYE, 13],
		[ItemItem::DYE, 12],
		[ItemItem::DYE, 11],
		[ItemItem::DYE, 10],
		[ItemItem::DYE, 9],
		[ItemItem::DYE, 8],

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
