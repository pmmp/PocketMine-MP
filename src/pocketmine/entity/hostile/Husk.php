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

namespace pocketmine\entity\hostile;

use pocketmine\entity\Ageable;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Monster;

class Husk extends Zombie{

	public const NETWORK_ID = self::HUSK;

	public function getName() : string{
		return "Husk";
	}

	public function entityBaseTick(int $diff = 1) : bool{
		return Monster::entityBaseTick($diff);
	}

	public function getArmorPoints() : int{
		return 2;
	}

	public function onCollideWithEntity(Entity $entity) : void{
		parent::onCollideWithEntity($entity);

		if($this->getTargetEntityId() === $entity->getId() and $entity instanceof Living){
			$entity->addEffect(new EffectInstance(Effect::getEffect(Effect::HUNGER), 7 * 20, 1));
		}
	}
}