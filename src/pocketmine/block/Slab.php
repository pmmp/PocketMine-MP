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
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Slab extends Transparent{
	/** @var int */
	protected $doubleId;

	/** @var bool */
	protected $top = false;

	public function __construct(int $id, int $doubleId, int $variant = 0, ?string $name = null){
		parent::__construct($id, $variant, $name . " Slab");
		$this->doubleId = $doubleId;
	}

	protected function writeStateToMeta() : int{
		return ($this->top ? 0x08 : 0);
	}

	public function readStateFromMeta(int $meta) : void{
		$this->top = ($meta & 0x08) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1000;
	}

	public function getDoubleSlabId() : int{
		return $this->doubleId;
	}

	protected function getDouble() : Block{
		return BlockFactory::get($this->doubleId, $this->variant);
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		if(parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock)){
			return true;
		}

		if($blockReplace instanceof Slab and $blockReplace->getId() === $this->getId() and $blockReplace->getVariant() === $this->variant){
			if($blockReplace->top){ //Trying to combine with top slab
				return $clickVector->y <= 0.5 or (!$isClickedBlock and $face === Facing::UP);
			}else{
				return $clickVector->y >= 0.5 or (!$isClickedBlock and $face === Facing::DOWN);
			}
		}

		return false;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($face === Facing::DOWN){
			if($blockClicked instanceof Slab and $blockClicked->getId() === $this->getId() and $blockClicked->top and $blockClicked->getVariant() === $this->variant){
				$this->getLevel()->setBlock($blockClicked, $this->getDouble());

				return true;
			}elseif($blockReplace->getId() === $this->getId() and $blockReplace->getVariant() === $this->variant){
				$this->getLevel()->setBlock($blockReplace, $this->getDouble());

				return true;
			}else{
				$this->top = true;
			}
		}elseif($face === Facing::UP){
			if($blockClicked instanceof Slab and $blockClicked->getId() === $this->getId() and !$blockClicked->top and $blockClicked->getVariant() === $this->variant){
				$this->getLevel()->setBlock($blockClicked, $this->getDouble());

				return true;
			}elseif($blockReplace->getId() === $this->getId() and $blockReplace->getVariant() === $this->variant){
				$this->getLevel()->setBlock($blockReplace, $this->getDouble());

				return true;
			}
		}else{ //TODO: collision
			if($blockReplace->getId() === $this->getId()){
				if($blockReplace->getVariant() === $this->variant){
					$this->getLevel()->setBlock($blockReplace, $this->getDouble());

					return true;
				}

				return false;
			}else{
				if($clickVector->y > 0.5){
					$this->top = true;
				}
			}
		}

		if($blockReplace->getId() === $this->getId() and $blockClicked->getVariant() !== $this->variant){
			return false;
		}

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		if($this->top){
			return new AxisAlignedBB(0, 0.5, 0, 1, 1, 1);
		}else{
			return new AxisAlignedBB(0, 0, 0, 1, 0.5, 1);
		}
	}
}
