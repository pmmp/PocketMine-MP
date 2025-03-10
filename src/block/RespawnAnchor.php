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
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Explosion;
use pocketmine\world\sound\AnchorChargeSound;

final class RespawnAnchor extends Opaque{
	protected const MIN_CHARGES = 0;
	protected const MAX_CHARGES = 4;

	private int $charges = self::MIN_CHARGES;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(self::MIN_CHARGES, self::MAX_CHARGES, $this->charges);
	}

	public function getCharges() : int {
		return $this->charges;
	}

	public function setCharges(int $charges) : self {
		if ($charges < self::MIN_CHARGES || $charges > self::MAX_CHARGES) {
			throw new \InvalidArgumentException("Charges must be between " . self::MIN_CHARGES . " and " . self::MAX_CHARGES . ", given: $charges");
		}
		$this->charges = $charges;
		return $this;
	}

	public function getLightLevel() : int{
		return $this->charges > 0 ? 3 + 4 * ($this->charges - 1) : 0;
	}


	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if ($item->getTypeId() === ItemTypeIds::fromBlockTypeId(BlockTypeIds::GLOWSTONE) && $this->charges < self::MAX_CHARGES) {
			$this->charges++;
			$this->position->getWorld()->setBlock($this->position, $this);
			$this->position->getWorld()->addSound($this->position, new AnchorChargeSound());

			return true;
		}

		if($this->charges > self::MIN_CHARGES){
			$this->explode($player);
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
