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
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function mt_rand;

class Leaves extends Transparent{
	/** @var TreeType */
	protected $treeType;

	/** @var bool */
	protected $noDecay = false;
	/** @var bool */
	protected $checkDecay = false;

	public function __construct(BlockIdentifier $idInfo, string $name, TreeType $treeType){
		parent::__construct($idInfo, $name);
		$this->treeType = $treeType;
	}

	protected function writeStateToMeta() : int{
		return ($this->noDecay ? 0x04 : 0) | ($this->checkDecay ? 0x08 : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->noDecay = ($stateMeta & 0x04) !== 0;
		$this->checkDecay = ($stateMeta & 0x08) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1100;
	}

	public function getHardness() : float{
		return 0.2;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_SHEARS;
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

		$id = $pos->getId();
		if($id === Block::WOOD or $id === Block::WOOD2){
			return true;
		}

		if($pos->getId() === $this->getId() and $distance <= 4){
			foreach(Facing::ALL as $side){
				if($this->findLog($pos->getSide($side), $visited, $distance + 1)){
					return true;
				}
			}
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->noDecay and !$this->checkDecay){
			$this->checkDecay = true;
			$this->getLevel()->setBlock($this, $this, false);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(!$this->noDecay and $this->checkDecay){
			$ev = new LeavesDecayEvent($this);
			$ev->call();
			if($ev->isCancelled() or $this->findLog($this)){
				$this->checkDecay = false;
				$this->getLevel()->setBlock($this, $this, false);
			}else{
				$this->getLevel()->useBreakOn($this);
			}
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->noDecay = true; //artificial leaves don't decay
		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getDrops(Item $item) : array{
		if($item->getBlockToolType() & BlockToolType::TYPE_SHEARS){
			return $this->getDropsForCompatibleTool($item);
		}

		$drops = [];
		if(mt_rand(1, 20) === 1){ //Saplings
			$drops[] = ItemFactory::get(Item::SAPLING, $this->treeType->getMagicNumber());
		}
		if(($this->treeType === TreeType::OAK() or $this->treeType === TreeType::DARK_OAK()) and mt_rand(1, 200) === 1){ //Apples
			$drops[] = ItemFactory::get(Item::APPLE);
		}

		return $drops;
	}

	public function getFlameEncouragement() : int{
		return 30;
	}

	public function getFlammability() : int{
		return 60;
	}
}
