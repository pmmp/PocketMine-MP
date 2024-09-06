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
use pocketmine\world\sound\MaceSmashGroundSound;
use pocketmine\world\sound\MaceSmashAirSound;
use pocketmine\player\Player;
use pocketmine\world\World;

class Mace extends TieredTool{

	public const MAX_DURABILITY = 501;

	public function getBlockToolType() : int{
		return BlockToolType::NONE;
	}

	public function getBlockToolHarvestLevel() : int{
		return $this->tier->getHarvestLevel();
	}
	
	public function getMaxDurability() : int{
		return self::MAX_DURABILITY;
	}

	public function getAttackPoints() : int{
		return $this->tier->getBaseAttackPoints() - 1;
	}

	public function onDestroyBlock(Block $block, array &$returnedItems) : bool{
		$world = $block->getPosition()->getWorld();
		$position = $block->getPosition();

		if(!$block->getBreakInfo()->breaksInstantly()){
			$world->addSound($position, new MaceSmashAirSound());
			return $this->applyDamage(1);
		}
		return false;
	}

	public function onAttackEntity(Entity $victim, array &$returnedItems) : bool{
		$world = $victim->getWorld();
		$position = $victim->getPosition();

		$world->addSound($position, new MaceSmashGroundSound());
		return $this->applyDamage(5);
	}
}
