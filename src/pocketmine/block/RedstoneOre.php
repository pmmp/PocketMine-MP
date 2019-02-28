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
use pocketmine\item\TieredTool;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function mt_rand;

class RedstoneOre extends Solid{
	/** @var BlockIdentifierFlattened */
	protected $idInfo;

	/** @var bool */
	protected $lit = false;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name){
		parent::__construct($idInfo, $name);
	}

	public function getId() : int{
		return $this->lit ? $this->idInfo->getSecondId() : parent::getId();
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->lit = $id === $this->idInfo->getSecondId();
	}

	public function getHardness() : float{
		return 3;
	}

	public function isLit() : bool{
		return $this->lit;
	}

	/**
	 * @param bool $lit
	 *
	 * @return $this
	 */
	public function setLit(bool $lit = true) : self{
		$this->lit = $lit;
		return $this;
	}

	public function getLightLevel() : int{
		return $this->lit ? 9 : 0;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		return $this->getLevel()->setBlock($this, $this, false);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->lit){
			$this->lit = true;
			$this->getLevel()->setBlock($this, $this); //no return here - this shouldn't prevent block placement
		}
		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->lit){
			$this->lit = true;
			$this->getLevel()->setBlock($this, $this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->lit){
			$this->lit = false;
			$this->level->setBlock($this, $this);
		}
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_IRON;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(Item::REDSTONE_DUST, 0, mt_rand(4, 5))
		];
	}

	protected function getXpDropAmount() : int{
		return mt_rand(1, 5);
	}
}
