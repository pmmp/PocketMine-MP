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

declare(strict_types=1);

namespace pocketmine\block\utils;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;

/* Gonna remove this but i don't want to retype everthing so :p */

trait CompostList {
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $compost_list = [];

	public function __construct() {
		// region: 30% percentage compost
		self::register(VanillaItems::BEETROOT_SEEDS(), 30);
		self::register(VanillaItems::DRIED_KELP(), 30);
		//Todo: add glow berry (?:?)
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 0), VanillaBlocks::TALL_GRASS()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 1), VanillaBlocks::TALL_GRASS()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::GRASS, 0), VanillaBlocks::GRASS()), 30);
		//Todo: add hanging roots (574:0)
		//Todo: add kelp (335:0)

		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 0), VanillaBlocks::OAK_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 1), VanillaBlocks::SPRUCE_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 2), VanillaBlocks::BIRCH_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 3), VanillaBlocks::JUNGLE_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 4), VanillaBlocks::OAK_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 5), VanillaBlocks::SPRUCE_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 6), VanillaBlocks::BIRCH_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 7), VanillaBlocks::JUNGLE_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 8), VanillaBlocks::OAK_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 9), VanillaBlocks::SPRUCE_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 10), VanillaBlocks::BIRCH_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 11), VanillaBlocks::JUNGLE_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 12), VanillaBlocks::OAK_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 13), VanillaBlocks::SPRUCE_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 14), VanillaBlocks::BIRCH_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 15), VanillaBlocks::JUNGLE_LEAVES()), 30);

		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 0), VanillaBlocks::ACACIA_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 1), VanillaBlocks::DARK_OAK_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 4), VanillaBlocks::ACACIA_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 5), VanillaBlocks::DARK_OAK_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 8), VanillaBlocks::ACACIA_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 9), VanillaBlocks::DARK_OAK_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 12), VanillaBlocks::ACACIA_LEAVES()), 30);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 13), VanillaBlocks::DARK_OAK_LEAVES()), 30);

		self::register(VanillaItems::MELON_SEEDS(), 30);
		//Todo: add moss carpet (590:0)

		self::register(VanillaItems::PUMPKIN_SEEDS(), 30);
		self::register(VanillaBlocks::OAK_SAPLING()->asItem(), 30);
		self::register(VanillaBlocks::SPRUCE_SAPLING()->asItem(), 30);
		self::register(VanillaBlocks::BIRCH_SAPLING()->asItem(), 30);
		self::register(VanillaBlocks::JUNGLE_SAPLING()->asItem(), 30);
		self::register(VanillaBlocks::ACACIA_SAPLING()->asItem(), 30);
		self::register(VanillaBlocks::DARK_OAK_SAPLING()->asItem(), 30);

		//Todo: add sea grass
		//Todo: add small dripleaf
		self::register(VanillaItems::SWEET_BERRIES(), 30);
		self::register(VanillaItems::WHEAT_SEEDS(), 30);

		// region: 50% percentage compost
		self::register(VanillaBlocks::CACTUS()->asItem(), 50);

		self::register(VanillaBlocks::DRIED_KELP()->asItem(), 50);
		//Todo: add Flowering Azalea Leaves and Glow Lichen
		self::register(VanillaItems::MELON(), 50);
		//Todo: add Nether Sprouts
		self::register(VanillaBlocks::SUGARCANE()->asItem(), 50);
		//Todo: add flowers
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 2), VanillaBlocks::DOUBLE_TALLGRASS()), 50);

		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 0), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 1), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 2), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 3), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 4), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 5), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 6), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 7), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 8), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 9), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 10), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 11), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 12), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 13), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 14), VanillaBlocks::VINES()), 50);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::VINES, 15), VanillaBlocks::VINES()), 50);

		//Todo: add Weeping Vines and Twisting Vines

		// region: 65% percentage compost
		self::register(VanillaItems::APPLE(), 65);
		//Todo: add Azalea

		//Todo: add Big Dripleaf
		self::register(VanillaItems::CARROT(), 65);
		self::register(VanillaItems::COCOA_BEANS(), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 2), VanillaBlocks::TALL_GRASS()), 65); //Fern
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 3), VanillaBlocks::TALL_GRASS()), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 3), VanillaBlocks::LARGE_FERN()), 65);

		self::register(VanillaBlocks::SUNFLOWER()->asItem(), 65);
		self::register(VanillaBlocks::LILAC()->asItem(), 65);
		self::register(VanillaBlocks::ROSE_BUSH()->asItem(), 65);
		self::register(VanillaBlocks::PEONY()->asItem(), 65);

		self::register(VanillaBlocks::LILY_PAD()->asItem(), 65);

		self::register(VanillaBlocks::MELON()->asItem(), 65);
		//Todo: add moss block

		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM, 0), VanillaBlocks::BROWN_MUSHROOM()), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM, 0), VanillaBlocks::RED_MUSHROOM()), 65);

		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 15), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 15), VanillaBlocks::RED_MUSHROOM_BLOCK()), 65);

		self::register(new Item(new ItemIdentifier(ItemIds::NETHER_WART, 0), "Nether Wart"), 65);

		self::register(VanillaItems::POTATO(), 65);

		self::register(new ItemBlock(new ItemIdentifier(ItemIds::PUMPKIN, 0), VanillaBlocks::PUMPKIN()), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::PUMPKIN, 1), VanillaBlocks::PUMPKIN()), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::PUMPKIN, 2), VanillaBlocks::PUMPKIN()), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::PUMPKIN, 3), VanillaBlocks::PUMPKIN()), 65);

		self::register(new ItemBlock(new ItemIdentifier(ItemIds::CARVED_PUMPKIN, 0), VanillaBlocks::CARVED_PUMPKIN()), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::CARVED_PUMPKIN, 1), VanillaBlocks::CARVED_PUMPKIN()), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::CARVED_PUMPKIN, 2), VanillaBlocks::CARVED_PUMPKIN()), 65);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::CARVED_PUMPKIN, 3), VanillaBlocks::CARVED_PUMPKIN()), 65);

		self::register(VanillaBlocks::SEA_PICKLE()->asItem(), 65);
		//Todo: add Shroomlight, Spore Blossom
		self::register(VanillaItems::WHEAT(), 65);
		//Todo: add Fungus, Roots

		// region: 85% percentage compost
		self::register(VanillaItems::BAKED_POTATO(), 85);
		self::register(VanillaItems::BREAD(), 85);
		self::register(VanillaItems::COOKIE(), 85);
		//Todo: add Flowering Azalea
		self::register(VanillaBlocks::HAY_BALE()->asItem(), 85);

		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 0), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 1), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 2), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 3), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 4), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 5), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 6), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 7), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 8), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 9), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 10), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 11), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 12), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 13), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 14), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 85);

		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 0), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 1), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 2), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 3), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 4), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 5), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 6), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 7), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 8), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 9), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 10), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 11), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 12), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 13), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);
		self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 14), VanillaBlocks::RED_MUSHROOM_BLOCK()), 85);

		//TODO: add Nether Wart Block, Warped Wart Blocks

		// region: 100% percentage compost
		self::register(VanillaBlocks::CAKE()->asItem(), 100);
		self::register(VanillaItems::PUMPKIN_PIE(), 100);
	}

	public function addCompostSource(Item $item, int $percentage, bool $overwrite = false) : bool{
		$fullId = self::getListOffset($item->getId(), $item->getMeta());
		if (!isset(self::$list[$fullId]) || $overwrite) {
			self::$list[$fullId] = $percentage;
		}
		return false;
	}

	public function isCompostable(Item $item) : bool{
		return !$item->isNull() && isset(self::$list[self::getListOffset($item->getId(), $item->getMeta())]);
	}

	public function getPercentage(Item $item) : int{
		return self::$list[self::getListOffset($item->getId(), $item->getMeta())] ?? 0;
	}

	private static function getListOffset(int $id, int $variant = 0) : int{
		if($id < -0x8000 or $id > 0x7fff){
			throw new \InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
		}
		return (($id & 0xffff) << 16) | ($variant & 0xffff);
	}
}
