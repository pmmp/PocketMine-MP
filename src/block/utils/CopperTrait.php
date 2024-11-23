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

namespace pocketmine\block\utils;

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Axe;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\CopperWaxApplySound;
use pocketmine\world\sound\CopperWaxRemoveSound;
use pocketmine\world\sound\ScrapeSound;

trait CopperTrait{
	private CopperOxidation $oxidation = CopperOxidation::NONE;
	private bool $waxed = false;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void{
		$w->enum($this->oxidation);
		$w->bool($this->waxed);
	}

	public function getOxidation() : CopperOxidation{ return $this->oxidation; }

	/** @return $this */
	public function setOxidation(CopperOxidation $oxidation) : self{
		$this->oxidation = $oxidation;
		return $this;
	}

	public function isWaxed() : bool{ return $this->waxed; }

	/** @return $this */
	public function setWaxed(bool $waxed) : self{
		$this->waxed = $waxed;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if(!$this->waxed && $item->getTypeId() === ItemTypeIds::HONEYCOMB){
			$this->waxed = true;
			$this->position->getWorld()->setBlock($this->position, $this);
			//TODO: orange particles are supposed to appear when applying wax
			$this->position->getWorld()->addSound($this->position, new CopperWaxApplySound());
			$item->pop();
			return true;
		}

		if($item instanceof Axe){
			if($this->waxed){
				$this->waxed = false;
				$this->position->getWorld()->setBlock($this->position, $this);
				//TODO: white particles are supposed to appear when removing wax
				$this->position->getWorld()->addSound($this->position, new CopperWaxRemoveSound());
				$item->applyDamage(1);
				return true;
			}

			$previousOxidation = $this->oxidation->getPrevious();
			if($previousOxidation !== null){
				$this->oxidation = $previousOxidation;
				$this->position->getWorld()->setBlock($this->position, $this);
				//TODO: turquoise particles are supposed to appear when removing oxidation
				$this->position->getWorld()->addSound($this->position, new ScrapeSound());
				$item->applyDamage(1);
				return true;
			}
		}

		return false;
	}
}
