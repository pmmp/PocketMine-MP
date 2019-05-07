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

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\EntityEventPacket;

class SalmonFish extends WaterAnimal{
	public const NETWORK_ID = self::SALMON;

	public $width = 0.8;
	public $height = 0.16;

	/** @var Vector3 */
	public $swimDirection = null;
	public $swimSpeed = 0.35;

	private $switchDirectionTicker = 0;

	public function initEntity() : void{
		$this->setMaxHealth(7);
		parent::initEntity();
	}

	public function getName() : string{
		return "Salmon";
	}

	public function getDrops() : array{
		return [
			ItemFactory::get(Item::FISH, 0, mt_rand(1, 3))
		];
	}
}