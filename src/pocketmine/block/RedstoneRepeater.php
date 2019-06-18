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
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class RedstoneRepeater extends Flowable{
	/** @var BlockIdentifierFlattened */
	protected $idInfo;

	/** @var bool */
	protected $powered = false;
	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var int */
	protected $delay = 1;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? BlockBreakInfo::instant());
	}

	public function getId() : int{
		return $this->powered ? $this->idInfo->getSecondId() : parent::getId();
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataValidator::readLegacyHorizontalFacing($stateMeta & 0x03);
		$this->delay = BlockDataValidator::readBoundedInt("delay", ($stateMeta >> 2) + 1, 1, 4);
		$this->powered = $id === $this->idInfo->getSecondId();
	}

	public function writeStateToMeta() : int{
		return Bearing::fromFacing($this->facing) | (($this->delay - 1) << 2);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->trim(Facing::UP, 7 / 8);
	}

	public function isPowered() : bool{
		return $this->powered;
	}

	/**
	 * @param bool $powered
	 *
	 * @return $this
	 */
	public function setPowered(bool $powered = true) : self{
		$this->powered = $powered;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$blockReplace->getSide(Facing::DOWN)->isTransparent()){
			if($player !== null){
				$this->facing = Facing::opposite($player->getHorizontalFacing());
			}

			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(++$this->delay > 4){
			$this->delay = 1;
		}
		$this->world->setBlock($this, $this);
		return true;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			$this->world->useBreakOn($this);
		}
	}

	//TODO: redstone functionality
}
