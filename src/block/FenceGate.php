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

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\DoorSound;

class FenceGate extends Transparent{
	use HorizontalFacingTrait;

	/** @var bool */
	protected $open = false;
	/** @var bool */
	protected $inWall = false;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(2.0, BlockToolType::AXE));
	}

	protected function writeStateToMeta() : int{
		return BlockDataSerializer::writeLegacyHorizontalFacing($this->facing) |
			($this->open ? BlockLegacyMetadata::FENCE_GATE_FLAG_OPEN : 0) |
			($this->inWall ? BlockLegacyMetadata::FENCE_GATE_FLAG_IN_WALL : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readLegacyHorizontalFacing($stateMeta & 0x03);
		$this->open = ($stateMeta & BlockLegacyMetadata::FENCE_GATE_FLAG_OPEN) !== 0;
		$this->inWall = ($stateMeta & BlockLegacyMetadata::FENCE_GATE_FLAG_IN_WALL) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return $this->open ? [] : [AxisAlignedBB::one()->extend(Facing::UP, 0.5)->squash(Facing::axis($this->facing), 6 / 16)];
	}

	private function checkInWall() : bool{
		return (
			$this->getSide(Facing::rotateY($this->facing, false)) instanceof Wall or
			$this->getSide(Facing::rotateY($this->facing, true)) instanceof Wall
		);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = $player->getHorizontalFacing();
		}

		$this->inWall = $this->checkInWall();

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$inWall = $this->checkInWall();
		if($inWall !== $this->inWall){
			$this->inWall = $inWall;
			$this->pos->getWorld()->setBlock($this->pos, $this);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->open = !$this->open;
		if($this->open and $player !== null){
			$playerFacing = $player->getHorizontalFacing();
			if($playerFacing === Facing::opposite($this->facing)){
				$this->facing = $playerFacing;
			}
		}

		$this->pos->getWorld()->setBlock($this->pos, $this);
		$this->pos->getWorld()->addSound($this->pos, new DoorSound());
		return true;
	}

	public function getFuelTime() : int{
		return 300;
	}

	public function getFlameEncouragement() : int{
		return 5;
	}

	public function getFlammability() : int{
		return 20;
	}
}
