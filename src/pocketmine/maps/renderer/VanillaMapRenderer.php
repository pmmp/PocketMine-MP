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
	protected $currentCheckX = 0;

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
			$realScale = 1 << $mapData->getScale();

			$centerX = $mapData->getCenterX();
			$centerZ = $mapData->getCenterZ();

			$playerMapX = (int) (floor($player->x - $centerX) / $realScale + 64);
			$playerMapY = (int) (floor($player->z - $centerZ) / $realScale + 64);
			$maxCheckDistance = 128 / $realScale;

			$world = $player->level;
			$air = new Air();

			$avgY = 0;

			for($y = max(0, $playerMapY - $maxCheckDistance + 1); $y < min(128, $playerMapY + $maxCheckDistance); $y++){
				$mapX = $this->currentCheckX;
				$mapY = $y;

				$distX = $mapX - $playerMapX;
				$distY = $mapY - $playerMapY;

				$isTooFar = $distX ** 2 + $distY ** 2 > ($maxCheckDistance - 2) ** 2;

				$worldX = ($centerX / $realScale + $mapX - 64) * $realScale;
				$worldZ = ($centerZ / $realScale + $mapY - 64) * $realScale;

				if($world->isChunkLoaded($worldX >> 4, $worldZ >> 4)){
					$liquidDepth = 0;
					$nextAvgY = 0;

					$chunk = $world->getChunk($worldX >> 4, $worldZ >> 4);
					$worldY = $chunk->getHeightMap($worldX & 15, $worldZ & 15) + 1;
					$block = clone $air;

					if($worldY > 1){
						while(true){
							$worldY--;
							$block = $world->getBlockAt($worldX, $worldY, $worldZ);

							if($block->getId() !== Block::AIR or $worldY <= 0){
								break;
							}
						}
						if($worldY > 0 and $block instanceof Liquid){
							$attempt = 0;
							$worldY2 = $worldY - 1;

							while($attempt++ <= 10){
								$b = $world->getBlockAt($worldX, $worldY2--, $worldZ);
								$liquidDepth++;

								if($worldY2 <= 0 or !($b instanceof Liquid)){
									break;
								}
							}
						}

						$nextAvgY += (int) $worldY / (int) ($realScale * $realScale);
						$mapColor = self::getMapColorByBlock($block);
					}else{
						$mapColor = new Color(0, 0, 0);
					}

					$liquidDepth /= ($realScale * $realScale);
					$avgYDifference = ($nextAvgY - $avgY) * 4 / (int) ($realScale + 4) + ((int) ($mapX + $mapY & 1) - 0.5) * 0.4;
					$colorDepth = 1;

					if($avgYDifference > 0.6){
						$colorDepth = 2;
					}

					if($avgYDifference < -0.6){
						$colorDepth = 0;
					}

					if($mapColor->getR() === 64 and $mapColor->getG() === 64 and $mapColor->getB() === 255){ // water color
						$avgYDifference = (int) $liquidDepth * 0.1 + (int) ($mapX + $mapY & 1) * 0.2;
						$colorDepth = 1;

						if($avgYDifference < 0.5){
							$colorDepth = 2;
						}

						if($avgYDifference > 0.9){
							$colorDepth = 0;
						}
					}

					$avgY = $nextAvgY;

					if(($distX ** 2 + $distY ** 2) < $maxCheckDistance ** 2 and (!$isTooFar or ($mapX + $mapY & 1) !== 0)){
						$oldColor = $mapData->getColorAt($mapX, $mapY);
						$newColor = self::colorizeMapColor($mapColor, $colorDepth);

						if(!$oldColor->equals($newColor)){
							$mapData->setColorAt($mapX, $mapY, $newColor);

							$mapData->updateTextureAt($mapX, $mapY);
						}
					}
				}
			}

			$this->currentCheckX++;
			$this->currentCheckX %= 128;
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
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_ORANGE) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_ORANGE) or $id === Block::ORANGE_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_ORANGE) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_ORANGE) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_ORANGE) or $id === Block::PUMPKIN or $id === Block::JACK_O_LANTERN or $id === Block::HARDENED_CLAY or ($id === Block::WOOD2 and ($meta & 0x03) == Wood2::ACACIA) or ($id === Block::PLANKS and $meta == Planks::ACACIA) or ($id === Block::FENCE and $meta == Planks::ACACIA) or $id === Block::ACACIA_FENCE_GATE or $id === Block::ACACIA_STAIRS or ($id === Block::WOODEN_SLAB and ($meta & 0x07) == Planks::ACACIA)){
			return new Color(216, 127, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_MAGENTA) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_MAGENTA) or $id === Block::MAGENTA_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_MAGENTA) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_MAGENTA) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_MAGENTA) or $id === Block::PURPUR_BLOCK or $id === Block::PURPUR_STAIRS or ($id === Block::STONE_SLAB2 and ($meta & 0x07) == Stone::PURPUR_BLOCK)){
			return new Color(178, 76, 216);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_LIGHT_BLUE) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_LIGHT_BLUE) or $id === Block::LIGHT_BLUE_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_LIGHT_BLUE) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_LIGHT_BLUE) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_LIGHT_BLUE)){
			return new Color(102, 153, 216);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_YELLOW) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_YELLOW) or $id === Block::YELLOW_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_YELLOW) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_YELLOW) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_YELLOW) or $id === Block::HAY_BALE or $id === Block::SPONGE){
			return new Color(229, 229, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_LIME) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_LIME) or $id === Block::LIME_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_LIME) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_LIME) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_LIME) or $id === Block::MELON_BLOCK){
			return new Color(229, 229, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_PINK) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_PINK) or $id === Block::PINK_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_PINK) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_PINK) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_PINK)){
			return new Color(242, 127, 165);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_GRAY) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_GRAY) or $id === Block::GRAY_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_GRAY) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_GRAY) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_GRAY) or $id === Block::CAULDRON_BLOCK){
			return new Color(76, 76, 76);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_LIGHT_GRAY) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_LIGHT_GRAY) or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_LIGHT_GRAY) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_LIGHT_GRAY) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_LIGHT_GRAY) or $id === Block::STRUCTURE_BLOCK){
			return new Color(153, 153, 153);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_CYAN) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_CYAN) or $id === Block::CYAN_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_CYAN) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_CYAN) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_CYAN) or ($id === Block::PRISMARINE and $meta == Prismarine::NORMAL)){
			return new Color(76, 127, 153);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_PURPLE) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_PURPLE) or $id === Block::PURPLE_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_PURPLE) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_PURPLE) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_PURPLE) or $id === Block::MYCELIUM or $id === Block::REPEATING_COMMAND_BLOCK or $id === Block::CHORUS_PLANT or $id === Block::CHORUS_FLOWER){
			return new Color(127, 63, 178);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_BLUE) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_BLUE) or $id === Block::BLUE_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_BLUE) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_BLUE) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_BLUE)){
			return new Color(51, 76, 178);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_BROWN) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_BROWN) or $id === Block::BROWN_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_BROWN) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_BROWN) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_BROWN) or $id === Block::SOUL_SAND or ($id === Block::WOOD2 and ($meta & 0x03) == Wood2::DARK_OAK) or ($id === Block::PLANKS and $meta == Planks::DARK_OAK) or ($id === Block::FENCE and $meta == Planks::DARK_OAK) or $id === Block::DARK_OAK_FENCE_GATE or $id === Block::DARK_OAK_STAIRS or ($id === Block::WOODEN_SLAB and ($meta & 0x07) == Planks::DARK_OAK) or $id === Block::COMMAND_BLOCK){
			return new Color(102, 76, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_GREEN) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_GREEN) or $id === Block::GREEN_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_GREEN) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_GREEN) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_GREEN) or $id === Block::END_PORTAL_FRAME or $id === Block::CHAIN_COMMAND_BLOCK){
			return new Color(102, 127, 51);
		}elseif(($id === Block::CONCRETE and $meta == self::COLOR_BLOCK_RED) or ($id === Block::CONCRETE_POWDER and $meta == self::COLOR_BLOCK_RED) or $id === Block::RED_GLAZED_TERRACOTTA or ($id === Block::WOOL and $meta == self::COLOR_BLOCK_RED) or ($id === Block::CARPET and $meta == self::COLOR_BLOCK_RED) or ($id === Block::STAINED_HARDENED_CLAY and $meta == self::COLOR_BLOCK_RED) or $id === Block::RED_MUSHROOM_BLOCK or $id === Block::BRICK_BLOCK or ($id === Block::STONE_SLAB and ($meta & 0x07) == 4) or $id === Block::BRICK_STAIRS or $id === Block::ENCHANTING_TABLE or $id === Block::NETHER_WART_BLOCK or $id === Block::NETHER_WART_PLANT){
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
	 * @param Color $color
	 * @param int   $colorLevel colorization level
	 *
	 * @return Color
	 */
	public static function colorizeMapColor(Color $color, int $colorLevel) : Color{
		$colorDepth = 220;

		if($colorLevel == 3){
			$colorDepth = 135;
		}elseif($colorLevel == 2){
			$colorDepth = 255;
		}elseif($colorLevel == 1){
			$colorDepth = 220;
		}elseif($colorLevel == 0){
			$colorDepth = 180;
		}

		$abgr = $color->toABGR();

		$r = ($abgr >> 16 & 255) * $colorDepth / 255;
		$g = ($abgr >> 8 & 255) * $colorDepth / 255;
		$b = ($abgr & 255) * $colorDepth / 255;

		return Color::fromABGR(-16777216 | $r << 16 | $g << 8 | $b);
	}
}
