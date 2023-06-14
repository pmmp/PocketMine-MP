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

use pocketmine\block\utils\DripleafTiltType;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\DripleafTiltDownSound;
use pocketmine\world\sound\DripleafTiltUpSound;

class BigDripleaf extends Transparent{
	use HorizontalFacingTrait;

	protected DripleafTiltType $tilt;
	protected bool $head = false;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo){
		$this->tilt = DripleafTiltType::NONE();
		parent::__construct($idInfo, $name, $typeInfo);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->dripleafTiltType($this->tilt);
		$w->bool($this->head);
	}

	/** @return $this */
	public function setTilt(DripleafTiltType $tilt) : self{
		$this->tilt = $tilt;
		return $this;
	}

	public function getTilt() : DripleafTiltType{
		return $this->tilt;
	}

	/** @return $this */
	public function setHead(bool $head) : self{
		$this->head = $head;
		return $this;
	}

	public function isHead() : bool{
		return $this->head;
	}

	private function canBeSupportedBy(Block $block) : bool{
		//TODO: Moss block
		return
			$block->hasSameTypeId($this) ||
			$block->getTypeId() === BlockTypeIds::CLAY ||
			$block->hasTypeTag(BlockTypeTags::DIRT) ||
			$block->hasTypeTag(BlockTypeTags::MUD);
	}

	public function onNearbyBlockChange() : void{
		if(
			!$this->head && !$this->getSide(Facing::UP)->hasSameTypeId($this) ||
			!$this->canBeSupportedBy($this->getSide(Facing::DOWN))
		){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$block = $blockReplace->getSide(Facing::DOWN);
		if(!$this->canBeSupportedBy($block)){
			return false;
		}
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}
		if($block instanceof BigDripleaf && $block->hasSameTypeId($this)){
			$this->facing = $block->getFacing();
			$tx->addBlock($block->getPosition(), (clone $block)->setHead(false));
		}
		$this->head = true;
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer && $this->grow($player)){
			$item->pop();
			return true;
		}
		return false;
	}

	private function seekToHead() : ?BigDripleaf{
		if($this->head){
			return $this;
		}
		$step = 1;
		while(($next = $this->getSide(Facing::UP, $step)) instanceof BigDripleaf && $next->hasSameTypeId($this)){
			if($next->isHead()){
				return $next;
			}
			$step++;
		}
		return null;
	}

	private function grow(?Player $player) : bool{
		$head = $this->seekToHead();
		if($head === null){
			return false;
		}
		$pos = $head->getPosition();
		$up = $pos->up();
		$world = $pos->getWorld();
		if(
			!$world->isInWorld($up->getFloorX(), $up->getFloorY(), $up->getFloorZ()) ||
			$world->getBlock($up)->getTypeId() !== BlockTypeIds::AIR
		){
			return false;
		}

		$tx = new BlockTransaction($world);

		$tx->addBlock($pos, (clone $head)->setHead(false));
		$tx->addBlock($up, VanillaBlocks::BIG_DRIPLEAF()
			->setFacing($head->getFacing())
			->setHead(true)
		);

		$ev = new StructureGrowEvent($head, $tx, $player);
		$ev->call();

		if(!$ev->isCancelled()){
			return $tx->apply();
		}
		return false;
	}

	private function setTiltAndScheduleTick(DripleafTiltType $tilt) : void{
		$this->position->getWorld()->setBlock($this->position, $this->setTilt($tilt));
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, $tilt->getUpdateTicks());
	}

	public function onEntityInside(Entity $entity) : bool{
		if(!$entity instanceof Living || !$this->head || !$this->tilt->equals(DripleafTiltType::NONE())){
			return false;
		}
		$this->setTiltAndScheduleTick(DripleafTiltType::UNSTABLE());
		return true;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		if($this->head){
			$this->setTiltAndScheduleTick(DripleafTiltType::FULL());
			$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
		}
	}

	public function onScheduledUpdate() : void{
		if($this->head && !$this->tilt->equals(DripleafTiltType::NONE())){
			if($this->tilt->equals(DripleafTiltType::FULL())){
				$this->position->getWorld()->setBlock($this->position, $this->setTilt(DripleafTiltType::NONE()));
				$this->position->getWorld()->addSound($this->position, new DripleafTiltUpSound());
			}else{
				$this->setTiltAndScheduleTick(match($this->tilt->id()) {
					DripleafTiltType::UNSTABLE()->id() => DripleafTiltType::PARTIAL(),
					DripleafTiltType::PARTIAL()->id() => DripleafTiltType::FULL(),
					default => throw new AssumptionFailedError("All types should be covered")
				});
				$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
			}
		}
	}

	protected function recalculateCollisionBoxes() : array{
		if(!$this->head || $this->tilt->equals(DripleafTiltType::FULL())){
			return [];
		}
		return [
			AxisAlignedBB::one()
			->trim(Facing::DOWN, 11 / 16)
			->trim(Facing::UP, match($this->tilt->id()) {
				DripleafTiltType::NONE()->id(), DripleafTiltType::UNSTABLE()->id() => 1 / 16,
				DripleafTiltType::PARTIAL()->id() => 3 / 16,
				default => throw new AssumptionFailedError("All types should be covered")
			})
		];
	}

	public function isFlammable() : bool{
		return true;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}
}
