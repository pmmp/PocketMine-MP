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

namespace pocketmine\block;

use pocketmine\item\TieredTool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

class CobblestoneWall extends Transparent{
	public const NONE_MOSSY_WALL = 0;
	public const MOSSY_WALL = 1;
	public const GRANITE_WALL = 2;
	public const DIORITE_WALL = 3;
	public const ANDESITE_WALL = 4;
	public const SANDSTONE_WALL = 5;
	public const BRICK_WALL = 6;
	public const STONE_BRICK_WALL = 7;
	public const MOSSY_STONE_BRICK_WALL = 8;
	public const NETHER_BRICK_WALL = 9;
	public const END_STONE_BRICK_WALL = 10;
	public const PRISMARINE_WALL = 11;
	public const RED_SANDSTONE_WALL = 12;
	public const RED_NETHER_BRICK_WALL = 13;

	/** @var bool[] facing => dummy */
	protected $connections = [];
	/** @var bool */
	protected $up = false;

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function getHardness() : float{
		return 2;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();

		foreach(Facing::HORIZONTAL as $facing){
			$block = $this->getSide($facing);
			if($block instanceof static or $block instanceof FenceGate or ($block->isSolid() and !$block->isTransparent())){
				$this->connections[$facing] = true;
			}else{
				unset($this->connections[$facing]);
			}
		}

		$this->up = $this->getSide(Facing::UP)->getId() !== Block::AIR;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		//walls don't have any special collision boxes like fences do

		$north = isset($this->connections[Facing::NORTH]);
		$south = isset($this->connections[Facing::SOUTH]);
		$west = isset($this->connections[Facing::WEST]);
		$east = isset($this->connections[Facing::EAST]);

		$inset = 0.25;
		if(
			!$this->up and //if there is a block on top, it stays as a post
			(
				($north and $south and !$west and !$east) or
				(!$north and !$south and $west and $east)
			)
		){
			//If connected to two sides on the same axis but not any others, AND there is not a block on top, there is no post and the wall is thinner
			$inset = 0.3125;
		}

		return new AxisAlignedBB(
			($west ? 0 : $inset),
			0,
			($north ? 0 : $inset),
			1 - ($east ? 0 : $inset),
			1.5,
			1 - ($south ? 0 : $inset)
		);
	}
}
