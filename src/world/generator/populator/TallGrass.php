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

namespace pocketmine\world\generator\populator;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class TallGrass extends Populator{
	/** @var int */
	private $randomAmount = 1;
	/** @var int */
	private $baseAmount = 0;

	public function setRandomAmount(int $amount) : void{
		$this->randomAmount = $amount;
	}

	public function setBaseAmount(int $amount) : void{
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $world, int $chunkX, int $chunkZ, Random $random) : void{
		$amount = $random->nextRange(0, $this->randomAmount) + $this->baseAmount;

		$block = VanillaBlocks::TALL_GRASS();
		for($i = 0; $i < $amount; ++$i){
			$x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
			$z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
			$y = $this->getHighestWorkableBlock($world, $x, $z);

			if($y !== -1 and $this->canTallGrassStay($world, $x, $y, $z)){
				$world->setBlockAt($x, $y, $z, $block);
			}
		}
	}

	private function canTallGrassStay(ChunkManager $world, int $x, int $y, int $z) : bool{
		$b = $world->getBlockAt($x, $y, $z)->getId();
		return ($b === BlockLegacyIds::AIR or $b === BlockLegacyIds::SNOW_LAYER) and $world->getBlockAt($x, $y - 1, $z)->getId() === BlockLegacyIds::GRASS;
	}

	private function getHighestWorkableBlock(ChunkManager $world, int $x, int $z) : int{
		for($y = 127; $y >= 0; --$y){
			$b = $world->getBlockAt($x, $y, $z)->getId();
			if($b !== BlockLegacyIds::AIR and $b !== BlockLegacyIds::LEAVES and $b !== BlockLegacyIds::LEAVES2 and $b !== BlockLegacyIds::SNOW_LAYER){
				return $y + 1;
			}
		}

		return -1;
	}
}
