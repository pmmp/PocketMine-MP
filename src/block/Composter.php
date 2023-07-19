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

use pocketmine\block\utils\CompostFactory;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\particle\CropGrowthEmitterParticle;
use pocketmine\world\sound\ComposterEmptySound;
use pocketmine\world\sound\ComposterFillSound;
use pocketmine\world\sound\ComposterFillSuccessSound;
use pocketmine\world\sound\ComposterReadySound;
use function max;
use function mt_rand;

class Composter extends Transparent{
	public const READY = 8;

	public const MIN_LEVEL = 0;
	public const MAX_LEVEL = 7;

	protected int $fill_level = self::MIN_LEVEL;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedInt(4, self::MIN_LEVEL, self::READY, $this->fill_level);
	}

	protected function recalculateCollisionBoxes() : array{
		$empty_layer = (max(1, 15 - 2 * $this->fill_level) - (int) ($this->fill_level === 0)) / 16;
		$boxes = [AxisAlignedBB::one()->contract(2 / 16, 0, 2 / 16)->trim(Facing::UP, $empty_layer)];

		foreach (Facing::HORIZONTAL as $side) {
			$boxes[] = AxisAlignedBB::one()->trim(Facing::opposite($side), 14 / 16);
		}
		return $boxes;
	}

	public function isEmpty() : bool{
		return $this->fill_level === self::MIN_LEVEL;
	}

	public function isReady() : bool{
		return $this->fill_level === self::READY;
	}

	public function getFillLevel() : int{
		return $this->fill_level;
	}

	/** @return $this */
	public function setFillLevel(int $fill_level) : self{
		if($fill_level < 0 || $fill_level > self::READY){
			throw new \InvalidArgumentException("Fill level  must be in range 0 ... " . self::READY);
		}
		$this->fill_level = $fill_level;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if (($player instanceof Player) && $this->compost(clone $item)) {
			$item->pop();
		}
		return true;
	}

	public function onScheduledUpdate() : void{
		if ($this->fill_level === self::MAX_LEVEL) {
			$this->position->getWorld()->addSound($this->position, new ComposterReadySound());
			$this->fill_level = self::READY;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}

	public function compost(Item $item) : bool{
		if ($this->fill_level === self::MAX_LEVEL) {
			return false;
		}
		if ($this->fill_level === self::READY) {
			$this->empty(true);
			return false;
		}
		if (!CompostFactory::getInstance()->isCompostable($item)) {
			return false;
		}
		$this->position->getWorld()->addParticle($this->position->add(0.5, 0.5, 0.5), new CropGrowthEmitterParticle());

		if (mt_rand(1, 100) <= CompostFactory::getInstance()->getPercentage($item)) {
			$this->position->getWorld()->addSound($this->position, new ComposterFillSuccessSound());
			++$this->fill_level;
			if ($this->fill_level === self::MAX_LEVEL) {
				$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20);
			}
		} else {
			$this->position->getWorld()->addSound($this->position, new ComposterFillSound());
		}

		$this->position->getWorld()->setBlock($this->position, $this);
		return true;
	}

	public function empty(bool $hasDrop = false) : void{
		if ($hasDrop && $this->fill_level === self::READY) {
			$this->position->getWorld()->dropItem(
				$this->position->add(0.5, 0.85, 0.5),
				VanillaItems::BONE_MEAL(),
				new Vector3(0, 0, 0)
			);
			$this->position->getWorld()->addParticle($this->position, new CropGrowthEmitterParticle());
		}

		$this->position->getWorld()->addSound($this->position, new ComposterEmptySound());
		$this->fill_level = self::MIN_LEVEL;
		$this->position->getWorld()->setBlock($this->position, $this);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return $this->fill_level === self::READY ? [
			VanillaBlocks::COMPOSTER()->asItem(),
			VanillaItems::BONE_MEAL()
		] : [
			VanillaBlocks::COMPOSTER()->asItem()
		];
	}

	public function getFlameEncouragement() : int{
		return 5;
	}

	public function getFlammability() : int{
		return 20;
	}

	public function getFuelTime() : int{
		return 50;
	}
}
