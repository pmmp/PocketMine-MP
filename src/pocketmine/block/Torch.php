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

use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Torch extends Flowable{

	protected $id = self::TORCH;

	public function __construct(int $meta = 0){
		$this->setDamage($meta);
	}

	public function getLightLevel() : int{
		return 14;
	}

	public function getName() : string{
		return "Torch";
	}

	public function onNearbyBlockChange() : void{
		$below = $this->getSide(Facing::DOWN);
		$side = $this->getDamage();
		static $faces = [
			0 => Facing::DOWN,
			1 => Facing::WEST,
			2 => Facing::EAST,
			3 => Facing::NORTH,
			4 => Facing::SOUTH,
			5 => Facing::DOWN
		];

		if($this->getSide($faces[$side])->isTransparent() and !($faces[$side] === Facing::DOWN and ($below->getId() === self::FENCE or $below->getId() === self::COBBLESTONE_WALL))){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$below = $this->getSide(Facing::DOWN);

		if(!$blockClicked->isTransparent() and $face !== Facing::DOWN){
			static $faces = [
				Facing::UP => 5,
				Facing::NORTH => 4,
				Facing::SOUTH => 3,
				Facing::WEST => 2,
				Facing::EAST => 1
			];
			$this->meta = $faces[$face];

			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}elseif(!$below->isTransparent() or $below->getId() === self::FENCE or $below->getId() === self::COBBLESTONE_WALL){
			$this->meta = 0;
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function getVariantBitmask() : int{
		return 0;
	}
}
