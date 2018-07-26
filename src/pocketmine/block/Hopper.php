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

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\TieredTool;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Hopper as TileHopper;
use pocketmine\tile\Tile;

class Hopper extends Transparent{

    protected $id = self::HOPPER_BLOCK;
    protected $itemId = Item::HOPPER;

    public function __construct(int $meta = 0){
        $this->meta = $meta;
    }

    public function getHardness(): float{
        return 3;
    }

    public function getBlastResistance(): float{
        return 24;
    }

    public function getToolType(): int{
        return BlockToolType::TYPE_PICKAXE;
    }

    public function getToolHarvestLevel(): int{
        return TieredTool::TIER_WOODEN;
    }

    public function getName(): string{
        return "Hopper";
    }

    public function onActivate(Item $item, Player $player = null): bool{
        if($player instanceof Player){

            $t = $this->getLevel()->getTile($this);
            if($t instanceof TileHopper){
                $hopper = $t;
            }else{
                $hopper = Tile::createTile(Tile::HOPPER, $this->getLevel(), TileHopper::createNBT($this));
            }

            if(!$hopper->canOpenWith($item->getCustomName())){
                return true;
            }

            $player->addWindow($hopper->getInventory());
        }

        return true;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null): bool{
        $faces = [
            0 => Vector3::SIDE_DOWN,
            1 => Vector3::SIDE_DOWN, // Not used
            2 => Vector3::SIDE_SOUTH,
            3 => Vector3::SIDE_NORTH,
            4 => Vector3::SIDE_EAST,
            5 => Vector3::SIDE_WEST
        ];
        $this->meta = $faces[$face];

        $this->getLevel()->setBlock($this, $this, true, false);
        Tile::createTile(Tile::HOPPER, $this->getLevel(), TileHopper::createNBT($this, $face, $item, $player));
        return true;
    }

    // TODO : ADD REDSTONE
}