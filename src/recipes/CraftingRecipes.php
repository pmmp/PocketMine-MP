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

class CraftingRecipes{
	private static $small = array(//Probably means craftable on crafting bench and in inventory. Name it better!
		//Building
		"CLAY:?x4=>CLAY_BLOCK:0x1",
		"WOODEN_PLANKS:?x4=>WORKBENCH:0x1",
		"GLOWSTONE_DUST:?x4=>GLOWSTONE_BLOCK:0x1",
		"PUMPKIN:?x1,TORCH:?x1=>LIT_PUMPKIN:0x1",
		"SNOWBALL:?x4=>SNOW_BLOCK:0x1",		
		"WOODEN_PLANKS:?x2=>STICK:0x4",
		"COBBLESTONE:?x4=>STONECUTTER:0x1",		
		"WOOD:0x1=>WOODEN_PLANKS:0x4",		
		"WOOD:1x1=>WOODEN_PLANKS:1x4",		
		"WOOD:2x1=>WOODEN_PLANKS:2x4",		
		"WOOD:3x1=>WOODEN_PLANKS:3x4",
		"WOOL:0x1,DYE:0x1=>WOOL:15x1",
		"WOOL:0x1,DYE:1x1=>WOOL:14x1",
		"WOOL:0x1,DYE:2x1=>WOOL:13x1",
		"WOOL:0x1,DYE:3x1=>WOOL:12x1",
		"WOOL:0x1,DYE:4x1=>WOOL:11x1",
		"WOOL:0x1,DYE:5x1=>WOOL:10x1",
		"WOOL:0x1,DYE:6x1=>WOOL:9x1",
		"WOOL:0x1,DYE:7x1=>WOOL:8x1",
		"WOOL:0x1,DYE:8x1=>WOOL:7x1",
		"WOOL:0x1,DYE:9x1=>WOOL:6x1",
		"WOOL:0x1,DYE:10x1=>WOOL:5x1",
		"WOOL:0x1,DYE:11x1=>WOOL:4x1",
		"WOOL:0x1,DYE:12x1=>WOOL:3x1",
		"WOOL:0x1,DYE:13x1=>WOOL:2x1",
		"WOOL:0x1,DYE:14x1=>WOOL:1x1",
		"STRING:?x4=>WOOL:0x1",
		
		//Tools
		"IRON_INGOT:?x1,FLINT:?x1=>FLINT_STEEL:0x1",
		"IRON_INGOT:?x2=>SHEARS:0x1",		
		"COAL:0x1,STICK:?x1=>TORCH:0x4",
		"COAL:1x1,STICK:?x1=>TORCH:0x4",
		
		//Food & protection		
		"MELON_SLICE:?x1=>MELON_SEEDS:0x1",
		"PUMPKIN:?x1=>PUMPKIN_SEEDS:0x4",		
		"PUMPKIN:?x1,EGG:?x1,SUGAR:?x1=>PUMPKIN_PIE:0x1",
		"BROWN_MUSHROOM:?x1,RED_MUSHROOM:?x1,BOWL:?x1=>MUSHROOM_STEW:0x1",
		"SUGARCANE:?x1=>SUGAR:0x1",
		"MELON_SLICE:?x1=>MELON_SEEDS:0x1",
		"HAY_BALE:?x1=>WHEAT:0x9",
		
		//Items
		"DIAMOND_BLOCK:?x1=>DIAMOND:0x9",
		"GOLD_BLOCK:?x1=>GOLD_INGOT:0x9",
		"IRON_BLOCK:?x1=>IRON_INGOT:0x9",
		"LAPIS_BLOCK:?x1=>DYE:4x9",
		"DANDELION:?x1=>DYE:11x2",
		"BONE:?x1=>DYE:15x3",
		"DYE:0x1,DYE:14x1=>DYE:3x2",
		"DYE:0x1,DYE:1x1,DYE:11x1=>DYE:3x3",
		"DYE:1x1,DYE:15x1=>DYE:9x2",
		"DYE:1x1,DYE:11x1=>DYE:14x2",
		"DYE:2x1,DYE:15x1=>DYE:10x2",
		"DYE:4x1,DYE:15x1=>DYE:12x2",
		"DYE:2x1,DYE:4x1=>DYE:6x2",
		"DYE:1x1,DYE:4x1=>DYE:5x2",
		"DYE:1x1,DYE:4x1,DYE:15x1=>DYE:13x3",		
		"BEETROOT:?x1=>DYE:1x1",
		"DYE:15x1,DYE:1x2,DYE:4x1=>DYE:13x4",//
		"DYE:5x1,DYE:9x1=>DYE:13x2",//		
		"DYE:0x1,DYE:15x1=>DYE:8x2",//		
		"DYE:0x1,DYE:15x2=>DYE:7x3",//
		"DYE:0x1,DYE:8x1=>DYE:7x2",//
	);
	
	private static $big = array(//Probably means only craftable on crafting bench. Name it better!
		//Building
		"WOOL:?x3,WOODEN_PLANKS:?x3=>BED:0x1",
		"WOODEN_PLANKS:?x8=>CHEST:0x1",
		"STICK:?x6=>FENCE:0x2",
		"STICK:?x4,WOODEN_PLANKS:?x2=>FENCE_GATE:0x1",
		"COBBLESTONE:?x8=>FURNACE:0x1",
		"GLASS:?x6=>GLASS_PANE:0x16",
		"STICK:?x7=>LADDER:0x2",
		"DIAMOND:?x3,IRON_INGOT:?x6=>NETHER_REACTOR:0x1",
		"WOODEN_PLANKS:?x6=>TRAPDOOR:0x2",
		"WOODEN_PLANKS:?x6=>WOODEN_DOOR:0x1",
		"WOODEN_PLANKS:0x6=>WOODEN_STAIRS:0x4",
		"WOODEN_PLANKS:0x3=>WOOD_SLAB:0x6",
		"WOODEN_PLANKS:1x6=>SPRUCE_WOOD_STAIRS:0x4",
		"WOODEN_PLANKS:1x3=>WOOD_SLAB:1x6",
		"WOODEN_PLANKS:2x6=>BIRCH_WOOD_STAIRS:0x4",
		"WOODEN_PLANKS:2x3=>BIRCH_WOOD_SLAB:2x6",
		"WOODEN_PLANKS:3x6=>JUNGLE_WOOD_STAIRS:0x4",
		"WOODEN_PLANKS:3x3=>JUNGLE_WOOD_SLAB:3x6",
		
		//Tools
		"STICK:?x1,FEATHER:?x1,FLINT:?x1=>ARROW:0x4",
		"STICK:?x3,STRING:?x3=>BOW:0x1",
		"IRON_INGOT:?x3=>BUCKET:0x1",
		"GOLD_INGOT:?x4,REDSTONE_DUST:?x1=>CLOCK:0x1",
		"IRON_INGOT:?x4,REDSTONE_DUST:?x1=>COMPASS:0x1",
		"DIAMOND:?x3,STICK:?x2=>DIAMOND_AXE:0x1",
		"DIAMOND:?x2,STICK:?x2=>DIAMOND_HOE:0x1",
		"DIAMOND:?x3,STICK:?x2=>DIAMOND_PICKAXE:0x1",
		"DIAMOND:?x1,STICK:?x2=>DIAMOND_SHOVEL:0x1",
		"DIAMOND:?x2,STICK:?x1=>DIAMOND_SWORD:0x1",
		"GOLD_INGOT:?x3,STICK:?x2=>GOLD_AXE:0x1",
		"GOLD_INGOT:?x2,STICK:?x2=>GOLD_HOE:0x1",
		"GOLD_INGOT:?x3,STICK:?x2=>GOLD_PICKAXE:0x1",
		"GOLD_INGOT:?x1,STICK:?x2=>GOLD_SHOVEL:0x1",
		"GOLD_INGOT:?x2,STICK:?x1=>GOLD_SWORD:0x1",
		"IRON_INGOT:?x3,STICK:?x2=>IRON_AXE:0x1",
		"IRON_INGOT:?x2,STICK:?x2=>IRON_HOE:0x1",
		"IRON_INGOT:?x3,STICK:?x2=>IRON_PICKAXE:0x1",
		"IRON_INGOT:?x1,STICK:?x2=>IRON_SHOVEL:0x1",
		"IRON_INGOT:?x2,STICK:?x1=>IRON_SWORD:0x1",
		"COBBLESTONE:?x3,STICK:?x2=>STONE_AXE:0x1",
		"COBBLESTONE:?x2,STICK:?x2=>STONE_HOE:0x1",
		"COBBLESTONE:?x3,STICK:?x2=>STONE_PICKAXE:0x1",
		"COBBLESTONE:?x1,STICK:?x2=>STONE_SHOVEL:0x1",
		"COBBLESTONE:?x2,STICK:?x1=>STONE_SWORD:0x1",
		"SAND:?x4,GUNPOWDER:?x5=>TNT:0x1",
		"WOODEN_PLANKS:?x3,STICK:?x2=>WOODEN_AXE:0x1",
		"WOODEN_PLANKS:?x2,STICK:?x2=>WOODEN_HOE:0x1",
		"WOODEN_PLANKS:?x3,STICK:?x2=>WOODEN_PICKAXE:0x1",
		"WOODEN_PLANKS:?x1,STICK:?x2=>WOODEN_SHOVEL:0x1",
		"WOODEN_PLANKS:?x2,STICK:?x1=>WOODEN_SWORD:0x1",
		
		//Food & protection
		"BEETROOT:?x4,BOWL:?x1=>BEETROOT_SOUP:0x1",
		"WOODEN_PLANKS:?x3=>BOWL:0x1",
		"WHEAT:?x3=>BREAD:0x1",
		"WHEAT:?x3,BUCKET:1x3,EGG:?x1,SUGAR:?x2=>CAKE:0x1",
		"DIAMOND:?x4=>DIAMOND_BOOTS:0x1",
		"DIAMOND:?x8=>DIAMOND_CHESTPLATE:0x1",
		"DIAMOND:?x5=>DIAMOND_HELMET:0x1",
		"DIAMOND:?x7=>DIAMOND_LEGGINGS:0x1",
		"GOLD_INGOT:?x4=>GOLD_BOOTS:0x1",
		"GOLD_INGOT:?x8=>GOLD_CHESTPLATE:0x1",
		"GOLD_INGOT:?x5=>GOLD_HELMET:0x1",
		"GOLD_INGOT:?x7=>GOLD_LEGGINGS:0x1",
		"IRON_INGOT:?x4=>IRON_BOOTS:0x1",
		"IRON_INGOT:?x8=>IRON_CHESTPLATE:0x1",
		"IRON_INGOT:?x5=>IRON_HELMET:0x1",
		"IRON_INGOT:?x7=>IRON_LEGGINGS:0x1",
		"LEATHER:?x4=>LEATHER_BOOTS:0x1",
		"LEATHER:?x8=>LEATHER_TUNIC:0x1",
		"LEATHER:?x5=>LEATHER_CAP:0x1",
		"LEATHER:?x7=>LEATHER_PANTS:0x1",
		"FIRE:?x4=>CHAIN_BOOTS:0x1",
		"FIRE:?x8=>CHAIN_CHESTPLATE:0x1",
		"FIRE:?x5=>CHAIN_HELMET:0x1",
		"FIRE:?x7=>CHAIN_LEGGINGS:0x1",
		
		//Items
		"DIAMOND:?x9=>DIAMOND_BLOCK:0x1",
		"GOLD_INGOT:?x9=>GOLD_BLOCK:0x1",
		"IRON_INGOT:?x9=>IRON_BLOCK:0x1",
		"IRON_INGOT:?x5=>MINECART:0x1",
		"WHEAT:?x9=>HAY_BALE:0x1",
		"PAPER:?x3=>BOOK:0x1",
		"WOODEN_PLANKS:?x6,BOOK:?x3=>BOOKSHELF:0x1",
		"DYE:4x9=>LAPIS_BLOCK:0x1",
		"WOOL:?x1,STICK:?x8=>PAINTING:0x1",
		"SUGARCANE:?x3=>PAPER:0x1",
		"WOODEN_PLANKS:?x6,STICK:?x1=>SIGN:0x1",
		"IRON_INGOT:?x6=>IRON_BARS:0x16",
		"COAL:0x9=>COAL_BLOCK:0x1",
		"COAL_BLOCK:?x1=>COAL:0x9",
	);
	
	private static $stone = array(
		"QUARTZ:?x4=>QUARTZ_BLOCK:0x1",
		"BRICKS_BLOCK:?x6=>BRICK_STAIRS:0x4",
		"BRICK:?x4=>BRICKS_BLOCK:0x1",
		"BRICKS_BLOCK:?x3=>SLAB:4x6",
		"SLAB:6x2=>QUARTZ_BLOCK:1x1",
		"COBBLESTONE:?x3=>SLAB:3x6",
		"COBBLESTONE:0x6=>STONE_WALL:0x6",
		"MOSSY_STONE:0x6=>STONE_WALL:1x6",
		"NETHER_BRICK:?x4=>NETHER_BRICKS:0x1",
		"NETHER_BRICKS:?x6=>NETHER_BRICKS_STAIRS:0x4",
		"QUARTZ_BLOCK:0x2=>QUARTZ_BLOCK:2x2",
		"QUARTZ_BLOCK:?x3=>SLAB:6x6",
		"SANDSTONE:0x6=>SANDSTONE_STAIRS:0x4",
		"SAND:?x4=>SANDSTONE:0x1",
		"SANDSTONE:0x4=>SANDSTONE:2x4",
		"SLAB:1x2=>SANDSTONE:1x1",
		"SANDSTONE:0x3=>SLAB:1x6",
		"STONE_BRICK:?x6=>STONE_BRICK_STAIRS:0x4",
		"STONE:?x4=>STONE_BRICK:0x4",
		"STONE_BRICKS:?x3=>SLAB:5x6",
		"STONE:?x3=>SLAB:0x6",
		"COBBLESTONE:?x6=>COBBLESTONE_STAIRS:0x4",
	);
	
	private static $recipes = array();
	
	private static function parseRecipe($recipe){
		$recipe = explode("=>", $recipe);
		$recipeItems = array();
		foreach(explode(",", $recipe[0]) as $item){
			$item = explode("x", $item);
			$id = explode(":", $item[0]);
			$meta = array_pop($id);
			$id = $id[0];
			
			$it = BlockAPI::fromString($id);
			$recipeItems[$it->getID()] = array($it->getID(), $meta === "?" ? false:intval($meta)&0xFFFF, intval($item[1]));
		}
		ksort($recipeItems);
		$item = explode("x", $recipe[1]);
		$id = explode(":", $item[0]);
		$meta = array_pop($id);
		$id = $id[0];
			
		$it = BlockAPI::fromString($id);

		$craftItem = array($it->getID(), intval($meta)&0xFFFF, intval($item[1]));
		
		$recipeString = "";
		foreach($recipeItems as $item){
			$recipeString .= $item[0]."x".$item[2].",";
		}
		$recipeString = substr($recipeString, 0, -1)."=>".$craftItem[0]."x".$craftItem[2];
		
		return array($recipeItems, $craftItem, $recipeString);
	}
	
	public static function init(){
		$server = ServerAPI::request();
		$id = 1;
		foreach(CraftingRecipes::$small as $recipe){
			$recipe = CraftingRecipes::parseRecipe($recipe);
			$recipe[3] = 0; //Type			
			CraftingRecipes::$recipes[$id] = $recipe;
			++$id;
		}
		foreach(CraftingRecipes::$big as $recipe){
			$recipe = CraftingRecipes::parseRecipe($recipe);
			$recipe[3] = 1;			
			CraftingRecipes::$recipes[$id] = $recipe;
			++$id;
		}
		foreach(CraftingRecipes::$stone as $recipe){
			$recipe = CraftingRecipes::parseRecipe($recipe);
			$recipe[3] = 2;		
			CraftingRecipes::$recipes[$id] = $recipe;
			++$id;
		}
		
		foreach(CraftingRecipes::$recipes as $id => $recipe){

			$server->query("INSERT INTO recipes (id, type, recipe) VALUES (".$id.", ".$recipe[3].", '".$recipe[2]."');");
		}

	}
	
	public static function canCraft(array $craftItem, array $recipeItems, $type){
		ksort($recipeItems);
		$recipeString = "";
		foreach($recipeItems as $item){
			$recipeString .= $item[0]."x".$item[2].",";
		}
		$recipeString = substr($recipeString, 0, -1)."=>".$craftItem[0]."x".$craftItem[2];
		$server = ServerAPI::request();

		$result = $server->query("SELECT id FROM recipes WHERE type == ".$type." AND recipe == '".$recipeString."';");
		if($result instanceof SQLite3Result){
			$continue = true;
			while(($r = $result->fetchArray(SQLITE3_NUM)) !== false){
				$continue = true;
				$recipe = CraftingRecipes::$recipes[$r[0]];
				foreach($recipe[0] as $item){
					if(!isset($recipeItems[$item[0]])){
						$continue = false;
						break;
					}
					$oitem = $recipeItems[$item[0]];
					if(($oitem[1] !== $item[1] and $item[1] !== false) or $oitem[2] !== $item[2]){
						$continue = false;
						break;
					}
				}
				if($continue === false or $craftItem[0] !== $recipe[1][0] or $recipe[1][1] !== $recipe[1][1] or $recipe[1][2] !== $recipe[1][2]){
					$continue = false;
					continue;
				}
				$continue = $recipe;
				break;
			}
		}else{
			return true;
		}
		return $continue;
	}

}
