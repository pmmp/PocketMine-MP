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

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\FlintSteel;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;
use function cos;
use function sin;
use const M_PI;

class TNT extends Solid{

	protected $id = self::TNT;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "TNT";
	}

	public function getHardness() : float{
		return 0;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($item instanceof FlintSteel or $item->hasEnchantment(Enchantment::FIRE_ASPECT)){
			if($item instanceof Durable){
				$item->applyDamage(1);
			}
			$this->ignite();
			return true;
		}

		return false;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void{
		if($entity instanceof Arrow and $entity->isOnFire()){
			$this->ignite();
		}
	}

	/**
	 * @return void
	 */
	public function ignite(int $fuse = 80){
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);

		$mot = (new Random())->nextSignedFloat() * M_PI * 2;
		$nbt = Entity::createBaseNBT($this->add(0.5, 0, 0.5), new Vector3(-sin($mot) * 0.02, 0.2, -cos($mot) * 0.02));
		$nbt->setShort("Fuse", $fuse);

		$tnt = Entity::createEntity("PrimedTNT", $this->getLevel(), $nbt);

		if($tnt !== null){
			$tnt->spawnToAll();
		}
	}

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}

	public function onIncinerate() : void{
		$this->ignite();
	}
}
