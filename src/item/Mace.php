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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\world\sound\MaceSmashGroundSound;

class Mace extends TieredTool {

	public const MAX_DURABILITY = 501;

	public function getBlockToolType() : int{
		return BlockToolType::NONE;
	}

	public function getMaxDurability() : int{
		return self::MAX_DURABILITY;
	}

	public function getAttackPoints() : int{
		return $this->tier->getBaseAttackPoints() - 1;
	}

	public function onDestroyBlock(Block $block, array &$returnedItems) : bool{
		if (!$block->getBreakInfo()->breaksInstantly()) {
			return $this->applyDamage(1);
		}
		return false;
	}

	public function onAttackEntity(Entity $victim, array &$returnedItems) : bool{
		$event = new EntityDamageByEntityEvent($holder, $victim, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getAttackPoints());
		$victim->attack($event);
		if ($event->isCancelled()) {
			return false;
		}

		$hitVector = new Vector3($victim->getPosition()->x, $victim->getPosition()->y, $victim->getPosition()->z);
		$victim->getWorld()->addSound($hitVector, new MaceSmashGroundSound());
		return $this->applyDamage(5);
	}
}
