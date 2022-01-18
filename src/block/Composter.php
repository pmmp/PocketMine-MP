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

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\ComposterUtils;
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\player\Player;
use pocketmine\world\sound\ComposterEmptySound;
use pocketmine\world\sound\ComposterFillSound;
use pocketmine\world\sound\ComposterFillSuccessSound;
use pocketmine\world\sound\ComposterReadySound;
use function abs;
use function mt_rand;

class Composter extends Transparent{
	public const CROP_GROWTH_EMITTER_PARTICLE = "minecraft:crop_growth_emitter";

	protected int $composter_fill_level = 0;

	protected function writeStateToMeta() : int{
		return $this->composter_fill_level;
	}

	public function writeStateToItemMeta() : int{
		return $this->composter_fill_level;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->composter_fill_level = BlockDataSerializer::readBoundedInt("composter_fill_level", $stateMeta, 0, 8);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	protected function recalculateCollisionBoxes() : array{
		$boxes = [$this->getSideCollisionBox(Facing::DOWN)];
		foreach (Facing::HORIZONTAL as $side) {
			$boxes[] = $this->getSideCollisionBox($side);
		}
		return $boxes;
	}

	protected function getSideCollisionBox(int $face = Facing::NORTH) : AxisAlignedBB{
		$empty = abs(15 - 2 * $this->composter_fill_level) - (int) ($this->composter_fill_level === 0);
		return ($face === Facing::DOWN || $face === Facing::UP) ? AxisAlignedBB::one()->contract(2 / 16, 0, 2 / 16)->trim(Facing::UP, $empty / 16) : AxisAlignedBB::one()->trim(Facing::opposite($face), 14 / 16);
	}

	public function isEmpty() : bool{
		return $this->composter_fill_level === 0;
	}

	public function isReady() : bool{
		return $this->composter_fill_level === 8;
	}

	public function getComposterFillLevel() : int{
		return $this->composter_fill_level;
	}

	/**
	 * @throws \Exception
	 */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if ($player instanceof Player) {
			if ($this->compost($player, clone $item)) {
				$item->pop();
			}
		}
		return true;
	}

	public function pushCollidedEntities() : void{
		if ($this->composter_fill_level === 7) return;
		foreach (
			$this->position->getWorld()->getNearbyEntities(
				$this->getSideCollisionBox(Facing::DOWN)->extend(Facing::UP, 0.25)->offset(
					$this->position->getFloorX(),
					$this->position->getFloorY(),
					$this->position->getFloorZ()
				)
			) as $entity) {
			if ($entity instanceof Player || $entity instanceof Projectile) continue;
			$motion = $entity->getMotion();
			$motion->y = 0.2; //Lower can make entity clip through the block, the game cannot catch up the speed of the entity? :/
			$entity->setMotion($motion);
		}
	}

	protected function spawnParticleEffect() : void{
		$packet = SpawnParticleEffectPacket::create(
			DimensionIds::OVERWORLD,
			-1,
			$this->position->add(0.5, 0.5, 0.5),
			self::CROP_GROWTH_EMITTER_PARTICLE
		);
		foreach ($this->position->getWorld()->getViewersForPosition($this->position) as $player) {
			$player->getNetworkSession()->sendDataPacket($packet);
		}
	}

	/**
	 * @throws \Exception
	 */
	public function compost(Block|Player $origin, ?Item $item = null) : bool{
		if ($this->composter_fill_level >= 8) {
			$this->position->getWorld()->dropItem(
				$this->position->add(0.5, 0.85, 0.5),
				new Fertilizer(new ItemIdentifier(ItemIds::DYE, 15), "Bone Meal"),
				new Vector3(0, 0, 0)
			);
			$this->position->getWorld()->addSound($this->position, new ComposterEmptySound());
			$this->spawnParticleEffect();
			$this->composter_fill_level = 0;
			$this->position->getWorld()->setBlock($this->position, $this);
			return false;
		} else {
			if ($item === null || !ComposterUtils::isCompostable($item)) return false;
			$this->spawnParticleEffect();

			$percent = ComposterUtils::getPercentage($item);
			if (mt_rand(1, 100) <= $percent) {
				$this->pushCollidedEntities();
				++$this->composter_fill_level;
				if ($this->composter_fill_level === 8) {
					$this->position->getWorld()->addSound($this->position, new ComposterReadySound());
				} else {
					$this->position->getWorld()->addSound($this->position, new ComposterFillSuccessSound());
				}
			} else {
				$this->position->getWorld()->addSound($this->position, new ComposterFillSound());
				return true;
			}
			$this->position->getWorld()->setBlock($this->position, $this);
			return true;
		}
	}

	public function getDrops(Item $item) : array{
		return $this->composter_fill_level === 8 ? [
			VanillaBlocks::COMPOSTER()->asItem(),
			new Fertilizer(new ItemIdentifier(ItemIds::DYE, 15), "Bone Meal")
		] : [
			VanillaBlocks::COMPOSTER()->asItem()
		];
	}

	public function getFlameEncouragement() : int{
		return 5;
	}

	public function getFlammability() : int{
		return 20;
	}

	public function getFuelTime() : int{
		return 50;
	}
}
