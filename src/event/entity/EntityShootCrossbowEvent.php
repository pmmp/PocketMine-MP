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

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\utils\Utils;
use function count;

/**
 * @phpstan-extends EntityEvent<Living>
 */
final class EntityShootCrossbowEvent extends EntityEvent implements Cancellable{
	use CancellableTrait;

	/** @phpstan-param non-empty-list<Entity> $projectiles */
	public function __construct(
		Living $shooter,
		private Item $crossbow,
		private array $projectiles
	){
		$this->entity = $shooter;
	}

	public function getCrossbow() : Item{
		return $this->crossbow;
	}

	/**
	 * Returns projectiles shot by crossbow.
	 * Can be more than 1 if multishot enchantment has applied.
	 *
	 * Note: This might not return a Projectile if a plugin modified the target entity.
	 *
	 * @return Entity[]
	 * @phpstan-return non-empty-list<Entity>
	 */
	public function getProjectiles() : array{
		return $this->projectiles;
	}

	/**
	 * @param Entity[] $projectiles
	 * @phpstan-param non-empty-list<Entity> $projectiles
	 */
	public function setProjectiles(array $projectiles) : void{
		Utils::validateArrayValueType($projectiles, function(Entity $_) : void{});
		if(count($projectiles) === 0){
			throw new \LogicException("Must have at least one projectile");
		}
		$this->projectiles = $projectiles;
	}
}
