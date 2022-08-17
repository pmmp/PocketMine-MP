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

namespace pocketmine\item;

use pocketmine\data\runtime\RuntimeDataWriter;
use pocketmine\player\Player;
use pocketmine\math\Vector3;

class GoatHorn extends Item implements Releasable{

	private GoatHornType $goatHornType;

	public function __construct(ItemIdentifier $identifier, string $name){
		$this->goatHornType = GoatHornType::PONDER();
		parent::__construct($identifier, $name);
	}

	protected function encodeType(RuntimeDataWriter $w) : void{
		$w->goatHornType($this->goatHornType);
	}

	public function getType() : GoatHornType{ return $this->goatHornType; }

	/**
	 * @return $this
	 */
	public function setType(GoatHornType $type) : self{
		$this->goatHornType = $type;
		return $this;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getCooldownTicks() : int{
		return 140;
	}

	public function canStartUsingItem(Player $player) : bool{
		return true;
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		$position = $player->getPosition();
		$position->getWorld()->addSound($position, $this->goatHornType->getSound());

		return ItemUseResult::SUCCESS();
	}
}
