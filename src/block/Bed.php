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

use pocketmine\block\tile\Bed as TileBed;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\item\Bed as ItemBed;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\lang\TranslationContainer;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\BlockTransaction;
use pocketmine\world\World;

class Bed extends Transparent{
	use HorizontalFacingTrait;

	/** @var bool */
	protected $occupied = false;
	/** @var bool */
	protected $head = false;
	/** @var DyeColor */
	protected $color;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(0.2));
		$this->color = DyeColor::RED();
	}

	protected function writeStateToMeta() : int{
		return BlockDataSerializer::writeLegacyHorizontalFacing($this->facing) |
			($this->occupied ? BlockLegacyMetadata::BED_FLAG_OCCUPIED : 0) |
			($this->head ? BlockLegacyMetadata::BED_FLAG_HEAD : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::readLegacyHorizontalFacing($stateMeta & 0x03);
		$this->occupied = ($stateMeta & BlockLegacyMetadata::BED_FLAG_OCCUPIED) !== 0;
		$this->head = ($stateMeta & BlockLegacyMetadata::BED_FLAG_HEAD) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		//read extra state information from the tile - this is an ugly hack
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile instanceof TileBed){
			$this->color = $tile->getColor();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		//extra block properties storage hack
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile instanceof TileBed){
			$tile->setColor($this->color);
		}
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 7 / 16)];
	}

	public function isHeadPart() : bool{
		return $this->head;
	}

	public function isOccupied() : bool{
		return $this->occupied;
	}

	/** @return $this */
	public function setOccupied(bool $occupied = true) : self{
		$this->occupied = $occupied;
		return $this;
	}

	private function getOtherHalfSide() : int{
		return $this->head ? Facing::opposite($this->facing) : $this->facing;
	}

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
			$playerPos = $player->getPosition();
			if($other === null){
				$player->sendMessage(TextFormat::GRAY . "This bed is incomplete");

				return true;
			}elseif($playerPos->distanceSquared($this->pos) > 4 and $playerPos->distanceSquared($other->pos) > 4){
				$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.bed.tooFar"));
				return true;
			}

			$time = $this->pos->getWorld()->getTimeOfDay();

			$isNight = ($time >= World::TIME_NIGHT and $time < World::TIME_SUNRISE);

			if(!$isNight){
				$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.bed.noSleep"));

				return true;
			}

			$b = ($this->isHeadPart() ? $this : $other);

			if($b->isOccupied()){
				$player->sendMessage(new TranslationContainer(TextFormat::GRAY . "%tile.bed.occupied"));

				return true;
			}

			$player->sleepOn($b->pos);
		}

		return true;

	}

	public function onNearbyBlockChange() : void{
		if(($other = $this->getOtherHalf()) !== null and $other->occupied !== $this->occupied){
			$this->occupied = $other->occupied;
			$this->pos->getWorld()->setBlock($this->pos, $this);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof ItemBed){ //TODO: the item should do this
			$this->color = $item->getColor();
		}
		$down = $this->getSide(Facing::DOWN);
		if(!$down->isTransparent()){
			$this->facing = $player !== null ? $player->getHorizontalFacing() : Facing::NORTH;

			$next = $this->getSide($this->getOtherHalfSide());
			if($next->canBeReplaced() and !$next->getSide(Facing::DOWN)->isTransparent()){
				$nextState = clone $this;
				$nextState->head = true;
				$tx->addBlock($blockReplace->pos, $this)->addBlock($next->pos, $nextState);
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
		return ItemFactory::getInstance()->get($this->idInfo->getItemId(), DyeColorIdMap::getInstance()->toId($this->color));
	}

	public function getAffectedBlocks() : array{
		if(($other = $this->getOtherHalf()) !== null){
			return [$this, $other];
		}

		return parent::getAffectedBlocks();
	}
}
