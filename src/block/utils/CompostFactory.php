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
use pocketmine\item\VanillaItems;
use pocketmine\utils\SingletonTrait;

final class CompostFactory{
	use SingletonTrait;

	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	protected array $list = [];

	public function __construct(){
		//region ---30% percentage compost---
		$this->register(VanillaBlocks::FERN()->asItem(), 30);
		$this->register(VanillaBlocks::GRASS()->asItem(), 30);
		$this->register(VanillaBlocks::HANGING_ROOTS()->asItem(), 30);
		$this->register(VanillaBlocks::TALL_GRASS()->asItem(), 30);
		$this->register(VanillaItems::BEETROOT_SEEDS(), 30);
		$this->register(VanillaItems::DRIED_KELP(), 30);
		$this->register(VanillaItems::GLOW_BERRIES(), 30);
		//Kelp
		$this->register(VanillaBlocks::ACACIA_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::AZALEA_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::BIRCH_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::CHERRY_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::DARK_OAK_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::JUNGLE_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::MANGROVE_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::OAK_LEAVES()->asItem(), 30);
		$this->register(VanillaBlocks::SPRUCE_LEAVES()->asItem(), 30);

		$this->register(VanillaBlocks::ACACIA_SAPLING()->asItem(), 30);
		$this->register(VanillaBlocks::BIRCH_SAPLING()->asItem(), 30);
		//Cherry sapling
		$this->register(VanillaBlocks::DARK_OAK_SAPLING()->asItem(), 30);
		$this->register(VanillaBlocks::JUNGLE_SAPLING()->asItem(), 30);
		$this->register(VanillaBlocks::OAK_SAPLING()->asItem(), 30);
		$this->register(VanillaBlocks::SPRUCE_SAPLING()->asItem(), 30);
		//Mangrove propagule
		//Mangrove roots
		$this->register(VanillaItems::MELON_SEEDS(), 30);
		//Moss Carpet
		$this->register(VanillaBlocks::PINK_PETALS()->asItem(), 30);
		$this->register(VanillaItems::PUMPKIN_SEEDS(), 30);
		//Pitcher Pod
		//Sea grass
		$this->register(VanillaBlocks::SMALL_DRIPLEAF()->asItem(), 30);
		$this->register(VanillaItems::SWEET_BERRIES(), 30);
		//Torchflower Seeds
		$this->register(VanillaItems::WHEAT_SEEDS(), 30);

		// region: 50% percentage compost
		$this->register(VanillaBlocks::CACTUS()->asItem(), 50);
		$this->register(VanillaBlocks::DRIED_KELP()->asItem(), 50);
		$this->register(VanillaBlocks::FLOWERING_AZALEA_LEAVES()->asItem(), 50);
		$this->register(VanillaBlocks::GLOW_LICHEN()->asItem(), 50);
		$this->register(VanillaItems::MELON(), 50);
		//Nether Sprouts Block
		//Nether Sprouts Item
		$this->register(VanillaBlocks::SUGARCANE()->asItem(), 50);
		$this->register(VanillaBlocks::DOUBLE_TALLGRASS()->asItem(), 50);

		$this->registerFlowers();

		$this->register(VanillaBlocks::VINES()->asItem(), 50);
		$this->register(VanillaBlocks::WEEPING_VINES()->asItem(), 50);
		$this->register(VanillaBlocks::TWISTING_VINES()->asItem(), 50);

		// region: 65% percentage compost
		$this->register(VanillaItems::APPLE(), 65);
		//Azalea
		$this->register(VanillaItems::BEETROOT(), 65);

		$this->register(VanillaBlocks::BIG_DRIPLEAF_HEAD()->asItem(), 30);
		$this->register(VanillaItems::CARROT(), 65);
		$this->register(VanillaItems::COCOA_BEANS(), 65);
		$this->register(VanillaBlocks::LARGE_FERN()->asItem(), 65);
		$this->register(VanillaBlocks::LILY_PAD()->asItem(), 65);
		$this->register(VanillaBlocks::MELON()->asItem(), 65);
		//Moss Block

		$this->register(VanillaBLOCKS::BROWN_MUSHROOM()->asItem(), 65);
		$this->register(VanillaBLOCKS::RED_MUSHROOM()->asItem(), 65);
		$this->register(VanillaBLOCKS::BROWN_MUSHROOM_BLOCK()->asItem(), 65);
		$this->register(VanillaBLOCKS::RED_MUSHROOM_BLOCK()->asItem(), 65);

		$this->register(VanillaBlocks::NETHER_WART()->asItem(), 65);
		$this->register(VanillaItems::POTATO(), 65);
		$this->register(VanillaBlocks::PUMPKIN()->asItem(), 65);
		$this->register(VanillaBlocks::CARVED_PUMPKIN()->asItem(), 65);
		$this->register(VanillaBlocks::SEA_PICKLE()->asItem(), 65);
		$this->register(VanillaBlocks::SHROOMLIGHT()->asItem(), 65);
		$this->register(VanillaBlocks::SPORE_BLOSSOM()->asItem(), 65);
		$this->register(VanillaItems::WHEAT(), 65);
		//Crimson fungus
		//Warped fungus
		//Crimson roots
		//Warped roots

		// region: 85% percentage compost
		$this->register(VanillaItems::BAKED_POTATO(), 85);
		$this->register(VanillaItems::BREAD(), 85);
		$this->register(VanillaItems::COOKIE(), 85);
		//Flowering Azalea
		$this->register(VanillaBlocks::HAY_BALE()->asItem(), 85);
		$this->register(VanillaBlocks::NETHER_WART_BLOCK()->asItem(), 85);
		//Pitcher Plant
		//Torchflower
		$this->register(VanillaBlocks::WARPED_WART_BLOCK()->asItem(), 85);

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

	public function register(Item $item, int $percentage, bool $overwrite = false) : bool{
		if($percentage < 0 || $percentage > 100){
			throw new \InvalidArgumentException("Percentage must be in range 0 to 100");
		}
		$typeId = $item->getTypeId();
		if(($overwrite || !isset($this->list[$typeId])) && !$item->isNull()){
			$this->list[$typeId] = $percentage;
			return true;
		}
		return false;
	}

	public function isCompostable(Item $item) : bool{
		return !$item->isNull() && isset($this->list[$item->getTypeId()]);
	}

	/**
	 * Returns the percentage of an item, return 0 when the percentage doesn't exist.
	 */
	public function getPercentage(Item $item) : ?int{
		return $this->list[$item->getTypeId()] ?? null;
	}
}
