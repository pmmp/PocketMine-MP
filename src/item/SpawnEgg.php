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
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use function lcg_value;

abstract class SpawnEgg extends Item{

	abstract protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity;

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		$entity = $this->createEntity($player->getWorld(), $blockReplace->getPos()->add(0.5, 0, 0.5), lcg_value() * 360, 0);

		if($this->hasCustomName()){
			$entity->setNameTag($this->getCustomName());
		}
		$this->pop();
		$entity->spawnToAll();
		//TODO: what if the entity was marked for deletion?
		return ItemUseResult::SUCCESS();
	}
}
