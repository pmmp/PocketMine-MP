<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

namespace pocketmine\maps\renderer;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\block\Planks;
use pocketmine\block\Prismarine;
use pocketmine\block\Stone;
use pocketmine\block\StoneSlab;
use pocketmine\block\Wood;
use pocketmine\block\Wood2;
use pocketmine\maps\MapData;
use pocketmine\Player;
use pocketmine\utils\Color;

class VanillaMapRenderer extends MapRenderer{

	public function initialize(MapData $mapData) : void{

	}

	public function onMapCreated(Player $player, MapData $mapData) : void{
		// TODO: make this async
		for($i = 0; $i < 128; $i++){
			$this->render($mapData, $player);
		}
	}

	/**
	 * Renders a map
	 *
	 * @param MapData $mapData
	 * @param Player  $player
	 */
	public function render(MapData $mapData, Player $player) : void{
		if($mapData->getLevelName() === ""){
			$mapData->setLevelName($player->level->getFolderName());
		}

		if($mapData->getLevelName() === $player->level->getFolderName() and $player->level->getDimension() === $mapData->getDimension() and !$mapData->isLocked()){
			$i = 1 << $mapData->getScale();

			$j = $mapData->getCenterX();
			$k = $mapData->getCenterZ();

			$l = (int) (floor($player->x - $j) / $i + 64);
			$i1 = (int) (floor($player->z - $k) / $i + 64);
			$j1 = 128 / $i;

			$info = $mapData->getMapInfo($player);
			$world = $player->level;
			$air = new Air();

			$avgY = 0;

			for($y = max(0, $i1 - $j1 + 1); $y < min(128, $i1 + $j1); $y++){
				$k1 = $info->currentCheckX;
				$l1 = $y;

				if($k1 >= 0 and $l1 >= -1 and $k1 < 128 and $l1 < 128){
					$i2 = $k1 - $l;
					$j2 = $l1 - $i1;
					$flag1 = $i2 * $i2 + $j2 * $j2 > ($j1 - 2) * ($j1 - 2);
					$k2 = ($j / $i + $k1 - 64) * $i;
					$l2 = ($k / $i + $l1 - 64) * $i;

					if($world->isChunkLoaded($k2 >> 4, $l2 >> 4)){
						$k3 = 0;
						$d1 = 0.0;
						$chunk = $world->getChunk($k2 >> 4, $l2 >> 4);
						$k4 = $chunk->getHeightMap($k2 & 15, $l2 & 15) + 1;
						$block = clone $air;

						if($k4 > 1){
							while(true){
								$k4--;
								$block = $world->getBlockAt($k2, $k4, $l2);

								if($block->getId() !== Block::AIR or $k4 <= 0){
									break;
								}
							}
							if($k4 > 0 and $block instanceof Liquid){
								$attempt = 0;
								$l4 = $k4 - 1;

								while($attempt++ <= 10){
									$b = $world->getBlockAt($k2, $l4--, $l2);
									$k3++;

									if($l4 <= 0 or !($b instanceof Liquid)){
										break;
									}
								}
							}

							$d1 += (int) $k4 / (int) ($i * $i);
							$mapColor = self::getMapColorByBlock($block);
						}else{
							$mapColor = new Color(0, 0, 0);
						}

						$k3 = $k3 / ($i * $i);
						$d2 = ($d1 - $avgY) * 4.0 / (int) ($i + 4) + ((int) ($k1 + $l1 & 1) - 0.5) * 0.4;
						$i5 = 1;

						if($d2 > 0.6){
							$i5 = 2;
						}

						if($d2 < -0.6){
							$i5 = 0;
						}

						if($mapColor->getR() === 64 and $mapColor->getG() === 64 and $mapColor->getB() === 255){ // water color
							$d2 = (int) $k3 * 0.1 + (int) ($k1 + $l1 & 1) * 0.2;
							$i5 = 1;

							if($d2 < 0.5){
								$i5 = 2;
							}

							if($d2 > 0.9){
								$i5 = 0;
							}
						}

						$avgY = $d1;

						if($l1 >= 0 and $i2 * $i2 + $j2 * $j2 < $j1 * $j1 and (!$flag1 || ($k1 + $l1 & 1) != 0)){
							$b0 = $mapData->getColorAt($k1, $l1)->toABGR();
							$b1 = self::colorizeMapColor($mapColor->toABGR(), $i5);

							if($b0 !== $b1){
								$mapData->setColorAt($k1, $l1, Color::fromABGR($b1));

								$mapData->updateTextureAt($k1, $l1);
							}
						}
					}
				}
			}

			$info->currentCheckX++;
			$info->currentCheckX %= 129;
		}
	}
	
	public const COLOR_BLOCK_WHITE = 0;
	public const COLOR_BLOCK_ORANGE = 1;
	public const COLOR_BLOCK_MAGENTA = 2;
	public const COLOR_BLOCK_LIGHT_BLUE = 3;
	public const COLOR_BLOCK_YELLOW = 4;
	public const COLOR_BLOCK_LIME = 5;
	public const COLOR_BLOCK_PINK = 6;
	public const COLOR_BLOCK_GRAY = 7;
	public const COLOR_BLOCK_LIGHT_GRAY = 8;
	public const COLOR_BLOCK_CYAN = 9;
	public const COLOR_BLOCK_PURPLE = 10;
	public const COLOR_BLOCK_BLUE = 11;
	public const COLOR_BLOCK_BROWN = 12;
	public const COLOR_BLOCK_GREEN = 13;
	public const COLOR_BLOCK_RED = 14;
	public const COLOR_BLOCK_BLACK = 15;

	/**
	 * TODO: Separate map colors to blocks
	 *
	 * @param Block $block
	 *
	 * @return Color
	 */
	public static function getMapColorByBlock(Block $block) : Color{
		$meta = $block->getDamage();
		$id = $block->getId();
		if($id === Block::AIR){
			return new Color(0, 0, 0);
		}elseif($id === Block::GRASS or $id === Block::SLIME_BLOCK){
			return new Color(127, 178, 56);
		}elseif($id === Block::SAND or $id === Block::SANDSTONE or $id === Block::SANDSTONE_STAIRS or ($id === Block::STONE_SLAB and ($meta & 0x07) == StoneSlab::SANDSTONE) or ($id === Block::DOUBLE_STONE_SLAB and $meta == StoneSlab::SANDSTONE) or $id === Block::GLOWSTONE or $id === Block::END_STONE or ($id === Block::PLANKS and $meta == Planks::BIRCH) or ($id === Block::LOG and ($meta & 0x03) == Wood::BIRCH) or $id === Block::BIRCH_FENCE_GATE or ($id === Block::FENCE and $meta = Planks::BIRCH) or $id === Block::BIRCH_STAIRS or ($id === Block::WOODEN_SLAB and ($meta & 0x07) == Planks::BIRCH) or $id === Block::BONE_BLOCK or $id === Block::END_BRICKS){
			return new Color(247, 233, 163);
		}elseif($id === Block::BED_BLOCK or $id === Block::COBWEB){
			return new Color(199, 199, 199);
		}elseif($id === Block::LAVA or $id === Block::STILL_LAVA or $id === Block::FLOWING_LAVA or $id === Block::TNT or $id === Block::FIRE or $id === Block::REDSTONE_BLOCK){
			return new Color(255, 0, 0);
		}elseif($id === Block::ICE or $id === Block::PACKED_ICE or $id === Block::FROSTED_ICE){
			return new Color(160, 160, 255);
		}elseif($id === Block::IRON_BLOCK or $id === Block::IRON_DOOR_BLOCK or $id === Block::IRON_TRAPDOOR or $id === Block::IRON_BARS or $id === Block::BREWING_STAND_BLOCK or $id === Block::ANVIL or $id === Block::HEAVY_WEIGHTED_PRESSURE_PLATE){
			return new Color(167, 167, 167);
		}elseif($id === Block::SAPLING or $id === Block::LEAVES or $id === Block::LEAVES2 or $id === Block::TALL_GRASS or $id === Block::DEAD_BUSH or $id === Block::RED_FLOWER or $id === Block::DOUBLE_PLANT or $id === Block::BROWN_MUSHROOM or $id === Block::RED_MUSHROOM or $id === Block::WHEAT_BLOCK or $id === Block::CARROT_BLOCK or $id === Block::POTATO_BLOCK or $id === Block::BEETROOT_BLOCK or $id === Block::CACTUS or $id === Block::SUGARCANE_BLOCK or $id === Block::PUMPKIN_STEM or $id === Block::MELON_STEM or $id === Block::VINE or $id === Block::LILY_PAD){
			return new Color(0, 124, 0);
		}elseif(($id === Block::WOOL and $meta == self::COLOR_BLOCK_WHITE) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_WHITE) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_WHITE) or $id === Block::SNOW_LAYER or $id === Block::SNOW_BLOCK){
			return new Color(255, 255, 255);
		}elseif($id === Block::CLAY_BLOCK or $id === Block::MONSTER_EGG){
			return new Color(164, 168, 184);
		}elseif($id === Block::DIRT or $id === Block::FARMLAND or ($id === Block::STONE and $meta == Stone::GRANITE) or ($id === Block::STONE and $meta == Stone::POLISHED_GRANITE) or ($id === Block::SAND and $meta == 1) or $id === Block::RED_SANDSTONE or $id === Block::RED_SANDSTONE_STAIRS or ($id === Block::STONE_SLAB2 and ($meta & 0x07) == StoneSlab::RED_SANDSTONE) or ($id === Block::LOG and ($meta & 0x03) == Wood::JUNGLE) or ($id === Block::PLANKS and $meta == Planks::JUNGLE) or $id === Block::JUNGLE_FENCE_GATE or ($id === Block::FENCE and $meta == Planks::JUNGLE) or $id === Block::JUNGLE_STAIRS or ($id === Block::WOODEN_SLAB and ($meta & 0x07) == Planks::JUNGLE)){
			return new Color(151, 109, 77);
		}elseif($id === Block::STONE or ($id === Block::STONE_SLAB and ($meta & 0x07) == StoneSlab::STONE) or $id === Block::COBBLESTONE or $id === Block::COBBLESTONE_STAIRS or ($id === Block::STONE_SLAB and ($meta & 0x07) == StoneSlab::COBBLESTONE) or $id === Block::COBBLESTONE_WALL or $id === Block::MOSS_STONE or ($id === Block::STONE and $meta == Stone::ANDESITE) or ($id === Block::STONE and $meta == Stone::POLISHED_ANDESITE) or $id === Block::BEDROCK or $id === Block::GOLD_ORE or $id === Block::IRON_ORE or $id === Block::COAL_ORE or $id === Block::LAPIS_ORE or $id === Block::DISPENSER or $id === Block::DROPPER or $id === Block::STICKY_PISTON or $id === Block::PISTON or $id === Block::PISTON_ARM_COLLISION or $id === Block::MOVINGBLOCK or $id === Block::MONSTER_SPAWNER or $id === Block::DIAMOND_ORE or $id === Block::FURNACE or $id === Block::STONE_PRESSURE_PLATE or $id === Block::REDSTONE_ORE or $id === Block::STONE_BRICK or $id === Block::STONE_BRICK_STAIRS or ($id === Block::STONE_SLAB and ($meta & 0x07) == StoneSlab::STONE_BRICK) or $id === Block::ENDER_CHEST or $id === Block::HOPPER_BLOCK or $id === Block::GRAVEL or $id === Block::OBSERVER){
			return new Color(112, 112, 112);
		}elseif($id === Block::WATER or $id === Block::STILL_WATER or $id === Block::FLOWING_WATER){
			return new Color(64, 64, 255);
		}elseif(($id === Block::WOOD and ($meta & 0x03) == Wood::OAK) or ($id === Block::PLANKS and $meta == Planks::OAK) or ($id === Block::FENCE and $meta == Planks::OAK) or $id === Block::OAK_FENCE_GATE or $id === Block::OAK_STAIRS or ($id === Block::WOODEN_SLAB and ($meta & 0x07) == Planks::OAK) or $id === Block::NOTEBLOCK or $id === Block::BOOKSHELF or $id === Block::CHEST or $id === Block::TRAPPED_CHEST or $id === Block::CRAFTING_TABLE or $id === Block::WOODEN_DOOR_BLOCK or $id === Block::BIRCH_DOOR_BLOCK or $id === Block::SPRUCE_DOOR_BLOCK or $id === Block::JUNGLE_DOOR_BLOCK or $id === Block::ACACIA_DOOR_BLOCK or $id === Block::DARK_OAK_DOOR_BLOCK or $id === Block::SIGN_POST or $id === Block::WALL_SIGN or $id === Block::WOODEN_PRESSURE_PLATE or $id === Block::JUKEBOX or $id === Block::WOODEN_TRAPDOOR or $id === Block::BROWN_MUSHROOM_BLOCK or $id === Block::STANDING_BANNER or $id === Block::WALL_BANNER or $id === Block::DAYLIGHT_SENSOR or $id === Block::DAYLIGHT_SENSOR_INVERTED){
			return new Color(143, 119, 72);
		}elseif($id === Block::QUARTZ_BLOCK or ($id === Block::STONE_SLAB and ($meta & 0x07) == 6) or $id === Block::QUARTZ_STAIRS or ($id === Block::STONE and $meta == Stone::DIORITE) or ($id === Block::STONE and $meta == Stone::POLISHED_DIORITE) or $id === Block::SEA_LANTERN){
			return new Color(255, 252, 245);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_ORANGE)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_ORANGE) or $id === Block::ORANGE_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_ORANGE) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_ORANGE) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_ORANGE) or $id === Block::PUMPKIN or $id === Block::JACK_O_LANTERN or $id === Block::HARDENED_CLAY or ($id === Block::WOOD2 and ($meta & 0x03) == Wood2::ACACIA) or ($id === Block::PLANKS and $meta == Planks::ACACIA) or ($id === Block::FENCE and $meta == Planks::ACACIA) or $id === Block::ACACIA_FENCE_GATE or $id === Block::ACACIA_STAIRS or ($id === Block::WOODEN_SLAB and ($meta & 0x07) == Planks::ACACIA)){
			return new Color(216, 127, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_MAGENTA)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_MAGENTA) or $id === Block::MAGENTA_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_MAGENTA) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_MAGENTA) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_MAGENTA) or $id === Block::PURPUR_BLOCK or $id === Block::PURPUR_STAIRS or ($id === Block::STONE_SLAB2 and ($meta & 0x07) == Stone::PURPUR_BLOCK)){
			return new Color(178, 76, 216);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_LIGHT_BLUE)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_LIGHT_BLUE) or $id === Block::LIGHT_BLUE_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_LIGHT_BLUE) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_LIGHT_BLUE) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_LIGHT_BLUE)){
			return new Color(102, 153, 216);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_YELLOW)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_YELLOW) or $id === Block::YELLOW_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_YELLOW) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_YELLOW) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_YELLOW) or $id === Block::HAY_BALE or $id === Block::SPONGE){
			return new Color(229, 229, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_LIME)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_LIME) or $id === Block::LIME_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_LIME) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_LIME) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_LIME) or $id === Block::MELON_BLOCK){
			return new Color(229, 229, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_PINK)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_PINK) or $id === Block::PINK_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_PINK) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_PINK) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_PINK)){
			return new Color(242, 127, 165);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_GRAY)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_GRAY) or $id === Block::GRAY_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_GRAY) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_GRAY) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_GRAY) or $id === Block::CAULDRON_BLOCK){
			return new Color(76, 76, 76);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_LIGHT_GRAY)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_LIGHT_GRAY) or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_LIGHT_GRAY) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_LIGHT_GRAY) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_LIGHT_GRAY) or $id === Block::STRUCTURE_BLOCK){
			return new Color(153, 153, 153);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_CYAN)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_CYAN) or $id === Block::CYAN_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_CYAN) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_CYAN) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_CYAN) or ($id === Block::PRISMARINE and $meta == Prismarine::NORMAL)){
			return new Color(76, 127, 153);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_PURPLE)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_PURPLE) or $id === Block::PURPLE_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_PURPLE) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_PURPLE) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_PURPLE) or $id === Block::MYCELIUM or $id === Block::REPEATING_COMMAND_BLOCK or $id === Block::CHORUS_PLANT or $id === Block::CHORUS_FLOWER){
			return new Color(127, 63, 178);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_BLUE)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_BLUE) or $id === Block::BLUE_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_BLUE) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_BLUE) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_BLUE)){
			return new Color(51, 76, 178);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_BROWN)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_BROWN) or $id === Block::BROWN_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_BROWN) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_BROWN) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_BROWN) or $id === Block::SOUL_SAND or ($id === Block::WOOD2 and ($meta & 0x03) == Wood2::DARK_OAK) or ($id === Block::PLANKS and $meta == Planks::DARK_OAK) or ($id === Block::FENCE and $meta == Planks::DARK_OAK) or $id === Block::DARK_OAK_FENCE_GATE or $id === Block::DARK_OAK_STAIRS or ($id === Block::WOODEN_SLAB and ($meta & 0x07) == Planks::DARK_OAK) or $id === Block::COMMAND_BLOCK){
			return new Color(102, 76, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_GREEN)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_GREEN) or $id === Block::GREEN_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_GREEN) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_GREEN) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_GREEN) or $id === Block::END_PORTAL_FRAME or $id === Block::CHAIN_COMMAND_BLOCK){
			return new Color(102, 127, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_RED)  or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_RED) or $id === Block::RED_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_RED) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_RED) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_RED) or $id === Block::RED_MUSHROOM_BLOCK or $id === Block::BRICK_BLOCK or ($id === Block::STONE_SLAB and ($meta & 0x07) == 4) or $id === Block::BRICK_STAIRS or $id === Block::ENCHANTING_TABLE or $id === Block::NETHER_WART_BLOCK or $id === Block::NETHER_WART_PLANT){
			return new Color(153, 51, 51);
		}elseif(($id === Block::WOOL and $meta == 0) or ($id === Block::CARPET and $meta == 0) or ($id === Block::STAINED_HARDENED_CLAY and $meta == 0) or $id === Block::DRAGON_EGG or $id === Block::COAL_BLOCK or $id === Block::OBSIDIAN or $id === Block::END_PORTAL){
			return new Color(25, 25, 25);
		}elseif($id === Block::GOLD_BLOCK or $id === Block::LIGHT_WEIGHTED_PRESSURE_PLATE){
			return new Color(250, 238, 77);
		}elseif($id === Block::DIAMOND_BLOCK or ($id === Block::PRISMARINE and $meta == Prismarine::DARK) or ($id === Block::PRISMARINE and $meta == Prismarine::BRICKS) or $id === Block::BEACON){
			return new Color(92, 219, 213);
		}elseif($id === Block::LAPIS_BLOCK){
			return new Color(74, 128, 255);
		}elseif($id === Block::EMERALD_BLOCK){
			return new Color(0, 217, 58);
		}elseif($id === Block::PODZOL or ($id === Block::WOOD and ($meta & 0x03) == Wood::SPRUCE) or ($id === Block::PLANKS and $meta == Planks::SPRUCE) or ($id === Block::FENCE and $meta == Planks::SPRUCE) or $id === Block::SPRUCE_FENCE_GATE or $id === Block::SPRUCE_STAIRS or ($id === Block::WOODEN_SLAB and ($meta & 0x07) == Planks::SPRUCE)){
			return new Color(129, 86, 49);
		}elseif($id === Block::NETHERRACK or $id === Block::NETHER_QUARTZ_ORE or $id === Block::NETHER_BRICK_FENCE or $id === Block::NETHER_BRICK_BLOCK or $id === Block::MAGMA or $id === Block::NETHER_BRICK_STAIRS or ($id === Block::STONE_SLAB and ($meta & 0x07) == 7)){
			return new Color(112, 2, 0);
		}else{
			return new Color(0, 0, 0, 0);
		}
	}

	/**
	 * @param int $v Color hash
	 * @param int $value colorization value
	 *
	 * @return int
	 */
	public static function colorizeMapColor(int $v, int $value) : int{
		$short1 = 220;
		if($value == 3){
			$short1 = 135;
		}
		if($value == 2){
			$short1 = 255;
		}
		if($value == 1){
			$short1 = 220;
		}
		if($value == 0){
			$short1 = 180;
		}
		$i = ($v >> 16 & 255) * $short1 / 255;
		$j = ($v >> 8 & 255) * $short1 / 255;
		$k = ($v & 255) * $short1 / 255;
		return -16777216 | $i << 16 | $j << 8 | $k;
	}
}
