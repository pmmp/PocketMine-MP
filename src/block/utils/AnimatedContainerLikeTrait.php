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

use pocketmine\inventory\InventoryHolder;
use pocketmine\world\Position;
use pocketmine\world\sound\Sound;
use function count;

trait AnimatedContainerLikeTrait{

	protected function getViewerCount() : int{
		$position = $this->getPosition();
		$tile = $position->getWorld()->getTile($position);
		if($tile instanceof InventoryHolder){
			return count($tile->getInventory()->getViewers());
		}
		return 0;
	}

	abstract protected function getOpenSound() : Sound;

	abstract protected function getCloseSound() : Sound;

	abstract protected function playAnimationVisual(Position $position, bool $isOpen) : void;

	protected function playAnimationSound(Position $position, bool $isOpen) : void{
		$position->getWorld()->addSound($position->add(0.5, 0.5, 0.5), $isOpen ? $this->getOpenSound() : $this->getCloseSound());
	}

	abstract protected function getPosition() : Position;

	protected function doAnimationEffects(bool $isOpen) : void{
		$position = $this->getPosition();
		$this->playAnimationVisual($position, $isOpen);
		$this->playAnimationSound($position, $isOpen);
	}

	public function onViewerAdded() : void{
		if($this->getViewerCount() === 1){
			$this->doAnimationEffects(true);
		}
	}

	public function onViewerRemoved() : void{
		if($this->getViewerCount() === 1){
			$this->doAnimationEffects(false);
		}
	}
}
