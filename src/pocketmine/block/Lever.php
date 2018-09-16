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
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Lever extends Flowable{

	protected $id = self::LEVER;

	public function __construct(int $meta = 0){
		$this->setDamage($meta);
	}

	public function getName() : string{
		return "Lever";
	}

	public function getHardness() : float{
		return 0.5;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if(!$blockClicked->isSolid()){
			return false;
		}

		if($face === Facing::DOWN){
			$this->meta = 0;
		}else{
			$this->meta = 6 - $face;
		}

		if($player !== null){
			$bearing = $player->getDirection();
			if($bearing === Bearing::EAST or $bearing === Bearing::WEST){
				if($face === Facing::UP){
					$this->meta = 6;
				}
			}else{
				if($face === Facing::DOWN){
					$this->meta = 7;
				}
			}
		}

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		static $faces = [
			0 => Facing::UP,
			1 => Facing::WEST,
			2 => Facing::EAST,
			3 => Facing::NORTH,
			4 => Facing::SOUTH,
			5 => Facing::DOWN,
			6 => Facing::DOWN,
			7 => Facing::UP
		];
		if(!$this->getSide($faces[$this->meta & 0x07])->isSolid()){
			$this->level->useBreakOn($this);
		}
	}

	//TODO
}
