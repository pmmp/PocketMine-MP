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

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\block\utils\Fallable;
use pocketmine\block\utils\FallableTrait;
use pocketmine\inventory\AnvilInventory;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Anvil extends Transparent implements Fallable{
	use FallableTrait;

	public const TYPE_NORMAL = 0;
	public const TYPE_SLIGHTLY_DAMAGED = 4;
	public const TYPE_VERY_DAMAGED = 8;

	/** @var int */
	protected $facing = Facing::NORTH;

	protected function writeStateToMeta() : int{
		return Bearing::fromFacing($this->facing);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataValidator::readLegacyHorizontalFacing($stateMeta);
	}

	public function getStateBitmask() : int{
		return 0b11;
	}

	public function getHardness() : float{
		return 5;
	}

	public function getBlastResistance() : float{
		return 6000;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->squash(Facing::axis(Facing::rotateY($this->facing, false)), 1 / 8);
	}

	public function onActivate(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$player->addWindow(new AnvilInventory($this));
		}

		return true;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = Facing::rotateY($player->getHorizontalFacing(), true);
		}
		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function tickFalling() : ?Block{
		return null;
	}
}
