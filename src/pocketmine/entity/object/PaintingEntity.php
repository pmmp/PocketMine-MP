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

namespace pocketmine\entity\object;

use pocketmine\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\Player;

class PaintingEntity extends HangingEntity{

	const NETWORK_ID = 83;

	/** @var string */
	protected $motive;

	protected function initEntity(){
		parent::initEntity();
		$this->motive = ((string) $this->namedtag->Motive->getValue());
	}

	protected function sendSpawnPacket(Player $player){
		$pk = new AddPaintingPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->x = $this->blockIn->x;
		$pk->y = $this->blockIn->y;
		$pk->z = $this->blockIn->z;
		$pk->direction = $this->direction;
		$pk->title = $this->motive;

		$player->dataPacket($pk);
	}

	public static function getSaveId() : string{
		return "Painting";
	}

	/**
	 * Returns the painting motive (which image is displayed on the painting)
	 * @return PaintingMotive
	 */
	public function getMotive() : PaintingMotive{
		return PaintingMotive::getMotiveByName($this->motive);
	}
}