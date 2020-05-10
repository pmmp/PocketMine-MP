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

namespace pocketmine\entity\object;

use pocketmine\block\Block;
use pocketmine\block\Fire;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Explosion;
use pocketmine\level\GameRules;
use pocketmine\level\generator\end\End;

class EnderCrystal extends Entity{
	public const NETWORK_ID = self::ENDER_CRYSTAL;

	public $height = 0.98;
	public $width = 0.98;

	public $gravity = 0;
	public $drag = 0;

	public function onMovementUpdate() : void{
		// NOOP
	}

	public function onUpdate(int $currentTick) : bool{
		if($this->level->getProvider()->getPath() === End::class){
			if($this->level->getBlock($this)->getId() !== Block::FIRE){
				$this->level->setBlock($this, new Fire());
			}
		}

		return parent::onUpdate($currentTick);
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);

		if(!$this->isFlaggedForDespawn() and !$source->isCancelled() and $source->getCause() !== EntityDamageEvent::CAUSE_FIRE and $source->getCause() !== EntityDamageEvent::CAUSE_FIRE_TICK){
			$this->flagForDespawn();

			if($this->level->getGameRules()->getBool(GameRules::RULE_TNT_EXPLODES)){
				$exp = new Explosion($this, 6, $this);

				$exp->explodeA();
				$exp->explodeB();
			}
		}
	}

	public function canBeCollidedWith() : bool{
		return false;
	}

	public function setShowBase(bool $value) : void{
		$this->setGenericFlag(self::DATA_FLAG_SHOWBASE, $value);
	}

	public function showBase() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_SHOWBASE);
	}
}
