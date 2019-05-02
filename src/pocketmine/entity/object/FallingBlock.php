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

use pocketmine\block\Anvil;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\utils\Fallable;
use pocketmine\entity\behaviour\Behaviour;
use pocketmine\entity\behaviour\DestroyWhileFalling;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\types\EntityMetadataProperties;
use function get_class;

class FallingBlock extends Entity{
	public const NETWORK_ID = self::FALLING_BLOCK;

	public const STATE_SOLIDIFY = 0;
	public const STATE_DROP_ITEM = 1;
	public const STATE_DESTROY_BLOCK = 2;

	public $width = 0.98;
	public $height = 0.98;

	protected $baseOffset = 0.49;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	/** @var Block */
	protected $block;

	/** @var Behaviour[]|null */
	protected $behaviours;

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
		if ($this->block instanceof Anvil) {
			$this->behaviours = [new DestroyWhileFalling($this, [Block::TORCH, Block::COLORED_TORCH_RG, Block::COLORED_TORCH_BP,
					Block::LIT_REDSTONE_TORCH, Block::UNLIT_REDSTONE_TORCH, Block::UNDERWATER_TORCH], true)];
		}

		$this->propertyManager->setInt(EntityMetadataProperties::VARIANT, $this->block->getRuntimeId());
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
			$pos = $this->add(-$this->width / 2, $this->height, -$this->width / 2)->floor();

			$this->block->position($this->level, $pos->x, $pos->y, $pos->z);

			$blockTarget = null;
			if($this->block instanceof Fallable){
				$blockTarget = $this->block->tickFalling();
			}

			if($this->behaviours != null) {
				foreach($this->behaviours as $behaviour) {
					$behaviour->update($tickDiff);
				}
			}


			if($this->onGround or $blockTarget !== null){
				// Check if the hit block (on ground) accepts the block falling ontop of it
				$block = $this->level->getBlock($pos);

				// FIXME: anvils are supposed to destroy torches
				$blockDown = $this->level->getBlock($block->getSide(Facing::DOWN));
				$state = $blockDown->canBlockLand($this->block);
				switch($state) {
					case self::STATE_SOLIDIFY:
						$this->flagForDespawn();

						$ev = new EntityBlockChangeEvent($this, $block, $blockTarget ?? $this->block);
						$ev->call();
						if(!$ev->isCancelled()){
							$this->getLevel()->setBlock($pos, $ev->getTo(), true);
						}
						break;

					case self::STATE_DROP_ITEM:
						$this->flagForDespawn();
						$this->getLevel()->dropItem($this, $this->block->asItem());
						break;

					case self::STATE_DESTROY_BLOCK:
						$this->level->setBlock($blockDown, Block::get(Block::AIR));
						$this->getLevel()->dropItem($blockDown, $blockDown->asItem());
						break;
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
}
