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
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\DoorSound;

class Trapdoor extends Transparent{

	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $open = false;
	/** @var bool */
	protected $top = false;

	protected function writeStateToMeta() : int{
		return (5 - $this->facing) | ($this->top ? BlockLegacyMetadata::TRAPDOOR_FLAG_UPPER : 0) | ($this->open ? BlockLegacyMetadata::TRAPDOOR_FLAG_OPEN : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		//TODO: in PC the values are reversed (facing - 2)

		$this->facing = BlockDataValidator::read5MinusHorizontalFacing($stateMeta);
		$this->top = ($stateMeta & BlockLegacyMetadata::TRAPDOOR_FLAG_UPPER) !== 0;
		$this->open = ($stateMeta & BlockLegacyMetadata::TRAPDOOR_FLAG_OPEN) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->trim($this->open ? $this->facing : ($this->top ? Facing::DOWN : Facing::UP), 13 / 16);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}
		if(($clickVector->y > 0.5 and $face !== Facing::UP) or $face === Facing::DOWN){
			$this->top = true;
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->open = !$this->open;
		$this->world->setBlock($this, $this);
		$this->world->addSound($this, new DoorSound());
		return true;
	}
}
