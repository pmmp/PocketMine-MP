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

use pocketmine\inventory\AnvilInventory;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Anvil extends Fallable{

	public const TYPE_NORMAL = 0;
	public const TYPE_SLIGHTLY_DAMAGED = 4;
	public const TYPE_VERY_DAMAGED = 8;

	protected $id = self::ANVIL;

	/** @var int */
	protected $facing = Facing::NORTH;

	/** @var int */
	protected $variant = self::TYPE_NORMAL;

	public function __construct(int $meta = 0){
		$this->setDamage($meta);
	}

	public function getDamage() : int{
		return Bearing::fromFacing($this->facing) | $this->variant;
	}

	public function setDamage(int $meta) : void{
		$this->variant = $meta & 0x0c;
		$this->facing = Bearing::toFacing($meta & 0x03);
	}

	public function getVariant() : int{
		return $this->variant;
	}

	public function getName() : string{
		static $names = [
			self::TYPE_NORMAL => "Anvil",
			self::TYPE_SLIGHTLY_DAMAGED => "Slightly Damaged Anvil",
			self::TYPE_VERY_DAMAGED => "Very Damaged Anvil"
		];
		return $names[$this->getVariant()] ?? "Anvil";
	}

	public function isTransparent() : bool{
		return true;
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
		$inset = 0.125;

		if(Facing::axis($this->facing) === Facing::AXIS_X){
			return new AxisAlignedBB(0, 0, $inset, 1, 1, 1 - $inset);
		}else{
			return new AxisAlignedBB($inset, 0, 0, 1 - $inset, 1, 1);
		}
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$player->addWindow(new AnvilInventory($this));
		}

		return true;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($player !== null){
			$this->facing = Bearing::toFacing(Bearing::rotate($player->getDirection(), 1));
		}
		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}
}
