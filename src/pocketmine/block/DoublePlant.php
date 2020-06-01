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
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function mt_rand;

class DoublePlant extends Flowable{
	public const BITFLAG_TOP = 0x08;

	protected $id = self::DOUBLE_PLANT;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function canBeReplaced() : bool{
		return $this->getVariant() === 2 or $this->getVariant() === 3; //grass or fern
	}

	public function getName() : string{
		static $names = [
			0 => "Sunflower",
			1 => "Lilac",
			2 => "Double Tallgrass",
			3 => "Large Fern",
			4 => "Rose Bush",
			5 => "Peony"
		];
		return $names[$this->getVariant()] ?? "";
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$id = $blockReplace->getSide(Vector3::SIDE_DOWN)->getId();
		if(($id === Block::GRASS or $id === Block::DIRT) and $blockReplace->getSide(Vector3::SIDE_UP)->canBeReplaced()){
			$this->getLevelNonNull()->setBlock($blockReplace, $this, false, false);
			$this->getLevelNonNull()->setBlock($blockReplace->getSide(Vector3::SIDE_UP), BlockFactory::get($this->id, $this->meta | self::BITFLAG_TOP), false, false);

			return true;
		}

		return false;
	}

	/**
	 * Returns whether this double-plant has a corresponding other half.
	 */
	public function isValidHalfPlant() : bool{
		if(($this->meta & self::BITFLAG_TOP) !== 0){
			$other = $this->getSide(Vector3::SIDE_DOWN);
		}else{
			$other = $this->getSide(Vector3::SIDE_UP);
		}

		return (
			$other->getId() === $this->getId() and
			$other->getVariant() === $this->getVariant() and
			($other->getDamage() & self::BITFLAG_TOP) !== ($this->getDamage() & self::BITFLAG_TOP)
		);
	}

	public function onNearbyBlockChange() : void{
		if(!$this->isValidHalfPlant() or (($this->meta & self::BITFLAG_TOP) === 0 and $this->getSide(Vector3::SIDE_DOWN)->isTransparent())){
			$this->getLevelNonNull()->useBreakOn($this);
		}
	}

	public function getVariantBitmask() : int{
		return 0x07;
	}

	public function getToolType() : int{
		return ($this->getVariant() === 2 or $this->getVariant() === 3) ? BlockToolType::TYPE_SHEARS : BlockToolType::TYPE_NONE;
	}

	public function getToolHarvestLevel() : int{
		return ($this->getVariant() === 2 or $this->getVariant() === 3) ? 1 : 0; //only grass or fern require shears
	}

	public function getDrops(Item $item) : array{
		if(($this->meta & self::BITFLAG_TOP) !== 0){
			if($this->isCompatibleWithTool($item)){
				return parent::getDrops($item);
			}

			if(mt_rand(0, 24) === 0){
				return [
					ItemFactory::get(Item::SEEDS)
				];
			}
		}

		return [];
	}

	public function getAffectedBlocks() : array{
		if($this->isValidHalfPlant()){
			return [$this, $this->getSide(($this->meta & self::BITFLAG_TOP) !== 0 ? Vector3::SIDE_DOWN : Vector3::SIDE_UP)];
		}

		return parent::getAffectedBlocks();
	}

	public function getFlameEncouragement() : int{
		return 60;
	}

	public function getFlammability() : int{
		return 100;
	}
}
