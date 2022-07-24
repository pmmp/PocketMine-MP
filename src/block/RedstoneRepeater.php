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
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class RedstoneRepeater extends Flowable{
	use HorizontalFacingTrait;
	use PoweredByRedstoneTrait;

	public const MIN_DELAY = 1;
	public const MAX_DELAY = 4;

	protected BlockIdentifierFlattened $idInfoFlattened;

	protected int $delay = self::MIN_DELAY;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->idInfoFlattened = $idInfo;
		parent::__construct($idInfo, $name, $breakInfo);
	}

	public function getId() : int{
		return $this->powered ? $this->idInfoFlattened->getSecondId() : parent::getId();
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readLegacyHorizontalFacing($stateMeta & 0x03);
		$this->delay = BlockDataSerializer::readBoundedInt("delay", ($stateMeta >> 2) + 1, self::MIN_DELAY, self::MAX_DELAY);
		$this->powered = $id === $this->idInfoFlattened->getSecondId();
	}

	public function writeStateToMeta() : int{
		return BlockDataSerializer::writeLegacyHorizontalFacing($this->facing) | (($this->delay - 1) << 2);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getDelay() : int{ return $this->delay; }

	/** @return $this */
	public function setDelay(int $delay) : self{
		if($delay < self::MIN_DELAY || $delay > self::MAX_DELAY){
			throw new \InvalidArgumentException("Delay must be in range " . self::MIN_DELAY . " ... " . self::MAX_DELAY);
		}
		$this->delay = $delay;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 7 / 8)];
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->canBeSupportedBy($blockReplace->getSide(Facing::DOWN))){
			if($player !== null){
				$this->facing = Facing::opposite($player->getHorizontalFacing());
			}

			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(++$this->delay > self::MAX_DELAY){
			$this->delay = self::MIN_DELAY;
		}
		$this->position->getWorld()->setBlock($this->position, $this);
		return true;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	private function canBeSupportedBy(Block $block) : bool{
		return !$block->getSupportType(Facing::UP)->equals(SupportType::NONE());
	}

	//TODO: redstone functionality
}
