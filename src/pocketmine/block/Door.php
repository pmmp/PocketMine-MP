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
use pocketmine\level\sound\DoorSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;


abstract class Door extends Transparent{

	public function isSolid() : bool{
		return false;
	}

	private function getFullDamage(){
		$damage = $this->getDamage();
		$isUp = ($damage & 0x08) > 0;

		if($isUp){
			$down = $this->getSide(Facing::DOWN)->getDamage();
			$up = $damage;
		}else{
			$down = $damage;
			$up = $this->getSide(Facing::UP)->getDamage();
		}

		$isRight = ($up & 0x01) > 0;

		return $down & 0x07 | ($isUp ? 8 : 0) | ($isRight ? 0x10 : 0);
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		$f = 0.1875;
		$damage = $this->getFullDamage();

		$bb = new AxisAlignedBB(0, 0, 0, 1, 2, 1);

		$j = $damage & 0x03;
		$isOpen = (($damage & 0x04) > 0);
		$isRight = (($damage & 0x10) > 0);

		if($j === 0){
			if($isOpen){
				if(!$isRight){
					$bb->setBounds(0, 0, 0, 1, 1, $f);
				}else{
					$bb->setBounds(0, 0, 1 - $f, 1, 1, 1);
				}
			}else{
				$bb->setBounds(0, 0, 0, $f, 1, 1);
			}
		}elseif($j === 1){
			if($isOpen){
				if(!$isRight){
					$bb->setBounds(1 - $f, 0, 0, 1, 1, 1);
				}else{
					$bb->setBounds(0, 0, 0, $f, 1, 1);
				}
			}else{
				$bb->setBounds(0, 0, 0, 1, 1, $f);
			}
		}elseif($j === 2){
			if($isOpen){
				if(!$isRight){
					$bb->setBounds(0, 0, 1 - $f, 1, 1, 1);
				}else{
					$bb->setBounds(0, 0, 0, 1, 1, $f);
				}
			}else{
				$bb->setBounds(1 - $f, 0, 0, 1, 1, 1);
			}
		}elseif($j === 3){
			if($isOpen){
				if(!$isRight){
					$bb->setBounds(0, 0, 0, $f, 1, 1);
				}else{
					$bb->setBounds(1 - $f, 0, 0, 1, 1, 1);
				}
			}else{
				$bb->setBounds(0, 0, 1 - $f, 1, 1, 1);
			}
		}

		return $bb;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->getId() === self::AIR){ //Replace with common break method
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), false);
			if($this->getSide(Facing::UP) instanceof Door){
				$this->getLevel()->setBlock($this->getSide(Facing::UP), BlockFactory::get(Block::AIR), false);
			}
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($face === Facing::UP){
			$blockUp = $this->getSide(Facing::UP);
			$blockDown = $this->getSide(Facing::DOWN);
			if(!$blockUp->canBeReplaced() or $blockDown->isTransparent()){
				return false;
			}

			$ccw = Bearing::toFacing($player instanceof Player ? Bearing::rotate($player->getDirection(), -1) : Facing::EAST);

			$next = $this->getSide(Facing::opposite($ccw));
			$next2 = $this->getSide($ccw);

			$metaUp = 0x08;
			if($next->getId() === $this->getId() or (!$next2->isTransparent() and $next->isTransparent())){ //Door hinge
				$metaUp |= 0x01;
			}

			$this->setDamage(Bearing::rotate($player->getDirection(), -1));
			$this->getLevel()->setBlock($blockReplace, $this, true, true); //Bottom
			$this->getLevel()->setBlock($blockUp, BlockFactory::get($this->getId(), $metaUp), true); //Top
			return true;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if(($this->getDamage() & 0x08) === 0x08){ //Top
			$down = $this->getSide(Facing::DOWN);
			if($down->getId() === $this->getId()){
				$meta = $down->getDamage() ^ 0x04;
				$this->level->setBlock($down, BlockFactory::get($this->getId(), $meta), true);
				$this->level->addSound(new DoorSound($this));
				return true;
			}

			return false;
		}else{
			$this->meta ^= 0x04;
			$this->level->setBlock($this, $this, true);
			$this->level->addSound(new DoorSound($this));
		}

		return true;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		if(($this->meta & 0x08) === 0){ //bottom half only
			return parent::getDropsForCompatibleTool($item);
		}

		return [];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function getAffectedBlocks() : array{
		if(($this->getDamage() & 0x08) === 0x08){
			$down = $this->getSide(Facing::DOWN);
			if($down->getId() === $this->getId()){
				return [$this, $down];
			}
		}else{
			$up = $this->getSide(Facing::UP);
			if($up->getId() === $this->getId()){
				return [$this, $up];
			}
		}

		return parent::getAffectedBlocks();
	}
}
