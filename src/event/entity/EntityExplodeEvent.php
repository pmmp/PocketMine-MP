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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\utils\Utils;
use pocketmine\world\Position;

/**
 * Called when a entity explodes
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityExplodeEvent extends EntityEvent implements Cancellable{
	use CancellableTrait;

	/** @var Position */
	protected $position;

	/** @var Block[] */
	protected $blocks;

	/** @var float */
	protected $yield;

	/**
	 * @param Block[]  $blocks
	 */
	public function __construct(Entity $entity, Position $position, array $blocks, float $yield){
		$this->entity = $entity;
		$this->position = $position;
		$this->blocks = $blocks;
		$this->yield = $yield;
	}

	public function getPosition() : Position{
		return $this->position;
	}

	/**
	 * @return Block[]
	 */
	public function getBlockList() : array{
		return $this->blocks;
	}

	/**
	 * @param Block[] $blocks
	 */
	public function setBlockList(array $blocks) : void{
		Utils::validateArrayValueType($blocks, function(Block $_) : void{});
		$this->blocks = $blocks;
	}

	public function getYield() : float{
		return $this->yield;
	}

	public function setYield(float $yield) : void{
		$this->yield = $yield;
	}
}
