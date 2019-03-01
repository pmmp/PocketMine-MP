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

use pocketmine\item\Item;
use pocketmine\Player;

class MonsterEgg extends Solid{

	protected $id = self::MONSTER_EGG;

	public const STONE_MONSTER_EGG = 0;
	public const COBBLESTONE_MONSTER_EGG = 1;
	public const STONE_BRICK_MONSTER_EGG = 2;
	public const MOSSY_STONE_BRICK_MONSTER_EGG = 3;
	public const CRACKED_STONE_BRICK_MONSTER_EGG = 4;
	public const CHISELED_STONE_BRICK_MONSTER_EGG = 5;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		switch($this->meta){
			case self::STONE_MONSTER_EGG:
				return "Stone Monster Egg";
			case self::COBBLESTONE_MONSTER_EGG:
				return "Cobblestone Monster Egg";
			case self::STONE_BRICK_MONSTER_EGG:
				return "Stone Brick Monster Egg";
			case self::MOSSY_STONE_BRICK_MONSTER_EGG:
				return "Mossy Stone Brick Monster Egg";
			case self::CRACKED_STONE_BRICK_MONSTER_EGG:
				return "Cracked Stone Brick Monster Egg";
			case self::CHISELED_STONE_BRICK_MONSTER_EGG:
				return "Chiseled Stone Brick Monster Egg";
		}

		return "Infested Block";
	}

	public function getHardness() : float{
		return 0.75;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function onBreak(Item $item, Player $player = null) : bool{
		// TODO: Spawn silverfish

		return parent::onBreak($item, $player);
	}
}