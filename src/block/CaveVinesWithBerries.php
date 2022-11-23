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

use pocketmine\data\runtime\RuntimeDataReader;
use pocketmine\data\runtime\RuntimeDataWriter;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\GlowBerriesPickSound;
use function mt_rand;

class CaveVinesWithBerries extends CaveVines{

	/** In Bedrock this called "head" */
	protected bool $tip = false;

	public function getRequiredStateDataBits() : int{
		return 6;
	}

	public function describeState(RuntimeDataWriter|RuntimeDataReader $w) : void{
		parent::describeState($w);
		$w->bool($this->tip);
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();

		$this->tip = !$this->getSide(Facing::DOWN) instanceof CaveVines;
		return $this;
	}

	public function isTip() : bool{
		return $this->tip;
	}

	/** @return $this */
	public function setTip(bool $tip) : self{
		$this->tip = $tip;
		return $this;
	}

	public function getLightLevel() : int{
		return 14;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [VanillaItems::GLOW_BERRIES()];
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		$this->position->getWorld()->dropItem($this->position, VanillaItems::GLOW_BERRIES());
		$this->position->getWorld()->addSound($this->position, new GlowBerriesPickSound());

		$this->position->getWorld()->setBlock($this->position, VanillaBlocks::CAVE_VINES()->setAge(mt_rand(0, self::MAX_AGE)));
		return true;
	}
}
