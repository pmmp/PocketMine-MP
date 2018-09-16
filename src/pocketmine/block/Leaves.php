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
use pocketmine\math\Facing;
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
		$this->setDamage($meta);
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


	protected function findLog(Block $pos, array &$visited = [], int $distance = 0) : bool{
		$index = Level::blockHash($pos->x, $pos->y, $pos->z);
		if(isset($visited[$index])){
			return false;
		}
		$visited[$index] = true;

		if($pos->getId() === $this->woodType){
			return true;
		}

		if($pos->getId() === $this->id and $distance <= 4){
			foreach(Facing::ALL as $side){
				if($this->findLog($pos->getSide($side), $visited, $distance + 1)){
					return true;
				}
			}
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(($this->meta & 0b00001100) === 0){
			$this->meta |= 0x08;
			$this->getLevel()->setBlock($this, $this, true, false);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(($this->meta & 0b00001100) === 0x08){
			$this->meta &= 0x03;

			$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new LeavesDecayEvent($this));

			if($ev->isCancelled() or $this->findLog($this)){
				$this->getLevel()->setBlock($this, $this, false, false);
			}else{
				$this->getLevel()->useBreakOn($this);
			}
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$this->meta |= 0x04;
		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
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

	public function getFlameEncouragement() : int{
		return 30;
	}

	public function getFlammability() : int{
		return 60;
	}
}
