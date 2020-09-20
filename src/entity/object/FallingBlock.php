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
use pocketmine\block\utils\Fallable;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use function abs;

class FallingBlock extends Entity{

	public static function getNetworkTypeId() : string{ return EntityIds::FALLING_BLOCK; }

	public $width = 0.98;
	public $height = 0.98;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	/** @var Block */
	protected $block;

	public $canCollide = false;

	public function __construct(Location $location, Block $block, ?CompoundTag $nbt = null){
		$this->block = $block;
		parent::__construct($location, $nbt);
	}

	public static function parseBlockNBT(BlockFactory $factory, CompoundTag $nbt) : Block{
		$blockId = 0;

		//TODO: 1.8+ save format
		if(($tileIdTag = $nbt->getTag("TileID")) instanceof IntTag){
			$blockId = $tileIdTag->getValue();
		}elseif(($tileTag = $nbt->getTag("Tile")) instanceof ByteTag){
			$blockId = $tileTag->getValue();
		}

		if($blockId === 0){
			throw new \UnexpectedValueException("Missing block info from NBT");
		}

		$damage = $nbt->getByte("Data", 0);

		return $factory->get($blockId, $damage);
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

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$this->isFlaggedForDespawn()){
			$world = $this->getWorld();
			$pos = $this->location->add(-$this->width / 2, $this->height, -$this->width / 2)->floor();

			$this->block->position($world, $pos->x, $pos->y, $pos->z);

			$blockTarget = null;
			if($this->block instanceof Fallable){
				$blockTarget = $this->block->tickFalling();
			}

			if($this->onGround or $blockTarget !== null){
				$this->flagForDespawn();

				$block = $world->getBlock($pos);
				if(!$block->canBeReplaced() or !$world->isInWorld($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()) or ($this->onGround and abs($this->location->y - $this->location->getFloorY()) > 0.001)){
					//FIXME: anvils are supposed to destroy torches
					$world->dropItem($this->location, $this->block->asItem());
				}else{
					$ev = new EntityBlockChangeEvent($this, $block, $blockTarget ?? $this->block);
					$ev->call();
					if(!$ev->isCancelled()){
						$world->setBlock($pos, $ev->getTo());
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
		$nbt->setByte("Data", $this->block->getMeta());

		return $nbt;
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setInt(EntityMetadataProperties::VARIANT, RuntimeBlockMapping::getInstance()->toRuntimeId($this->block->getFullId()));
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return $vector3->add(0, 0.49, 0); //TODO: check if height affects this
	}
}
