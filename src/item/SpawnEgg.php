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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use function lcg_value;

class SpawnEgg extends Item{

	/** @var string */
	private $entityClass;

	/**
	 * @param int    $id
	 * @param int    $variant
	 * @param string $name
	 *
	 * @param string $entityClass instanceof Entity
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(int $id, int $variant, string $name, string $entityClass){
		parent::__construct($id, $variant, $name);
		Utils::testValidInstance($entityClass, Entity::class);
		$this->entityClass = $entityClass;
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		$nbt = EntityFactory::createBaseNBT($blockReplace->getPos()->add(0.5, 0, 0.5), null, lcg_value() * 360, 0);

		if($this->hasCustomName()){
			$nbt->setString("CustomName", $this->getCustomName());
		}

		$entity = EntityFactory::create($this->entityClass, $player->getWorld(), $nbt);
		$this->pop();
		$entity->spawnToAll();
		//TODO: what if the entity was marked for deletion?
		return ItemUseResult::SUCCESS();
	}
}
