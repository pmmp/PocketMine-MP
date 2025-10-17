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

use pocketmine\block\utils\BlockEventHelper;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use function count;

final class Nylium extends Block{

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaBlocks::NETHERRACK()->asItem()
		];
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$world = $this->position->getWorld();
		$above = $this->getSide(Facing::UP);
		if($above instanceof Opaque){
			BlockEventHelper::spread($this, VanillaBlocks::NETHERRACK(), $this);
			return;
		}

		$random = new Random();
		for($i = 0; $i < 4; ++$i){
			$pos = $this->position->add($random->nextRange(-1, 1), $random->nextRange(-2, 0), $random->nextRange(-1, 1));
			$block = $world->getBlock($pos);
			if($block instanceof Netherrack && !$world->getBlock($pos->up()) instanceof Opaque){
				BlockEventHelper::spread($block, $this, $this);
			}
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer && $this->getSide(Facing::UP)->getTypeId() !== BlockTypeIds::AIR){
			$item->pop();
			$this->growVegetation(new Random());
			return true;
		}
		return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
	}

	private function growVegetation(Random $random) : void{
		$world = $this->position->getWorld();
		$tx = new BlockTransaction($world);

		$vegetation = $this->getTypeId() === BlockTypeIds::CRIMSON_NYLIUM ? [
			VanillaBlocks::CRIMSON_FUNGUS(),
			VanillaBlocks::CRIMSON_ROOTS()
		] : [
			VanillaBlocks::WARPED_FUNGUS(),
			VanillaBlocks::WARPED_ROOTS(),
			VanillaBlocks::NETHER_SPROUTS()
		];

		for($x = -2; $x <= 2; ++$x){
			for($z = -2; $z <= 2; ++$z){
				if($random->nextBoundedInt(3) === 0){
					$pos = $this->position->add($x, 1, $z);
					$replace = $world->getBlock($pos);
					$place = $vegetation[$random->nextBoundedInt(count($vegetation))];
					if($replace->getTypeId() === BlockTypeIds::AIR && $place->canBePlacedAt($replace, Vector3::zero(), Facing::DOWN, true)){
						$tx->addBlock($pos, $place);
					}
				}
			}
		}
		$tx->apply();
	}
}
