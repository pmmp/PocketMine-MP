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

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Fire;
use pocketmine\block\Liquid;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\object\FallingBlock;
use pocketmine\level\Position;
use pocketmine\math\Facing;

/**
 * This trait handles falling behaviour for blocks that need them.
 * TODO: convert this into a dynamic component
 * @see Fallable
 */
trait FallableTrait{

	abstract protected function asPosition() : Position;

	abstract protected function getId() : int;

	abstract protected function getMeta() : int;

	public function onNearbyBlockChange() : void{
		$pos = $this->asPosition();
		$down = $pos->level->getBlock($pos->getSide(Facing::DOWN));
		if($down->getId() === Block::AIR or $down instanceof Liquid or $down instanceof Fire){
			$pos->level->setBlock($pos, BlockFactory::get(Block::AIR));

			$nbt = EntityFactory::createBaseNBT($pos->add(0.5, 0, 0.5));
			$nbt->setInt("TileID", $this->getId());
			$nbt->setByte("Data", $this->getMeta());

			/** @var FallingBlock $fall */
			$fall = EntityFactory::create(FallingBlock::class, $pos->getLevel(), $nbt);
			$fall->spawnToAll();
		}
	}
}
