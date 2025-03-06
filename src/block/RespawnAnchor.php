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

use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\event\block\BlockPreExplodeEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Explosion;
use pocketmine\world\sound\AnchorChargeSound;

class RespawnAnchor extends Opaque{
	protected int $charges = 0;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(0, 4, $this->charges);
	}

	public function getCharges() : int{ return $this->charges; }

	public function setCharges(int $charges) : self{
		$this->charges = $charges;
		return $this;
	}

	public function getLightLevel() : int{
		return match ($this->charges){
			1 => 3,
			2 => 7,
			default => ($this->charges > 2 ? 15 : 0),
		};
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item->getTypeId() === VanillaBlocks::GLOWSTONE()->asItem()->getTypeId() && $this->charges < 4){
			if($player !== null && !$player->isCreative()){
				$item->pop();
			}
			$this->charges++;
			$this->position->getWorld()->setBlock($this->position, $this);
			$this->position->getWorld()->addSound($this->position, new AnchorChargeSound());

			return true;
		}

		if($player === null){
			return false;
		}

		if($this->charges >= 1){
			$this->explode($player);
			$this->position->getWorld()->setBlock($this->position, VanillaBlocks::AIR(), false);
			return true;
		}

		return false;
	}

	public function explode(Player $player) : void{
		$ev = new BlockPreExplodeEvent($this, 5, $player);
		$ev->setIncendiary(true);
	
		if($ev->isCancelled()){
			return;
		}
	
		$this->position->getWorld()->setBlock($this->position, VanillaBlocks::AIR());

		$explosion = new Explosion($this->position, $ev->getRadius(), $this);
		$explosion->setFireChance($ev->getFireChance());
		
		if($ev->isBlockBreaking()){
			$explosion->explodeA();
		}
		$explosion->explodeB();
	}
}
