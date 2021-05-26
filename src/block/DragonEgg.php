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

use pocketmine\block\utils\Fallable;
use pocketmine\block\utils\FallableTrait;
use pocketmine\event\block\BlockTeleportEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\particle\DragonEggTeleportParticle;
use pocketmine\world\World;
use function max;
use function min;
use function mt_rand;

class DragonEgg extends Transparent implements Fallable{
	use FallableTrait;

	public function getLightLevel() : int{
		return 1;
	}

	public function tickFalling() : ?Block{
		return null;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->teleport();
		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		if($player !== null && !$player->getGamemode()->equals(GameMode::CREATIVE())){
			$this->teleport();
			return true;
		}
		return false;
	}

	public function teleport() : void{
		for($tries = 0; $tries < 16; ++$tries){
			$block = $this->pos->getWorld()->getBlockAt(
				$this->pos->x + mt_rand(-16, 16),
				max(World::Y_MIN, min(World::Y_MAX - 1, $this->pos->y + mt_rand(-8, 8))),
				$this->pos->z + mt_rand(-16, 16)
			);
			if($block instanceof Air){
				$ev = new BlockTeleportEvent($this, $block->pos);
				$ev->call();
				if($ev->isCancelled()){
					break;
				}

				$blockPos = $ev->getTo();
				$this->pos->getWorld()->addParticle($this->pos, new DragonEggTeleportParticle($this->pos->x - $blockPos->x, $this->pos->y - $blockPos->y, $this->pos->z - $blockPos->z));
				$this->pos->getWorld()->setBlock($this->pos, VanillaBlocks::AIR());
				$this->pos->getWorld()->setBlock($blockPos, $this);
				break;
			}
		}
	}
}
