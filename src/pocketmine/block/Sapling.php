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

use pocketmine\block\utils\TreeType;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\level\generator\object\Tree;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;
use function mt_rand;

class Sapling extends Flowable{

	/** @var bool */
	protected $ready = false;
	/** @var TreeType */
	private $treeType;

	public function __construct(BlockIdentifier $idInfo, string $name, TreeType $treeType){
		parent::__construct($idInfo, $name);
		$this->treeType = $treeType;
	}

	protected function writeStateToMeta() : int{
		return ($this->ready ? 0x08 : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->ready = ($stateMeta & 0x08) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1000;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$down = $this->getSide(Facing::DOWN);
		if($down->getId() === self::GRASS or $down->getId() === self::DIRT or $down->getId() === self::FARMLAND){
			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof Fertilizer){
			Tree::growTree($this->getLevel(), $this->x, $this->y, $this->z, new Random(mt_rand()), $this->treeType);

			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->level->getFullLightAt($this->x, $this->y, $this->z) >= 8 and mt_rand(1, 7) === 1){
			if($this->ready){
				Tree::growTree($this->getLevel(), $this->x, $this->y, $this->z, new Random(mt_rand()), $this->treeType);
			}else{
				$this->ready = true;
				$this->getLevel()->setBlock($this, $this);
			}
		}
	}

	public function getFuelTime() : int{
		return 100;
	}
}
