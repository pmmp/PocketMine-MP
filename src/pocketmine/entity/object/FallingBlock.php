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

namespace pocketmine\entity\object;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Fallable;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

class FallingBlock extends Entity{
	public const NETWORK_ID = self::FALLING_BLOCK;

	public $width = 0.98;
	public $height = 0.98;

	protected $baseOffset = 0.49;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	/** @var Block */
	protected $block;

	public $canCollide = false;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$blockId = 0;

		//TODO: 1.8+ save format
		if($nbt->hasTag("TileID", IntTag::class)){
			$blockId = $nbt->getInt("TileID");
		}elseif($nbt->hasTag("Tile", ByteTag::class)){
			$blockId = $nbt->getByte("Tile");
		}

		if($blockId === 0){
			throw new \UnexpectedValueException("Invalid " . get_class($this) . " entity: block ID is 0 or missing");
		}

		$damage = $nbt->getByte("Data", 0);

		$this->block = BlockFactory::get($blockId, $damage);

		$this->propertyManager->setInt(self::DATA_VARIANT, $this->block->getRuntimeId());
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function canBeMovedByCurrents() : bool{
		return false;
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$this->isFlaggedForDespawn()){
			$pos = $this->add(-$this->width / 2, $this->height, -$this->width / 2)->floor();

			$this->block->position($this->level, $pos->x, $pos->y, $pos->z);

			$blockTarget = null;
			if($this->block instanceof Fallable){
				$blockTarget = $this->block->tickFalling();
			}

			if($this->onGround or $blockTarget !== null){
				$this->flagForDespawn();

				$block = $this->level->getBlock($pos);
				if($block->getId() > 0 and $block->isTransparent() and !$block->canBeReplaced()){
					//FIXME: anvils are supposed to destroy torches
					$this->getLevel()->dropItem($this, $this->block->getItem());
				}else{
					$ev = new EntityBlockChangeEvent($this, $block, $blockTarget ?? $this->block);
					$ev->call();
					if(!$ev->isCancelled()){
						$this->getLevel()->setBlock($pos, $ev->getTo());
					}
				}
				$hasUpdate = true;
			}
		}

		return $hasUpdate;
	}

	public function getBlock() : Block{
		return $this->block;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setInt("TileID", $this->block->getId());
		$nbt->setByte("Data", $this->block->getDamage());

		return $nbt;
	}
}
