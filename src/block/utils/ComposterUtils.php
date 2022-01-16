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
use pocketmine\item\Apple;
use pocketmine\item\BakedPotato;
use pocketmine\item\BeetrootSeeds;
use pocketmine\item\Bread;
use pocketmine\item\Carrot;
use pocketmine\item\CocoaBeans;
use pocketmine\item\Cookie;
use pocketmine\item\DriedKelp;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\Melon;
use pocketmine\item\MelonSeeds;
use pocketmine\item\Potato;
use pocketmine\item\PumpkinSeeds;
use pocketmine\item\SweetBerries;
use pocketmine\item\WheatSeeds;
use function count;

class ComposterUtils {
    /** @param mixed[] $list */
    private static array $list = [];

    public function __construct() {
        self::registryDefault();
    }

    public static function registryDefault() : void{
        // region 30% percentage compost
        self::register(new BeetrootSeeds(new ItemIdentifier(ItemIds::BEETROOT_SEEDS, 0), "Beetroot Seeds"));
        self::register(new DriedKelp(new ItemIdentifier(ItemIds::DRIED_KELP, 0), "Dried Kelp"));
        //Todo: add glow berry (?:?)
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 0), VanillaBlocks::TALL_GRASS()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 1), VanillaBlocks::TALL_GRASS()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::GRASS, 0), VanillaBlocks::GRASS()));
        //Todo: add hanging roots (574:0)
        //Todo: add kelp (335:0)

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 0), VanillaBlocks::OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 1), VanillaBlocks::SPRUCE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 2), VanillaBlocks::BIRCH_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 3), VanillaBlocks::JUNGLE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 4), VanillaBlocks::OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 5), VanillaBlocks::SPRUCE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 6), VanillaBlocks::BIRCH_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 7), VanillaBlocks::JUNGLE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 8), VanillaBlocks::OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 9), VanillaBlocks::SPRUCE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 10), VanillaBlocks::BIRCH_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 11), VanillaBlocks::JUNGLE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 12), VanillaBlocks::OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 13), VanillaBlocks::SPRUCE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 14), VanillaBlocks::BIRCH_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 15), VanillaBlocks::JUNGLE_LEAVES()));

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 0), VanillaBlocks::ACACIA_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 1), VanillaBlocks::DARK_OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 4), VanillaBlocks::ACACIA_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 5), VanillaBlocks::DARK_OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 8), VanillaBlocks::ACACIA_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 9), VanillaBlocks::DARK_OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 12), VanillaBlocks::ACACIA_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 13), VanillaBlocks::DARK_OAK_LEAVES()));

        self::register(new MelonSeeds(new ItemIdentifier(ItemIds::MELON_SEEDS, 0), "Melon Seeds"));
        //Todo: add moss carpet (590:0)

        self::register(new PumpkinSeeds(new ItemIdentifier(ItemIds::PUMPKIN_SEEDS, 0), "Pumpkin Seeds"));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 0), VanillaBlocks::OAK_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 1), VanillaBlocks::SPRUCE_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 2), VanillaBlocks::BIRCH_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 3), VanillaBlocks::JUNGLE_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 4), VanillaBlocks::ACACIA_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 5), VanillaBlocks::DARK_OAK_SAPLING()));

        //Todo: add sea grass
        //Todo: add small dripleaf
        self::register(new SweetBerries(new ItemIdentifier(ItemIds::SWEET_BERRIES, 0), "Sweet Berry"));
        self::register(new WheatSeeds(new ItemIdentifier(ItemIds::WHEAT_SEEDS, 0), "Wheat Seeds"));

        // region 50% percentage compost
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::CACTUS, 0), VanillaBlocks::CACTUS()), 50);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DRIED_KELP_BLOCK, 0), VanillaBlocks::DRIED_KELP()), 50);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DRIED_KELP_BLOCK, 0), VanillaBlocks::DRIED_KELP()), 50);
        //Todo: add Flowering Azalea Leaves and Glow Lichen
        self::register(new Melon(new ItemIdentifier(ItemIds::MELON_SLICE, 0), "Melon slice"), 50);
        //Todo: add Nether Sprouts
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SUGARCANE, 0), VanillaBlocks::SUGARCANE()), 50);
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

        // region 65% percentage compost
        self::register(new Apple(new ItemIdentifier(ItemIds::APPLE, 0), "Apple"), 65);
        //Todo: add Azalea

        //Todo: add Big Dripleaf
        self::register(new Carrot(new ItemIdentifier(ItemIds::CARROT, 0), "Carrot"), 65);
        self::register(new CocoaBeans(new ItemIdentifier(351, 3), "Cocoa Beans"), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 2), VanillaBlocks::TALL_GRASS()), 65); //Fern
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 3), VanillaBlocks::TALL_GRASS()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 3), VanillaBlocks::LARGE_FERN()), 65);

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 0), VanillaBlocks::SUNFLOWER()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 1), VanillaBlocks::LILAC()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 4), VanillaBlocks::ROSE_BUSH()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::DOUBLE_PLANT, 5), VanillaBlocks::PEONY()), 65);

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LILY_PAD, 0), VanillaBlocks::LILY_PAD()), 65);

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::MELON_BLOCK, 0), VanillaBlocks::MELON()), 65);
        //Todo: add moss block

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM, 0), VanillaBlocks::BROWN_MUSHROOM()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM, 0), VanillaBlocks::RED_MUSHROOM()));

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::BROWN_MUSHROOM_BLOCK, 15), VanillaBlocks::BROWN_MUSHROOM_BLOCK()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::RED_MUSHROOM_BLOCK, 15), VanillaBlocks::RED_MUSHROOM_BLOCK()), 65);

        self::register(new Item(new ItemIdentifier(ItemIds::NETHER_WART, 0), "Nether Wart"), 65);

        self::register(new Potato(new ItemIdentifier(ItemIds::POTATO, 0), "Potato"), 65);

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::PUMPKIN, 0), VanillaBlocks::PUMPKIN()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::PUMPKIN, 1), VanillaBlocks::PUMPKIN()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::PUMPKIN, 2), VanillaBlocks::PUMPKIN()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::PUMPKIN, 3), VanillaBlocks::PUMPKIN()), 65);

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::CARVED_PUMPKIN, 0), VanillaBlocks::CARVED_PUMPKIN()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::CARVED_PUMPKIN, 1), VanillaBlocks::CARVED_PUMPKIN()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::CARVED_PUMPKIN, 2), VanillaBlocks::CARVED_PUMPKIN()), 65);
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::CARVED_PUMPKIN, 3), VanillaBlocks::CARVED_PUMPKIN()), 65);

        self::register(new ItemBlock(new ItemIdentifier(411, 0), VanillaBlocks::SEA_PICKLE()), 65);
        //Todo: add Shroomlight, Spore Blossom
        self::register(new Item(new ItemIdentifier(ItemIds::WHEAT, 0), "Wheat"), 65);
        //Todo: add Fungus, Roots

        // region 85% percentage compost
        self::register(new BakedPotato(new ItemIdentifier(ItemIds::BAKED_POTATO, 0), "Baked Potato"), 85);
        self::register(new Bread(new ItemIdentifier(ItemIds::BREAD, 0), "Bread"), 85);
        self::register(new Cookie(new ItemIdentifier(ItemIds::COOKIE, 0), "Cookie"), 85);
        //Todo: add Flowering Azalea
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::WHEAT_BLOCK, 0), VanillaBlocks::HAY_BALE()), 85);

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

        // region 100% percentage compost
        self::register(new Item(new ItemIdentifier(ItemIds::CAKE, 0), "Cake"), 100);
        self::register(new Item(new ItemIdentifier(ItemIds::PUMPKIN_PIE, 0), "Pumpkin Pie"), 100);
    }

    public static function register(Item $item, int $percentage = 30, bool $overwrite = false) : bool{
        $fullId = self::getListOffset($item->getId(), $item->getMeta());
        if (!isset(self::$list[$fullId]) || $overwrite) {
            self::$list[$fullId] = $percentage;
        }
        return false;
    }

    public static function isCompostable(Item $item) : bool{
        if (count(self::$list) === 0) {
            self::registryDefault();
        }
        return !$item->isNull() && isset(self::$list[self::getListOffset($item->getId(), $item->getMeta())]);
    }

    public static function getPercentage(Item $item) : int{
        if (count(self::$list) === 0) {
            self::registryDefault();
        }
        return self::$list[self::getListOffset($item->getId(), $item->getMeta())] ?? 0;
    }

    public static function getListOffset(int $id, int $variant = 0) : int{
        if($id < -0x8000 or $id > 0x7fff){
            throw new \InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
        }
        return (($id & 0xffff) << 16) | ($variant & 0xffff);
    }
}
