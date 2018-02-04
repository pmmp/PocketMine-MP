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

use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Leaves extends Transparent{
	public const OAK = 0;
	public const SPRUCE = 1;
	public const BIRCH = 2;
	public const JUNGLE = 3;
	public const ACACIA = 0;
	public const DARK_OAK = 1;

	protected $id = self::LEAVES;
	protected $woodType = self::WOOD;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 0.2;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHEARS;
	}

	public function getName() : string{
		static $names = [
			self::OAK => "Oak Leaves",
			self::SPRUCE => "Spruce Leaves",
			self::BIRCH => "Birch Leaves",
			self::JUNGLE => "Jungle Leaves"
		];
		return $names[$this->getVariant()];
	}

	public function diffusesSkyLight() : bool{
		return true;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	protected function findLog(Block $pos, array $visited, $distance, &$check, $fromSide = null){
		++$check;
		$index = $pos->x . "." . $pos->y . "." . $pos->z;
		if(isset($visited[$index])){
			return false;
		}
		if($pos->getId() === $this->woodType){
			return true;
		}elseif($pos->getId() === $this->id and $distance < 3){
			$visited[$index] = true;
			$down = $pos->getSide(Vector3::SIDE_DOWN)->getId();
			if($down === $this->woodType){
				return true;
			}
			if($fromSide === null){
				for($side = 2; $side <= 5; ++$side){
					if($this->findLog($pos->getSide($side), $visited, $distance + 1, $check, $side) === true){
						return true;
					}
				}
			}else{ //No more loops
				switch($fromSide){
					case 2:
						if($this->findLog($pos->getSide(Vector3::SIDE_NORTH), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(Vector3::SIDE_WEST), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(Vector3::SIDE_EAST), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}
						break;
					case 3:
						if($this->findLog($pos->getSide(Vector3::SIDE_SOUTH), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(Vector3::SIDE_WEST), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(Vector3::SIDE_EAST), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}
						break;
					case 4:
						if($this->findLog($pos->getSide(Vector3::SIDE_NORTH), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(Vector3::SIDE_SOUTH), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(Vector3::SIDE_WEST), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}
						break;
					case 5:
						if($this->findLog($pos->getSide(Vector3::SIDE_NORTH), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(Vector3::SIDE_SOUTH), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}elseif($this->findLog($pos->getSide(Vector3::SIDE_EAST), $visited, $distance + 1, $check, $fromSide) === true){
							return true;
						}
						break;
				}
			}
		}

		return false;
	}

	public function onUpdate(int $type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if(($this->meta & 0b00001100) === 0){
				$this->meta |= 0x08;
				$this->getLevel()->setBlock($this, $this, true, false);
			}
		}elseif($type === Level::BLOCK_UPDATE_RANDOM){
			if(($this->meta & 0b00001100) === 0x08){
				$this->meta &= 0x03;
				$visited = [];
				$check = 0;

				$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new LeavesDecayEvent($this));

				if($ev->isCancelled() or $this->findLog($this, $visited, 0, $check) === true){
					$this->getLevel()->setBlock($this, $this, false, false);
				}else{
					$this->getLevel()->useBreakOn($this);

					return Level::BLOCK_UPDATE_NORMAL;
				}
			}
		}

		return false;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$this->meta |= 0x04;
		return $this->getLevel()->setBlock($this, $this, true);
	}

	public function getVariantBitmask() : int{
		return 0x03;
	}

	public function getDrops(Item $item) : array{
		if($item->getBlockToolType() & BlockToolType::TYPE_SHEARS){
			return $this->getDropsForCompatibleTool($item);
		}

		$drops = [];
		if(mt_rand(1, 20) === 1){ //Saplings
			$drops[] = $this->getSaplingItem();
		}
		if($this->canDropApples() and mt_rand(1, 200) === 1){ //Apples
			$drops[] = ItemFactory::get(Item::APPLE);
		}

		return $drops;
	}

	public function getSaplingItem() : Item{
		return ItemFactory::get(Item::SAPLING, $this->getVariant());
	}

	public function canDropApples() : bool{
		return $this->meta === self::OAK;
	}
}
