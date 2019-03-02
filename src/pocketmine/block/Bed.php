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
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Bed as ItemBed;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\lang\TranslationContainer;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Bed as TileBed;
use pocketmine\utils\TextFormat;

class Bed extends Transparent{
	private const BITFLAG_OCCUPIED = 0x04;
	private const BITFLAG_HEAD = 0x08;

	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $occupied = false;
	/** @var bool */
	protected $head = false;
	/** @var DyeColor */
	protected $color;

	public function __construct(BlockIdentifier $idInfo, string $name){
		parent::__construct($idInfo, $name);
		$this->color = DyeColor::RED();
	}

	protected function writeStateToMeta() : int{
		return Bearing::fromFacing($this->facing) |
			($this->occupied ? self::BITFLAG_OCCUPIED : 0) |
			($this->head ? self::BITFLAG_HEAD : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataValidator::readLegacyHorizontalFacing($stateMeta & 0x03);
		$this->occupied = ($stateMeta & self::BITFLAG_OCCUPIED) !== 0;
		$this->head = ($stateMeta & self::BITFLAG_HEAD) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		//read extra state information from the tile - this is an ugly hack
		$tile = $this->level->getTile($this);
		if($tile instanceof TileBed){
			$this->color = $tile->getColor();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		//extra block properties storage hack
		$tile = $this->level->getTile($this);
		if($tile instanceof TileBed){
			$tile->setColor($this->color);
		}
	}

	public function getHardness() : float{
		return 0.2;
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->trim(Facing::UP, 7 / 16);
	}

	public function isHeadPart() : bool{
		return $this->head;
	}

	/**
	 * @return bool
	 */
	public function isOccupied() : bool{
		return $this->occupied;
	}

	public function setOccupied(bool $occupied = true) : void{
		$this->occupied = $occupied;
		$this->level->setBlock($this, $this, false);

		if(($other = $this->getOtherHalf()) !== null){
			$other->occupied = $occupied;
			$this->level->setBlock($other, $other, false);
		}
	}

	/**
	 * @return int
	 */
	private function getOtherHalfSide() : int{
		return $this->head ? Facing::opposite($this->facing) : $this->facing;
	}

	/**
	 * @return Bed|null
	 */
	public function getOtherHalf() : ?Bed{
		$other = $this->getSide($this->getOtherHalfSide());
		if($other instanceof Bed and $other->head !== $this->head and $other->facing === $this->facing){
			return $other;
		}

		return null;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$other = $this->getOtherHalf();
			if($other === null){
				$player->sendMessage(TextFormat::GRAY . "This bed is incomplete");

				return true;
			}elseif($player->distanceSquared($this) > 4 and $player->distanceSquared($other) > 4){
				$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.bed.tooFar"));
				return true;
			}

			$time = $this->getLevel()->getTime() % Level::TIME_FULL;

			$isNight = ($time >= Level::TIME_NIGHT and $time < Level::TIME_SUNRISE);

			if(!$isNight){
				$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.bed.noSleep"));

				return true;
			}

			$b = ($this->isHeadPart() ? $this : $other);

			if($b->isOccupied()){
				$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.bed.occupied"));

				return true;
			}

			$player->sleepOn($b);
		}

		return true;

	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof ItemBed){ //TODO: the item should do this
			$this->color = $item->getColor();
		}
		$down = $this->getSide(Facing::DOWN);
		if(!$down->isTransparent()){
			$this->facing = $player !== null ? $player->getHorizontalFacing() : Facing::NORTH;

			$next = $this->getSide($this->getOtherHalfSide());
			if($next->canBeReplaced() and !$next->getSide(Facing::DOWN)->isTransparent()){
				parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
				$nextState = clone $this;
				$nextState->head = true;
				$this->getLevel()->setBlock($next, $nextState);

				return true;
			}
		}

		return false;
	}

	public function getDrops(Item $item) : array{
		if($this->head){
			return parent::getDrops($item);
		}

		return [];
	}

	public function asItem() : Item{
		return ItemFactory::get($this->idInfo->getItemId(), $this->color->getMagicNumber());
	}

	public function getAffectedBlocks() : array{
		if(($other = $this->getOtherHalf()) !== null){
			return [$this, $other];
		}

		return parent::getAffectedBlocks();
	}
}
