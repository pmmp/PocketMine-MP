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

namespace pocketmine\block\inventory;

use pocketmine\player\Player;
use pocketmine\world\sound\Sound;
use function count;

trait AnimatedBlockInventoryTrait{
	use BlockInventoryTrait;

	/**
	 * @return Player[]
	 * @phpstan-return array<int, Player>
	 */
	abstract public function getViewers() : array;

	abstract protected function getOpenSound() : Sound;

	abstract protected function getCloseSound() : Sound;

	public function onOpen(Player $who) : void{
		parent::onOpen($who);

		if(count($this->getViewers()) === 1 and $this->getHolder()->isValid()){
			//TODO: this crap really shouldn't be managed by the inventory
			$this->animateBlock(true);
			$this->getHolder()->getWorld()->addSound($this->getHolder()->add(0.5, 0.5, 0.5), $this->getOpenSound());
		}
	}

	abstract protected function animateBlock(bool $isOpen) : void;

	public function onClose(Player $who) : void{
		if(count($this->getViewers()) === 1 and $this->getHolder()->isValid()){
			//TODO: this crap really shouldn't be managed by the inventory
			$this->animateBlock(false);
			$this->getHolder()->getWorld()->addSound($this->getHolder()->add(0.5, 0.5, 0.5), $this->getCloseSound());
		}
		parent::onClose($who);
	}
}
