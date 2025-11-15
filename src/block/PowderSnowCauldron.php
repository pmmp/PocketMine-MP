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

use pocketmine\block\tile\Cauldron as TileCauldron;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\CauldronEmptyPowderSnowSound;
use pocketmine\world\sound\CauldronFillPowderSnowSound;
use pocketmine\world\sound\Sound;
use function assert;

final class PowderSnowCauldron extends FillableCauldron{

	public function getFillSound() : Sound{
		return new CauldronFillPowderSnowSound();
	}

	public function getEmptySound() : Sound{
		return new CauldronEmptyPowderSnowSound();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		match($item->getTypeId()){
			ItemTypeIds::POWDER_SNOW_BUCKET => $this->addFillLevels(self::MAX_FILL_LEVEL, $item, VanillaItems::BUCKET(), $returnedItems),
			ItemTypeIds::BUCKET => $this->removeFillLevels(self::MAX_FILL_LEVEL, $item, VanillaItems::POWDER_SNOW_BUCKET(), $returnedItems),
			ItemTypeIds::WATER_BUCKET, ItemTypeIds::LAVA_BUCKET => $this->mix($item, VanillaItems::BUCKET(), $returnedItems),
			default => null
		};

		return true;
	}

	public function hasEntityCollision() : bool{ return true; }

	public function onEntityInside(Entity $entity) : bool{
		// Powder snow cauldron: extinguish burning entities like water cauldron
		if($entity->isOnFire()){
			$entity->extinguish();
			$this->position->getWorld()->setBlock($this->position, $this->withFillLevel($this->getFillLevel() - 1));
		}

		return true;
	}
}
