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
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function mt_rand;

class RedstoneOre extends Opaque{
	/** @var BlockIdentifierFlattened */
	protected $idInfo;

	/** @var bool */
	protected $lit = false;

	public function __construct(BlockIdentifierFlattened $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(3.0, BlockToolType::PICKAXE, ToolTier::IRON()->getHarvestLevel()));
	}

	public function getId() : int{
		return $this->lit ? $this->idInfo->getSecondId() : parent::getId();
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->lit = $id === $this->idInfo->getSecondId();
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

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->lit){
			$this->lit = true;
			$this->pos->getWorld()->setBlock($this->pos, $this); //no return here - this shouldn't prevent block placement
		}
		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->lit){
			$this->lit = true;
			$this->pos->getWorld()->setBlock($this->pos, $this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->lit){
			$this->lit = false;
			$this->pos->getWorld()->setBlock($this->pos, $this);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaItems::REDSTONE_DUST()->setCount(mt_rand(4, 5))
		];
	}

	protected function getXpDropAmount() : int{
		return mt_rand(1, 5);
	}
}
