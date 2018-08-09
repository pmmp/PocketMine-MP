<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Rail extends Flowable{

	public const STRAIGHT_NORTH_SOUTH = 0;
	public const STRAIGHT_EAST_WEST = 1;
	public const ASCENDING_EAST = 2;
	public const ASCENDING_WEST = 3;
	public const ASCENDING_NORTH = 4;
	public const ASCENDING_SOUTH = 5;
	public const CURVE_SOUTHEAST = 6;
	public const CURVE_SOUTHWEST = 7;
	public const CURVE_NORTHWEST = 8;
	public const CURVE_NORTHEAST = 9;

	protected $id = self::RAIL;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Rail";
	}

	/**
	 * @param Rail $block
	 * @return bool|array
	 */
	public function canConnect(Rail $block){
		if($this->distanceSquared($block) > 2){
			return false;
		}

		/** @var Vector3 [] $blocks */
		if(count($blocks = self::check($this)) == 2){
			return false;
		}

		return $blocks;
	}

	public function isBlock(Block $block){
		if($block instanceof Air){
			return false;
		}
		return $block;
	}

	public function connect(Rail $rail, $force = false){

		if(!$force){
			$connected = $this->canConnect($rail);
			if(!is_array($connected)){
				return false;
			}
			/** @var Vector3 [] $connected */
			$connected[] = $rail;
			switch(count($connected)){
				case  1:
					$v3 = $connected[0]->subtract($this);
					$this->meta = (int)((($v3->y != 1) ? ($v3->x == 0 ? 0 : 1) : ($v3->z == 0 ? ($v3->x / -2) + 2.5 : ($v3->z / 2) + 4.5)));
					break;
				case 2:
					$subtract = [];
					foreach($connected as $key => $value){
						$subtract[$key] = $value->subtract($this);
					}
					if(abs($subtract[0]->x) == abs($subtract[1]->z) and abs($subtract[1]->x) == abs($subtract[0]->z)){
						$v3 = $connected[0]->subtract($this)->add($connected[1]->subtract($this));
						$this->meta = (int)($v3->x == 1 ? ($v3->z == 1 ? 6 : 9) : ($v3->z == 1 ? 7 : 8));
					}elseif($subtract[0]->y == 1 or $subtract[1]->y == 1){
						$v3 = $subtract[0]->y == 1 ? $subtract[0] : $subtract[1];
						$this->meta = (int)($v3->x == 0 ? ($v3->z == -1 ? 4 : 5) : ($v3->x == 1 ? 2 : 3));
					}else{
						$this->meta = (int)($subtract[0]->x == 0 ? 0 : 1);
					}
					break;
				default:
					break;
			}
		}
		$this->level->setBlock($this, Block::get($this->id, $this->meta), true, true);
		return true;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$downBlock = $this->getSide(Vector3::SIDE_DOWN);

		if($downBlock instanceof Rail or !$this->isBlock($downBlock)){
			return false;
		}

		$arrayXZ = [[1, 0], [0, 1], [-1, 0], [0, -1]];
		$arrayY = [0, 1, -1];

		/** @var Vector3 [] $connected */
		$connected = [];
		foreach($arrayXZ as $xz){
			$x = $xz[0];
			$z = $xz[1];
			foreach($arrayY as $y){
				$v3 = (new Vector3($x, $y, $z))->add($this);
				$block = $this->level->getBlock($v3);
				if($block instanceof Rail){
					if($block->connect($this)){
						$connected[] = $v3;
						break;
					}
				}
			}
			if(count($connected) == 2){
				break;
			}
		}
		switch(count($connected)){
			case  1:
				$v3 = $connected[0]->subtract($this);
				$this->meta = (int)((($v3->y != 1) ? ($v3->x == 0 ? 0 : 1) : ($v3->z == 0 ? ($v3->x / -2) + 2.5 : ($v3->z / 2) + 4.5)));
				break;
			case 2:
				$subtract = [];
				foreach($connected as $key => $value){
					$subtract[$key] = $value->subtract($this);
				}
				if(abs($subtract[0]->x) == abs($subtract[1]->z) and abs($subtract[1]->x) == abs($subtract[0]->z)){
					$v3 = $connected[0]->subtract($this)->add($connected[1]->subtract($this));
					$this->meta = (int)($v3->x == 1 ? ($v3->z == 1 ? 6 : 9) : ($v3->z == 1 ? 7 : 8));
				}elseif($subtract[0]->y == 1 or $subtract[1]->y == 1){
					$v3 = $subtract[0]->y == 1 ? $subtract[0] : $subtract[1];
					$this->meta = (int)($v3->x == 0 ? ($v3->z == -1 ? 4 : 5) : ($v3->x == 1 ? 2 : 3));
				}else{
					$this->meta = (int)($subtract[0]->x == 0 ? 0 : 1);
				}
				break;
			default:
				break;
		}
		$this->level->setBlock($this, Block::get((int) $this->id, (int) $this->meta), true, true);
		return true;
	}

	/**
	 * @param Rail $rail
	 * @return array
	 */
	public static function check(Rail $rail){
		$array = [
			[[0, 1], [0, -1]],
			[[1, 0], [-1, 0]],
			[[1, 0], [-1, 0]],
			[[1, 0], [-1, 0]],
			[[0, 1], [0, -1]],
			[[0, 1], [0, -1]],
			[[1, 0], [0, 1]],
			[[0, 1], [-1, 0]],
			[[-1, 0], [0, -1]],
			[[0, -1], [1, 0]]
		];
		$arrayY = [0, 1, -1];
		$blocks = $array[$rail->getDamage()];
		$connected = [];
		foreach($arrayY as $y){
			$v3 = new Vector3($rail->x + $blocks[0][0], $rail->y + $y, $rail->z + $blocks[0][1]);
			$id = $rail->getLevel()->getBlockIdAt($v3->x, $v3->y, $v3->z);
			$meta = (int)($rail->getLevel()->getBlockDataAt($v3->x, $v3->y, $v3->z));
			if(in_array($id, [self::RAIL, self::ACTIVATOR_RAIL, self::DETECTOR_RAIL, self::POWERED_RAIL]) and in_array([$rail->x - $v3->x, $rail->z - $v3->z], $array[$meta])){
				$connected[] = $v3;
				break;
			}
		}
		foreach($arrayY as $y){
			$v3 = new Vector3($rail->x + $blocks[1][0], $rail->y + $y, $rail->z + $blocks[1][1]);
			$id = $rail->getLevel()->getBlockIdAt($v3->x, $v3->y, $v3->z);
			$meta = (int)($rail->getLevel()->getBlockDataAt($v3->x, $v3->y, $v3->z));
			if(in_array($id, [self::RAIL, self::ACTIVATOR_RAIL, self::DETECTOR_RAIL, self::POWERED_RAIL]) and in_array([$rail->x - $v3->x, $rail->z - $v3->z], $array[$meta])){
				$connected[] = $v3;
				break;
			}
		}
		return $connected;
	}

	public function getHardness() : float{
		return 0.7;
	}

	public function getResistance() : float{
		return 3.5;
	}

	public function canPassThrough() : bool{
		return true;
	}


	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			$this->getLevel()->useBreakOn($this);
		}else{
			self::check($this);
		}
	}

	public function getVariantBitmask() : int{
		return 0;
	}
}