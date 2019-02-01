<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\level\biome;

use pocketmine\entity\Entity;
use pocketmine\utils\WeightedRandomItem;

class SpawnListEntry extends WeightedRandomItem{
	/** @var Entity */
	public $entityClass;
	public $minGroupCount = 0;
	public $maxGroupCount = 0;

	public function __construct(string $entityClass, int $itemWeight, int $minGroupCount, int $maxGroupCount){
		parent::__construct($itemWeight);

		$this->entityClass = $entityClass;
		$this->minGroupCount = $minGroupCount;
		$this->maxGroupCount = $maxGroupCount;
	}
}