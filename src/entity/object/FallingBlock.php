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
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\utils\Fallable;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\sound\BlockBreakSound;
use function abs;
use function min;
use function round;

class FallingBlock extends Entity{
	private const TAG_FALLING_BLOCK = "FallingBlock"; //TAG_Compound
	private const TAG_TILE_ID = "TileID"; //TAG_Int
	private const TAG_TILE = "Tile"; //TAG_Byte
	private const TAG_DATA = "Data"; //TAG_Byte

	public static function getNetworkTypeId() : string{ return EntityIds::FALLING_BLOCK; }

	protected Block $block;

	public function __construct(Location $location, Block $block, ?CompoundTag $nbt = null){
		$this->block = $block;
		parent::__construct($location, $nbt);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.98, 0.98); }

	protected function getInitialDragMultiplier() : float{ return 0.02; }

	protected function getInitialGravity() : float{ return 0.04; }

	public static function parseBlockNBT(RuntimeBlockStateRegistry $factory, CompoundTag $nbt) : Block{

		//TODO: 1.8+ save format
		$blockDataUpgrader = GlobalBlockStateHandlers::getUpgrader();
		if(($fallingBlockTag = $nbt->getCompoundTag(self::TAG_FALLING_BLOCK)) !== null){
			try{
				$blockStateData = $blockDataUpgrader->upgradeBlockStateNbt($fallingBlockTag);
			}catch(BlockStateDeserializeException $e){
				throw new SavedDataLoadingException("Invalid falling block blockstate: " . $e->getMessage(), 0, $e);
			}
		}else{
			if(($tileIdTag = $nbt->getTag(self::TAG_TILE_ID)) instanceof IntTag){
				$blockId = $tileIdTag->getValue();
			}elseif(($tileTag = $nbt->getTag(self::TAG_TILE)) instanceof ByteTag){
				$blockId = $tileTag->getValue();
			}else{
				throw new SavedDataLoadingException("Missing legacy falling block info");
			}
			$damage = $nbt->getByte(self::TAG_DATA, 0);

			try{
				$blockStateData = $blockDataUpgrader->upgradeIntIdMeta($blockId, $damage);
			}catch(BlockStateDeserializeException $e){
				throw new SavedDataLoadingException("Invalid legacy falling block data: " . $e->getMessage(), 0, $e);
			}
		}

		try{
			$blockStateId = GlobalBlockStateHandlers::getDeserializer()->deserialize($blockStateData);
		}catch(BlockStateDeserializeException $e){
			throw new SavedDataLoadingException($e->getMessage(), 0, $e);
		}

		return $factory->fromStateId($blockStateId);
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
			$pos = $this->location->add(-$this->size->getWidth() / 2, $this->size->getHeight(), -$this->size->getWidth() / 2)->floor();

			$this->block->position($world, $pos->x, $pos->y, $pos->z);

			$blockTarget = null;
			if($this->block instanceof Fallable){
				$blockTarget = $this->block->tickFalling();
			}

			if($this->onGround || $blockTarget !== null){
				$this->flagForDespawn();

				$blockResult = $blockTarget ?? $this->block;
				$block = $world->getBlock($pos);
				if(!$block->canBeReplaced() || !$world->isInWorld($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()) || ($this->onGround && abs($this->location->y - $this->location->getFloorY()) > 0.001)){
					$world->dropItem($this->location, $this->block->asItem());
					$world->addSound($pos->add(0.5, 0.5, 0.5), new BlockBreakSound($blockResult));
				}else{
					$ev = new EntityBlockChangeEvent($this, $block, $blockResult);
					$ev->call();
					if(!$ev->isCancelled()){
						$b = $ev->getTo();
						$world->setBlock($pos, $b);
						if($this->onGround && $b instanceof Fallable && ($sound = $b->getLandSound()) !== null){
							$world->addSound($pos->add(0.5, 0.5, 0.5), $sound);
						}
					}
				}
				$hasUpdate = true;
			}
		}

		return $hasUpdate;
	}

	protected function onHitGround() : ?float{
		if($this->block instanceof Fallable){
			$damagePerBlock = $this->block->getFallDamagePerBlock();
			if($damagePerBlock > 0 && ($fallenBlocks = round($this->fallDistance) - 1) > 0){
				$damage = min($fallenBlocks * $damagePerBlock, $this->block->getMaxFallDamage());
				foreach($this->getWorld()->getCollidingEntities($this->getBoundingBox()) as $entity){
					if($entity instanceof Living){
						$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_FALLING_BLOCK, $damage);
						$entity->attack($ev);
					}
				}
			}
			if(!$this->block->onHitGround($this)){
				$this->flagForDespawn();
			}
		}
		return null;
	}

	public function getBlock() : Block{
		return $this->block;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setTag(self::TAG_FALLING_BLOCK, GlobalBlockStateHandlers::getSerializer()->serialize($this->block->getStateId())->toNbt());

		return $nbt;
	}

	public function getPickedItem() : ?Item{
		return $this->block->asItem();
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setInt(EntityMetadataProperties::VARIANT, TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($this->block->getStateId()));
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return $vector3->add(0, 0.49, 0); //TODO: check if height affects this
	}
}
