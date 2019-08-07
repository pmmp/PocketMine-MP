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
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\generator\object\Tree;
use function mt_rand;

class Sapling extends Flowable{

	/** @var bool */
	protected $ready = false;
	/** @var TreeType */
	private $treeType;

	public function __construct(BlockIdentifier $idInfo, string $name, TreeType $treeType, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? BlockBreakInfo::instant());
		$this->treeType = $treeType;
	}

	protected function writeStateToMeta() : int{
		return ($this->ready ? BlockLegacyMetadata::SAPLING_FLAG_READY : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->ready = ($stateMeta & BlockLegacyMetadata::SAPLING_FLAG_READY) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1000;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$down = $this->getSide(Facing::DOWN);
		if($down->getId() === BlockLegacyIds::GRASS or $down->getId() === BlockLegacyIds::DIRT or $down->getId() === BlockLegacyIds::FARMLAND){
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof Fertilizer){
			Tree::growTree($this->pos->getWorld(), $this->pos->x, $this->pos->y, $this->pos->z, new Random(mt_rand()), $this->treeType);

			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			$this->pos->getWorld()->useBreakOn($this->pos);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->pos->getWorld()->getFullLightAt($this->pos->x, $this->pos->y, $this->pos->z) >= 8 and mt_rand(1, 7) === 1){
			if($this->ready){
				Tree::growTree($this->pos->getWorld(), $this->pos->x, $this->pos->y, $this->pos->z, new Random(mt_rand()), $this->treeType);
			}else{
				$this->ready = true;
				$this->pos->getWorld()->setBlock($this->pos, $this);
			}
		}
	}

	public function getFuelTime() : int{
		return 100;
	}
}
