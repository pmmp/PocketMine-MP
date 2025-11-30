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

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\event\block\ComposterEmptyEvent;
use pocketmine\event\block\ComposterFillEvent;
use pocketmine\event\block\ComposterReadyEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\CropGrowthEmitterParticle;
use pocketmine\world\sound\ComposterEmptySound;
use pocketmine\world\sound\ComposterFillSound;
use pocketmine\world\sound\ComposterFillSuccessSound;
use pocketmine\world\sound\ComposterReadySound;

class Composter extends Transparent{
	public const MIN_COMPOST_LAYERS = 0;
	public const MAX_COMPOST_LAYERS = 8;

	protected int $layers = 0;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(0, self::MAX_COMPOST_LAYERS, $this->layers);
	}
	
	protected function recalculateCollisionBoxes() : array{
		foreach(Facing::HORIZONTAL as $f){
			$result[] = AxisAlignedBB::one()->trim($f, 14 / 16);
		}
		return $result;
	}

	public function getCompostLayers() : int{ return $this->layers; }

	/** @return $this */
	public function setCompostLayers(int $layers) : self{
		if($layers < 0 || $layers > self::MAX_COMPOST_LAYERS){
			throw new \InvalidArgumentException("Compost layers must be in range 0 ... " . self::MAX_COMPOST_LAYERS);
		}
		$this->layers = $layers;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($this->layers >= self::MAX_COMPOST_LAYERS){
			$event = new ComposterEmptyEvent($this, self::MIN_COMPOST_LAYERS, [VanillaItems::BONE_MEAL()]);
			$event->call();

			if($event->isCancelled()){
				return true;
			}

			$this->setCompostLayers($event->getCompostLayer());
			$this->position->getWorld()->setBlock($this->position, $this);
			$this->position->getWorld()->addSound($this->position, new ComposterEmptySound());

			foreach($event->getDrops() as $drop){
				$this->position->getWorld()->dropItem($this->position->add(0.5, 1.0, 0.5), $drop);
			}
			return true;
		}

		if(!$item->isNull() && $item->isCompostable()){
			$event = new ComposterFillEvent(
				$this, 
				$item, 
				$this->layers,
				$this->layers + 1, 
				mt_rand(1, 100) <= $item->getCompostabilityChance() && $this->layers < self::MAX_COMPOST_LAYERS - 1
			);
			$event->call();

			if(!$event->isCancelled()){
				$this->position->getWorld()->addSound($this->position, new ComposterFillSound());

				if($event->getResult()){
					$this->setCompostLayers($event->getNewFillLayer());
					$this->position->getWorld()->setBlock($this->position, $this);
					$this->position->getWorld()->addSound($this->position, new ComposterFillSuccessSound());
					$this->position->getWorld()->addParticle($this->position->add(0.5, 0.5, 0.5), new CropGrowthEmitterParticle());
					$item->pop();
				}

				if($this->layers === self::MAX_COMPOST_LAYERS - 1){
					$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20);
				}
			}
		}

		return true;
	}

	public function onScheduledUpdate() : void{
		$event = new ComposterReadyEvent($this);
		$event->call();

		if($event->isCancelled()){
			return;
		}

		$this->layers = min(self::MAX_COMPOST_LAYERS, ++$this->layers);
		$this->position->getWorld()->setBlock($this->position, $this);
		$this->position->getWorld()->addSound($this->position, new ComposterReadySound());
	}

	public function getDropsForIncompatibleTool(Item $item) : array{
		return [$this->asItem()];
	}

	public function getFlammability() : int{ return 5; }

	public function getFuelTime() : int{ return 15; }
}