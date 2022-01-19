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

use pocketmine\block\BlockLegacyMetadata as Meta;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\SingletonTrait;

class CompostFactory{
	use SingletonTrait;

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $list = [];

	public function __construct(){
		//region ---30% percentage compost---
		$this->register(VanillaItems::BEETROOT_SEEDS(), 30);
		$this->register(VanillaItems::DRIED_KELP(), 30);
		//Glow Berry (?, ?, 30)
		$this->register(VanillaBlocks::TALL_GRASS()->asItem(), 30);
		$this->register(VanillaBlocks::FERN()->asItem(), 30);
		$this->register(VanillaBlocks::GRASS()->asItem(), 30);
		//Hanging roots (574, 0, 30)
		//Kelp (335, 0, 30)
		$this->register(VanillaBlocks::OAK_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::ACACIA_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::BIRCH_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::DARK_OAK_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::JUNGLE_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::SPRUCE_LEAVES()->asItem(), 30);
		//Azalea Leaves (579, 0, 30)
		$this->register(VanillaItems::MELON_SEEDS(), 30);
		//Moss Carpet (590, 0, 30)
		$this->register(VanillaItems::PUMPKIN_SEEDS(), 30);
		$this->register(VanillaBlocks::OAK_SAPLING()->asItem(), 30);
		$this->register(VanillaBlocks::SPRUCE_SAPLING()->asItem(), 30);
		$this->register(VanillaBlocks::BIRCH_SAPLING()->asItem(), 30);
		$this->register(VanillaBlocks::JUNGLE_SAPLING()->asItem(), 30);
		$this->register(VanillaBlocks::ACACIA_SAPLING()->asItem(), 30);
		$this->register(VanillaBlocks::DARK_OAK_SAPLING()->asItem(), 30);
		//Sea grass (385, 0, 30)
		//Small Dripleaf (591, 0, 30)
		$this->register(VanillaItems::SWEET_BERRIES(), 30);
		$this->register(VanillaItems::WHEAT_SEEDS(), 30);

		// region: 50% percentage compost
		$this->register(VanillaBlocks::CACTUS()->asItem(), 50);
		$this->register(VanillaBlocks::DRIED_KELP()->asItem(), 50);
		//Flowering Azalea Leaves (580, 0, 50)
		//Glow Lichen (666, 0, 50)
		$this->register(VanillaItems::MELON(), 50);
		//Nether Sprouts (Block: 493, Item: 760, 50)
		$this->register(VanillaBlocks::SUGARCANE()->asItem(), 50);
		$this->register(VanillaBlocks::DOUBLE_TALLGRASS()->asItem(), 50);

		$this->registerFlowers();

		$this->register(VanillaBlocks::VINES()->asItem(), 50);
		// Weeping Vines (486, 0, 50)
		//Twisting Vines (542, 0, 50)

		// region: 65% percentage compost
		$this->register(VanillaItems::APPLE(), 65);
		//Azalea (592, 0, 65);
		$this->register(VanillaItems::BEETROOT(), 65);
		//Big Dripleaf (578, 0, 65);
		$this->register(VanillaItems::CARROT(), 65);
		$this->register(VanillaItems::COCOA_BEANS(), 65);
		$this->register(VanillaBlocks::LARGE_FERN()->asItem(), 65);
		$this->register(VanillaBlocks::LILY_PAD()->asItem(), 65);
		$this->register(VanillaBlocks::MELON()->asItem(), 65);
		//Moss Block (575, 0, 65)

		$this->register(VanillaBLOCKS::BROWN_MUSHROOM()->asItem(), 65);
		$this->register(VanillaBLOCKS::RED_MUSHROOM()->asItem(), 65);

		$this->registerMushroomBlocks();

		$this->register(VanillaBlocks::NETHER_WART()->asItem(), 65);
		$this->register(VanillaItems::POTATO(), 65);
		$this->register(VanillaBlocks::PUMPKIN()->asItem(), 65);
		$this->register(VanillaBlocks::CARVED_PUMPKIN()->asItem(), 65);
		$this->register(VanillaBlocks::SEA_PICKLE()->asItem(), 65);
		//Shroomlight (485, 0, 65)
		//Spore Blossom (567, 0, 65)
		$this->register(VanillaItems::WHEAT(), 65);
		//Crimson fungus (483, 0, 65)
		//Warped fungus (484, 0, 65)
		//Crimson roots (478, 0, 65)
		//Warped roots (479, 0, 65)

		// region: 85% percentage compost
		$this->register(VanillaItems::BAKED_POTATO(), 85);
		$this->register(VanillaItems::BREAD(), 85);
		$this->register(VanillaItems::COOKIE(), 85);
		//Flowering Azalea (593, 0, 85)
		$this->register(VanillaBlocks::HAY_BALE()->asItem(), 85);
		$this->register(VanillaBlocks::NETHER_WART_BLOCK()->asItem(), 85);
		//Warped Wart Block (482, 0, 85)

		// region: 100% percentage compost
		$this->register(VanillaBlocks::CAKE()->asItem(), 100);
		$this->register(VanillaItems::PUMPKIN_PIE(), 100);
	}

	private function registerFlowers() : void{
		$this->register(VanillaBlocks::DANDELION()->asItem(), 50);

		$this->register(VanillaBlocks::POPPY()->asItem(), 50);
		$this->register(VanillaBlocks::BLUE_ORCHID()->asItem(), 50);
		$this->register(VanillaBlocks::ALLIUM()->asItem(), 50);
		$this->register(VanillaBlocks::AZURE_BLUET()->asItem(), 50);
		$this->register(VanillaBlocks::RED_TULIP()->asItem(), 50);
		$this->register(VanillaBlocks::ORANGE_TULIP()->asItem(), 50);
		$this->register(VanillaBlocks::WHITE_TULIP()->asItem(), 50);
		$this->register(VanillaBlocks::PINK_TULIP()->asItem(), 50);
		$this->register(VanillaBlocks::OXEYE_DAISY()->asItem(), 50);
		$this->register(VanillaBlocks::CORNFLOWER()->asItem(), 50);
		$this->register(VanillaBlocks::LILY_OF_THE_VALLEY()->asItem(), 50);

		$this->register(VanillaBlocks::SUNFLOWER()->asItem(), 65);
		$this->register(VanillaBlocks::LILAC()->asItem(), 65);
		$this->register(VanillaBlocks::ROSE_BUSH()->asItem(), 65);
		$this->register(VanillaBlocks::PEONY()->asItem(), 65);
	}

	private function registerMushroomBlocks() : void{
		foreach ([VanillaBlocks::BROWN_MUSHROOM_BLOCK(), VanillaBlocks::RED_MUSHROOM_BLOCK()] as $block) {
			foreach (
				[
					Meta::MUSHROOM_BLOCK_ALL_PORES,
					Meta::MUSHROOM_BLOCK_CAP_NORTHWEST_CORNER,
					Meta::MUSHROOM_BLOCK_CAP_NORTH_SIDE,
					Meta::MUSHROOM_BLOCK_CAP_NORTHEAST_CORNER,
					Meta::MUSHROOM_BLOCK_CAP_WEST_SIDE,
					Meta::MUSHROOM_BLOCK_CAP_TOP_ONLY,
					Meta::MUSHROOM_BLOCK_CAP_EAST_SIDE,
					Meta::MUSHROOM_BLOCK_CAP_SOUTHWEST_CORNER,
					Meta::MUSHROOM_BLOCK_CAP_SOUTH_SIDE,
					Meta::MUSHROOM_BLOCK_CAP_SOUTHEAST_CORNER,
					Meta::MUSHROOM_BLOCK_ALL_CAP,
				] as $meta
			) {
				$block->readStateFromData($block->getId(), $meta);
				$this->register($block->asItem(), 85);
			}

			//and the invalid states
			for ($meta = 11; $meta <= 13; ++$meta){
				$this->remap($block->asItem()->getId(), $meta, 85);
			}
			$this->remap($block->asItem()->getId(), Meta::MUSHROOM_BLOCK_STEM, 85);
			$this->remap($block->asItem()->getId(), Meta::MUSHROOM_BLOCK_ALL_STEM, 65);
		}
	}

	public function register(Item $item, int $percentage, bool $overwrite = false) : bool{
		return $this->remap($item->getId(), $item->getMeta(), $percentage, $overwrite);
	}

	public function remap(int $id, int $meta, int $percentage, bool $overwrite = false) : bool{
		$fullId = $this->getListOffset($id, $meta);
		if (!isset($this->list[$fullId]) || $overwrite) {
			$this->list[$fullId] = $percentage;
			return true;
		}
		return false;
	}

	public function isCompostable(Item $item) : bool{
		return !$item->isNull() && isset($this->list[$this->getListOffset($item->getId(), $item->getMeta())]);
	}

	public function getPercentage(Item $item) : int{
		return $this->list[$this->getListOffset($item->getId(), $item->getMeta())] ?? 0;
	}

	private function getListOffset(int $id, int $meta = 0) : int{
		if($id < -0x8000 || $id > 0x7fff){
			throw new \InvalidArgumentException("ID must be in range " . -32768 . " - " . 32767);
		}
		return (($id & 0xffff) << 16) | ($meta & 0xffff);
	}
}
