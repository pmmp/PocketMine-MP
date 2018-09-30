<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\TieredTool;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\Player;

class RedstoneOre extends Solid{

	protected $itemId = self::REDSTONE_ORE;

	/** @var bool */
	protected $lit = false;

	public function __construct(){

	}

	public function getId() : int{
		return $this->lit ? self::GLOWING_REDSTONE_ORE : self::REDSTONE_ORE;
	}

	public function getName() : string{
		return "Redstone Ore";
	}

	public function getHardness() : float{
		return 3;
	}

	public function isLit() : bool{
		return $this->lit;
	}

	public function setLit(bool $lit = true) : void{
		$this->lit = $lit;
	}

	public function getLightLevel() : int{
		return $this->lit ? 9 : 0;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		return $this->getLevel()->setBlock($this, $this, false);
	}

	public function onActivate(Item $item, Player $player = null) : bool{
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
