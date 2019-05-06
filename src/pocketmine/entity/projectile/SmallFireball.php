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

namespace pocketmine\entity\projectile;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Living;
use pocketmine\level\GameRules;
use pocketmine\math\RayTraceResult;

class SmallFireball extends Projectile{

	public const NETWORK_ID = self::SMALL_FIREBALL;

	public $height = 0.3125;
	public $width = 0.3125;

	protected $damage = 5.0;
	protected $life = 0;

	public function getName() : string{
		return "SmallFireball";
	}

	public function initEntity() : void{
		parent::initEntity();

		$this->life = $this->namedtag->getInt("life", 0);
	}

	public function onUpdate(int $currentTick) : bool{
		if($this->isAlive() and !$this->closed and !$this->isFlaggedForDespawn()){
			$this->setOnFire(1);

			if($this->life++ > 600){
				$this->flagForDespawn();
			}
		}
		return parent::onUpdate($currentTick);
	}

	public function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);

		$this->flagForDespawn();

		$owner = $this->getOwningEntity();
		if($owner instanceof Living){
			if($this->level->getGameRules()->getBool(GameRules::RULE_MOB_GRIEFING)){
				$block = $this->level->getBlock($this);
				if($block instanceof Air){
					$this->level->setBlock($this, BlockFactory::get(Block::FIRE));
				}
			}
		}
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setInt("life", $this->life);
	}
}