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

namespace pocketmine\entity\profession;

use pocketmine\block\Block;
use pocketmine\data\bedrock\VillagerProfessionTypeIds;
use pocketmine\entity\trade\TradeRecipe;

abstract class VillagerProfession{

	/** @phpstan-param VillagerProfessionTypeIds::* $id */
	public function __construct(
		private readonly int $id,
		private readonly string $villagerName,
		private readonly Block $jobBlock,
		private readonly bool $canTrade = true
	){}

	/** @phpstan-return VillagerProfessionTypeIds::* */
	public function getId() : int{
		return $this->id;
	}

	public function getVillagerName() : string{
		return $this->villagerName;
	}

	public function getJobBlock() : Block{
		return $this->jobBlock;
	}

	public function canTrade() : bool{
		return $this->canTrade;
	}

	/** @phpstan-return list<TradeRecipe> */
	abstract public function getRecipes(int $biomeId) : array;
}
