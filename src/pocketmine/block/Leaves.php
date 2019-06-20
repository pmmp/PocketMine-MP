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
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\World;
use function mt_rand;

class Leaves extends Transparent{
	/** @var TreeType */
	protected $treeType;

	/** @var bool */
	protected $noDecay = false;
	/** @var bool */
	protected $checkDecay = false;

	public function __construct(BlockIdentifier $idInfo, string $name, TreeType $treeType, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(0.2, BlockToolType::TYPE_SHEARS));
		$this->treeType = $treeType;
	}

	protected function writeStateToMeta() : int{
		return ($this->noDecay ? BlockLegacyMetadata::LEAVES_FLAG_NO_DECAY : 0) | ($this->checkDecay ? BlockLegacyMetadata::LEAVES_FLAG_CHECK_DECAY : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->noDecay = ($stateMeta & BlockLegacyMetadata::LEAVES_FLAG_NO_DECAY) !== 0;
		$this->checkDecay = ($stateMeta & BlockLegacyMetadata::LEAVES_FLAG_CHECK_DECAY) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1100;
	}

	public function diffusesSkyLight() : bool{
		return true;
	}


	protected function findLog(Block $pos, array &$visited = [], int $distance = 0) : bool{
		$index = World::blockHash($pos->x, $pos->y, $pos->z);
		if(isset($visited[$index])){
			return false;
		}
		$visited[$index] = true;

		if($pos instanceof Wood){ //type doesn't matter
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
			$this->getWorld()->setBlock($this, $this, false);
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
				$this->getWorld()->setBlock($this, $this, false);
			}else{
				$this->getWorld()->useBreakOn($this);
			}
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->noDecay = true; //artificial leaves don't decay
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getDrops(Item $item) : array{
		if($item->getBlockToolType() & BlockToolType::TYPE_SHEARS){
			return $this->getDropsForCompatibleTool($item);
		}

		$drops = [];
		if(mt_rand(1, 20) === 1){ //Saplings
			$drops[] = ItemFactory::get(Item::SAPLING, $this->treeType->getMagicNumber());
		}
		if(($this->treeType->equals(TreeType::OAK()) or $this->treeType->equals(TreeType::DARK_OAK())) and mt_rand(1, 200) === 1){ //Apples
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
