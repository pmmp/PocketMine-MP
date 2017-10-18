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
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

class WoodenSlab extends Transparent{

	protected $id = self::WOODEN_SLAB;

	protected $doubleId = self::DOUBLE_WOODEN_SLAB;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 2;
	}

	public function getName() : string{
		static $names = [
			0 => "Oak",
			1 => "Spruce",
			2 => "Birch",
			3 => "Jungle",
			4 => "Acacia",
			5 => "Dark Oak"
		];
		return (($this->meta & 0x08) === 0x08 ? "Upper " : "") . ($names[$this->getVariant()] ?? "") . " Wooden Slab";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{

		if(($this->meta & 0x08) > 0){
			return new AxisAlignedBB(
				$this->x,
				$this->y + 0.5,
				$this->z,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1
			);
		}else{
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z,
				$this->x + 1,
				$this->y + 0.5,
				$this->z + 1
			);
		}
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector) : bool{
		return parent::canBePlacedAt($blockReplace, $clickVector) or
			(
				$blockReplace->getId() === $this->getId() and
				$blockReplace->getVariant() === $this->getVariant() and
				(
					(($blockReplace->getDamage() & 0x08) !== 0 and ($clickVector->y <= 0.5 or $clickVector->y === 1.0)) or //top slab, fill bottom half
					(($blockReplace->getDamage() & 0x08) === 0 and ($clickVector->y >= 0.5 or $clickVector->y === 0.0)) //bottom slab, fill top half
				)
			);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $facePos, Player $player = null) : bool{
		$this->meta &= 0x07;
		if($face === Vector3::SIDE_DOWN){
			if($blockClicked->getId() === $this->id and ($blockClicked->getDamage() & 0x08) === 0x08 and $blockClicked->getVariant() === $this->getVariant()){
				$this->getLevel()->setBlock($blockClicked, BlockFactory::get($this->doubleId, $this->getVariant()), true);

				return true;
			}elseif($blockReplace->getId() === $this->id and $blockReplace->getVariant() === $this->getVariant()){
				$this->getLevel()->setBlock($blockReplace, BlockFactory::get($this->doubleId, $this->getVariant()), true);

				return true;
			}else{
				$this->meta |= 0x08;
			}
		}elseif($face === Vector3::SIDE_UP){
			if($blockClicked->getId() === $this->id and ($blockClicked->getDamage() & 0x08) === 0 and $blockClicked->getVariant() === $this->getVariant()){
				$this->getLevel()->setBlock($blockClicked, BlockFactory::get($this->doubleId, $this->getVariant()), true);

				return true;
			}elseif($blockReplace->getId() === $this->id and $blockReplace->getVariant() === $this->getVariant()){
				$this->getLevel()->setBlock($blockReplace, BlockFactory::get($this->doubleId, $this->getVariant()), true);

				return true;
			}
		}else{ //TODO: collision
			if($blockReplace->getId() === $this->id){
				if($blockReplace->getVariant() === $this->meta){
					$this->getLevel()->setBlock($blockReplace, BlockFactory::get($this->doubleId, $this->getVariant()), true);

					return true;
				}

				return false;
			}else{
				if($facePos->y > 0.5){
					$this->meta |= 0x08;
				}
			}
		}

		if($blockReplace->getId() === $this->id and $blockClicked->getVariant() !== $this->getVariant()){
			return false;
		}
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		return true;
	}

	public function getToolType() : int{
		return Tool::TYPE_AXE;
	}

	public function getVariantBitmask() : int{
		return 0x07;
	}

	public function getFuelTime() : int{
		return 300;
	}
}