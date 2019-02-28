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
use pocketmine\item\Item;
use pocketmine\level\sound\DoorSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Trapdoor extends Transparent{
	private const MASK_UPPER = 0x04;
	private const MASK_OPENED = 0x08;

	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $open = false;
	/** @var bool */
	protected $top = false;

	protected function writeStateToMeta() : int{
		return (5 - $this->facing) | ($this->top ? self::MASK_UPPER : 0) | ($this->open ? self::MASK_OPENED : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		//TODO: in PC the values are reversed (facing - 2)

		$this->facing = BlockDataValidator::readHorizontalFacing(5 - ($stateMeta & 0x03));
		$this->top = ($stateMeta & self::MASK_UPPER) !== 0;
		$this->open = ($stateMeta & self::MASK_OPENED) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getHardness() : float{
		return 3;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->trim($this->open ? $this->facing : ($this->top ? Facing::DOWN : Facing::UP), 13 / 16);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}
		if(($clickVector->y > 0.5 and $face !== Facing::UP) or $face === Facing::DOWN){
			$this->top = true;
		}

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->open = !$this->open;
		$this->level->setBlock($this, $this);
		$this->level->addSound($this, new DoorSound());
		return true;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function getFuelTime() : int{
		return 300;
	}
}
