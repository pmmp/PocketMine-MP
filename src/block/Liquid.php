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
use pocketmine\block\utils\MinimumCostFlowCalculator;
use pocketmine\block\utils\SupportType;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\world\sound\FizzSound;
use pocketmine\world\sound\Sound;
use function lcg_value;

abstract class Liquid extends Transparent{
	public const MAX_DECAY = 7;

	protected BlockIdentifierFlattened $idInfoFlattened;

	public int $adjacentSources = 0;

	protected ?Vector3 $flowVector = null;

	protected bool $falling = false;
	protected int $decay = 0; //PC "level" property
	protected bool $still = false;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->idInfoFlattened = $idInfo;
		parent::__construct($idInfo, $name, $breakInfo);
	}

	public function getId() : int{
		return $this->still ? $this->idInfoFlattened->getSecondId() : parent::getId();
	}

	protected function writeStateToMeta() : int{
		return $this->decay | ($this->falling ? BlockLegacyMetadata::LIQUID_FLAG_FALLING : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->decay = BlockDataSerializer::readBoundedInt("decay", $stateMeta & 0x07, 0, self::MAX_DECAY);
		$this->falling = ($stateMeta & BlockLegacyMetadata::LIQUID_FLAG_FALLING) !== 0;
		$this->still = $id === $this->idInfoFlattened->getSecondId();
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function isFalling() : bool{ return $this->falling; }

	/** @return $this */
	public function setFalling(bool $falling) : self{
		$this->falling = $falling;
		return $this;
	}

	public function getDecay() : int{ return $this->decay; }

	/** @return $this */
	public function setDecay(int $decay) : self{
		if($decay < 0 || $decay > self::MAX_DECAY){
			throw new \InvalidArgumentException("Decay must be in range 0 ... " . self::MAX_DECAY);
		}
		$this->decay = $decay;
		return $this;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function canBeReplaced() : bool{
		return true;
	}

	public function canBeFlowedInto() : bool{
		return true;
	}

	public function isSolid() : bool{
		return false;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function getStillForm() : Block{
		$b = clone $this;
		$b->still = true;
		return $b;
	}

	public function getFlowingForm() : Block{
		$b = clone $this;
		$b->still = false;
		return $b;
	}

	abstract public function getBucketFillSound() : Sound;

	abstract public function getBucketEmptySound() : Sound;

	public function isSource() : bool{
		return !$this->falling && $this->decay === 0;
	}

	/**
	 * @return float
	 */
	public function getFluidHeightPercent(){
		return (($this->falling ? 0 : $this->decay) + 1) / 9;
	}

	public function isStill() : bool{
		return $this->still;
	}

	/**
	 * @return $this
	 */
	public function setStill(bool $still = true) : self{
		$this->still = $still;
		return $this;
	}

	protected function getEffectiveFlowDecay(Block $block) : int{
		if(!($block instanceof Liquid) || !$block->isSameType($this)){
			return -1;
		}

		return $block->falling ? 0 : $block->decay;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$this->flowVector = null;
	}

	public function getFlowVector() : Vector3{
		if($this->flowVector !== null){
			return $this->flowVector;
		}

		$vX = $vY = $vZ = 0;

		$decay = $this->getEffectiveFlowDecay($this);

		$world = $this->position->getWorld();

		foreach(Facing::HORIZONTAL as $j){
			$x = $this->position->x;
			$y = $this->position->y;
			$z = $this->position->z;

			match($j){
				Facing::WEST => --$x,
				Facing::EAST => ++$x,
				Facing::NORTH => --$z,
				Facing::SOUTH => ++$z
			};

			$sideBlock = $world->getBlockAt($x, $y, $z);
			$blockDecay = $this->getEffectiveFlowDecay($sideBlock);

			if($blockDecay < 0){
				if(!$sideBlock->canBeFlowedInto()){
					continue;
				}

				$blockDecay = $this->getEffectiveFlowDecay($world->getBlockAt($x, $y - 1, $z));

				if($blockDecay >= 0){
					$realDecay = $blockDecay - ($decay - 8);
					$vX += ($x - $this->position->x) * $realDecay;
					$vY += ($y - $this->position->y) * $realDecay;
					$vZ += ($z - $this->position->z) * $realDecay;
				}

				continue;
			}else{
				$realDecay = $blockDecay - $decay;
				$vX += ($x - $this->position->x) * $realDecay;
				$vY += ($y - $this->position->y) * $realDecay;
				$vZ += ($z - $this->position->z) * $realDecay;
			}
		}

		$vector = new Vector3($vX, $vY, $vZ);

		if($this->falling){
			foreach(Facing::HORIZONTAL as $facing){
				$pos = $this->position->getSide($facing);
				if(
					!$this->canFlowInto($world->getBlockAt($pos->x, $pos->y, $pos->z)) ||
					!$this->canFlowInto($world->getBlockAt($pos->x, $pos->y + 1, $pos->z))
				){
					$vector = $vector->normalize()->add(0, -6, 0);
					break;
				}
			}
		}

		return $this->flowVector = $vector->normalize();
	}

	public function addVelocityToEntity(Entity $entity) : ?Vector3{
		if($entity->canBeMovedByCurrents()){
			return $this->getFlowVector();
		}
		return null;
	}

	abstract public function tickRate() : int;

	/**
	 * Returns how many liquid levels are lost per block flowed horizontally. Affects how far the liquid can flow.
	 */
	public function getFlowDecayPerBlock() : int{
		return 1;
	}

	/**
	 * Returns the number of source blocks of this liquid that must be horizontally adjacent to this block in order for
	 * this block to become a source block itself, or null if the liquid does not exhibit source-forming behaviour.
	 */
	public function getMinAdjacentSourcesToFormSource() : ?int{
		return null;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->checkForHarden()){
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, $this->tickRate());
		}
	}

	public function onScheduledUpdate() : void{
		$multiplier = $this->getFlowDecayPerBlock();

		$world = $this->position->getWorld();

		if(!$this->isSource()){
			$smallestFlowDecay = -100;
			$this->adjacentSources = 0;
			$smallestFlowDecay = $this->getSmallestFlowDecay($world->getBlockAt($this->position->x, $this->position->y, $this->position->z - 1), $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($world->getBlockAt($this->position->x, $this->position->y, $this->position->z + 1), $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($world->getBlockAt($this->position->x - 1, $this->position->y, $this->position->z), $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($world->getBlockAt($this->position->x + 1, $this->position->y, $this->position->z), $smallestFlowDecay);

			$newDecay = $smallestFlowDecay + $multiplier;
			$falling = false;

			if($newDecay > self::MAX_DECAY || $smallestFlowDecay < 0){
				$newDecay = -1;
			}

			if($this->getEffectiveFlowDecay($world->getBlockAt($this->position->x, $this->position->y + 1, $this->position->z)) >= 0){
				$falling = true;
			}

			$minAdjacentSources = $this->getMinAdjacentSourcesToFormSource();
			if($minAdjacentSources !== null && $this->adjacentSources >= $minAdjacentSources){
				$bottomBlock = $world->getBlockAt($this->position->x, $this->position->y - 1, $this->position->z);
				if($bottomBlock->isSolid() || ($bottomBlock instanceof Liquid && $bottomBlock->isSameType($this) && $bottomBlock->isSource())){
					$newDecay = 0;
					$falling = false;
				}
			}

			if($falling !== $this->falling || (!$falling && $newDecay !== $this->decay)){
				if(!$falling && $newDecay < 0){
					$world->setBlock($this->position, VanillaBlocks::AIR());
					return;
				}

				$this->falling = $falling;
				$this->decay = $falling ? 0 : $newDecay;
				$world->setBlock($this->position, $this); //local block update will cause an update to be scheduled
			}
		}

		$bottomBlock = $world->getBlockAt($this->position->x, $this->position->y - 1, $this->position->z);

		$this->flowIntoBlock($bottomBlock, 0, true);

		if($this->isSource() || !$bottomBlock->canBeFlowedInto()){
			if($this->falling){
				$adjacentDecay = 1; //falling liquid behaves like source block
			}else{
				$adjacentDecay = $this->decay + $multiplier;
			}

			if($adjacentDecay <= self::MAX_DECAY){
				$calculator = new MinimumCostFlowCalculator($world, $this->getFlowDecayPerBlock(), \Closure::fromCallable([$this, 'canFlowInto']));
				foreach($calculator->getOptimalFlowDirections($this->position->getFloorX(), $this->position->getFloorY(), $this->position->getFloorZ()) as $facing){
					$this->flowIntoBlock($world->getBlock($this->position->getSide($facing)), $adjacentDecay, false);
				}
			}
		}

		$this->checkForHarden();
	}

	protected function flowIntoBlock(Block $block, int $newFlowDecay, bool $falling) : void{
		if($this->canFlowInto($block) && !($block instanceof Liquid)){
			$new = clone $this;
			$new->falling = $falling;
			$new->decay = $falling ? 0 : $newFlowDecay;

			$ev = new BlockSpreadEvent($block, $this, $new);
			$ev->call();
			if(!$ev->isCancelled()){
				$world = $this->position->getWorld();
				if($block->getId() !== BlockLegacyIds::AIR){
					$world->useBreakOn($block->position);
				}

				$world->setBlock($block->position, $ev->getNewState());
			}
		}
	}

	/** @phpstan-impure */
	private function getSmallestFlowDecay(Block $block, int $decay) : int{
		if(!($block instanceof Liquid) || !$block->isSameType($this)){
			return $decay;
		}

		$blockDecay = $block->decay;

		if($block->isSource()){
			++$this->adjacentSources;
		}elseif($block->falling){
			$blockDecay = 0;
		}

		return ($decay >= 0 && $blockDecay >= $decay) ? $decay : $blockDecay;
	}

	protected function checkForHarden() : bool{
		return false;
	}

	protected function liquidCollide(Block $cause, Block $result) : bool{
		$ev = new BlockFormEvent($this, $result);
		$ev->call();
		if(!$ev->isCancelled()){
			$world = $this->position->getWorld();
			$world->setBlock($this->position, $ev->getNewState());
			$world->addSound($this->position->add(0.5, 0.5, 0.5), new FizzSound(2.6 + (lcg_value() - lcg_value()) * 0.8));
		}
		return true;
	}

	protected function canFlowInto(Block $block) : bool{
		return
			$this->position->getWorld()->isInWorld($block->position->x, $block->position->y, $block->position->z) &&
			$block->canBeFlowedInto() &&
			!($block instanceof Liquid && $block->isSource()); //TODO: I think this should only be liquids of the same type
	}
}
