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

namespace pocketmine\entity\behaviour;


use pocketmine\block\Block;
use pocketmine\entity\Entity;

class DestroyWhileFalling extends Behaviour{

	/** @var int[] */
	private $destroyables;
	/** @var bool */
	private $dropItems;

	public function __construct(Entity $entity, array $destroyables, bool $dropItems){
		parent::__construct($entity);

		$this->destroyables = $destroyables;
		$this->dropItems = $dropItems;
	}

	public function update(int $tickDiff = 1) : void{
		$block = $this->entity->getLevel()->getBlock($this->entity->getPosition());
		foreach($this->destroyables as $destroyableId) {
			if($block->getId() == $destroyableId) {
				if($this->dropItems) {
					$this->entity->getLevel()->dropItem($this->entity->getPosition(), $block->asItem());
				}

				$this->entity->getLevel()->setBlock($block, Block::get(Block::AIR));
			}
		}
	}
}