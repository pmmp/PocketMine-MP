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

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\Tool;

class Cobweb extends Flowable{

	protected $id = self::COBWEB;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function getName() : string{
		return "Cobweb";
	}

	public function getHardness() : float{
		return 4;
	}

	public function getToolType() : int{
		return Tool::TYPE_SWORD;
	}

	public function onEntityCollide(Entity $entity) : void{
		$entity->resetFallDistance();
	}

	public function getDrops(Item $item) : array{
		//TODO: correct drops
		return [];
	}

	public function diffusesSkyLight() : bool{
		return true;
	}
}