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

use pocketmine\item\SpawnEgg;
use pocketmine\item\TieredTool;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\tile\MobSpawner;
use pocketmine\tile\Tile;
use function mt_rand;

class MonsterSpawner extends Transparent{

	protected $id = self::MONSTER_SPAWNER;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 5;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function getName() : string{
		return "Monster Spawner";
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	protected function getXpDropAmount() : int{
		return mt_rand(15, 43);
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($item instanceof SpawnEgg){
			/** @var MobSpawner $tile */
			$tile = Tile::createTile(Tile::MOB_SPAWNER, $this->level, MobSpawner::createNBT($this));
			$tile->setEntityId($item->getDamage());

			if($player instanceof Player){
				$item->pop();
				$player->getInventory()->setItemInHand($item);
			}
		}
		return true;
	}
}
