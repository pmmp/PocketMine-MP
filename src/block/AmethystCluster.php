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

use pocketmine\block\utils\AmethystTrait;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\FortuneDropHelper;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\BlockTransaction;

final class AmethystCluster extends Transparent{
	use AmethystTrait;
	use AnyFacingTrait;

	public const STAGE_SMALL_BUD = 0;
	public const STAGE_MEDIUM_BUD = 1;
	public const STAGE_LARGE_BUD = 2;
	public const STAGE_CLUSTER = 3;

	private int $stage = self::STAGE_CLUSTER;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(self::STAGE_SMALL_BUD, self::STAGE_CLUSTER, $this->stage);
	}

	public function getStage() : int{ return $this->stage; }

	public function setStage(int $stage) : self{
		if($stage < self::STAGE_SMALL_BUD || $stage > self::STAGE_CLUSTER){
			throw new \InvalidArgumentException("Size must be in range " . self::STAGE_SMALL_BUD . " ... " . self::STAGE_CLUSTER);
		}
		$this->stage = $stage;
		return $this;
	}

	public function getLightLevel() : int{
		return match($this->stage){
			self::STAGE_SMALL_BUD => 1,
			self::STAGE_MEDIUM_BUD => 2,
			self::STAGE_LARGE_BUD => 4,
			self::STAGE_CLUSTER => 5,
			default => throw new AssumptionFailedError("Invalid stage $this->stage"),
		};
	}

	protected function recalculateCollisionBoxes() : array{
		$myAxis = Facing::axis($this->facing);

		$box = AxisAlignedBB::one();
		foreach([Axis::Y, Axis::Z, Axis::X] as $axis){
			if($axis === $myAxis){
				continue;
			}
			$box->squash($axis, $this->stage === self::STAGE_SMALL_BUD ? 4 / 16 : 3 / 16);
		}
		$box->trim($this->facing, 1 - ($this->stage === self::STAGE_CLUSTER ? 7 / 16 : ($this->stage + 3) / 16));

		return [$box];
	}

	private function canBeSupportedAt(Block $block, int $facing) : bool{
		return $block->getAdjacentSupportType($facing) === SupportType::FULL;
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedAt($blockReplace, Facing::opposite($face))){
			return false;
		}

		$this->facing = $face;
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedAt($this, Facing::opposite($this->facing))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		if($this->stage === self::STAGE_CLUSTER){
			return [VanillaItems::AMETHYST_SHARD()->setCount(FortuneDropHelper::weighted($item, min: 4, maxBase: 4))];
		}

		return [];
	}

	public function getDropsForIncompatibleTool(Item $item) : array{
		if($this->stage === self::STAGE_CLUSTER){
			return [VanillaItems::AMETHYST_SHARD()->setCount(FortuneDropHelper::weighted($item, min: 2, maxBase: 2))];
		}

		return [];
	}
}
