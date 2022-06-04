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
use pocketmine\block\utils\ColoredTrait;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\BlockTransaction;
use pocketmine\world\World;

class Bed extends Transparent{
	use ColoredTrait;
	use HorizontalFacingTrait;

	protected bool $occupied = false;
	protected bool $head = false;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->color = DyeColor::RED();
		parent::__construct($idInfo, $name, $breakInfo);
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
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileBed){
			$this->color = $tile->getColor();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		//extra block properties storage hack
		$tile = $this->position->getWorld()->getTile($this->position);
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

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	public function isHeadPart() : bool{
		return $this->head;
	}

	/** @return $this */
	public function setHead(bool $head) : self{
		$this->head = $head;
		return $this;
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
		if($other instanceof Bed && $other->head !== $this->head && $other->facing === $this->facing){
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
			}elseif($playerPos->distanceSquared($this->position) > 4 && $playerPos->distanceSquared($other->position) > 4){
				$player->sendMessage(KnownTranslationFactory::tile_bed_tooFar()->prefix(TextFormat::GRAY));
				return true;
			}

			$time = $this->position->getWorld()->getTimeOfDay();

			$isNight = ($time >= World::TIME_NIGHT && $time < World::TIME_SUNRISE);

			if(!$isNight){
				$player->sendMessage(KnownTranslationFactory::tile_bed_noSleep()->prefix(TextFormat::GRAY));

				return true;
			}

			$b = ($this->isHeadPart() ? $this : $other);

			if($b->isOccupied()){
				$player->sendMessage(KnownTranslationFactory::tile_bed_occupied()->prefix(TextFormat::GRAY));

				return true;
			}

			$player->sleepOn($b->position);
		}

		return true;

	}

	public function onNearbyBlockChange() : void{
		if(!$this->head && ($other = $this->getOtherHalf()) !== null && $other->occupied !== $this->occupied){
			$this->occupied = $other->occupied;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}

	public function onEntityLand(Entity $entity) : ?float{
		if($entity instanceof Living && $entity->isSneaking()){
			return null;
		}
		$entity->fallDistance *= 0.5;
		return $entity->getMotion()->y * -3 / 4; // 2/3 in Java, according to the wiki
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->facing = $player !== null ? $player->getHorizontalFacing() : Facing::NORTH;

			$next = $this->getSide($this->getOtherHalfSide());
			if($next->canBeReplaced() && $this->canBeSupportedBy($next->getSide(Facing::DOWN))){
				$nextState = clone $this;
				$nextState->head = true;
				$tx->addBlock($blockReplace->position, $this)->addBlock($next->position, $nextState);
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

	protected function writeStateToItemMeta() : int{
		return DyeColorIdMap::getInstance()->toId($this->color);
	}

	public function getAffectedBlocks() : array{
		if(($other = $this->getOtherHalf()) !== null){
			return [$this, $other];
		}

		return parent::getAffectedBlocks();
	}

	private function canBeSupportedBy(Block $block) : bool{
		return !$block->getSupportType(Facing::UP)->equals(SupportType::NONE());
	}
}
