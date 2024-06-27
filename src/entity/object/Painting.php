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

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\World;
use function ceil;

class Painting extends Entity{
	public const TAG_TILE_X = "TileX"; //TAG_Int
	public const TAG_TILE_Y = "TileY"; //TAG_Int
	public const TAG_TILE_Z = "TileZ"; //TAG_Int
	public const TAG_FACING_JE = "Facing"; //TAG_Byte
	public const TAG_DIRECTION_BE = "Direction"; //TAG_Byte
	public const TAG_MOTIVE = "Motive"; //TAG_String

	public static function getNetworkTypeId() : string{ return EntityIds::PAINTING; }

	public const DATA_TO_FACING = [
		0 => Facing::SOUTH,
		1 => Facing::WEST,
		2 => Facing::NORTH,
		3 => Facing::EAST
	];
	private const FACING_TO_DATA = [
		Facing::SOUTH => 0,
		Facing::WEST => 1,
		Facing::NORTH => 2,
		Facing::EAST => 3
	];

	protected Vector3 $blockIn;
	protected int $facing;
	protected PaintingMotive $motive;

	public function __construct(Location $location, Vector3 $blockIn, int $facing, PaintingMotive $motive, ?CompoundTag $nbt = null){
		$this->motive = $motive;
		$this->blockIn = $blockIn->asVector3();
		$this->facing = $facing;
		parent::__construct($location, $nbt);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		//these aren't accurate, but it doesn't matter since they aren't used (vanilla PC does something similar)
		return new EntitySizeInfo(0.5, 0.5);
	}

	protected function getInitialDragMultiplier() : float{ return 1.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(1);
		$this->setHealth(1);
		parent::initEntity($nbt);
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setInt(self::TAG_TILE_X, (int) $this->blockIn->x);
		$nbt->setInt(self::TAG_TILE_Y, (int) $this->blockIn->y);
		$nbt->setInt(self::TAG_TILE_Z, (int) $this->blockIn->z);

		$nbt->setByte(self::TAG_FACING_JE, self::FACING_TO_DATA[$this->facing]);
		$nbt->setByte(self::TAG_DIRECTION_BE, self::FACING_TO_DATA[$this->facing]); //Save both for full compatibility

		$nbt->setString(self::TAG_MOTIVE, $this->motive->getName());

		return $nbt;
	}

	protected function onDeath() : void{
		parent::onDeath();

		$drops = true;

		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
			$killer = $this->lastDamageCause->getDamager();
			if($killer instanceof Player && !$killer->hasFiniteResources()){
				$drops = false;
			}
		}

		if($drops){
			//non-living entities don't have a way to create drops generically yet
			$this->getWorld()->dropItem($this->location, VanillaItems::PAINTING());
		}
		$this->getWorld()->addParticle($this->location->add(0.5, 0.5, 0.5), new BlockBreakParticle(VanillaBlocks::OAK_PLANKS()));
	}

	protected function recalculateBoundingBox() : void{
		$side = $this->blockIn->getSide($this->facing);
		$this->boundingBox = self::getPaintingBB($this->facing, $this->getMotive())->offset($side->x, $side->y, $side->z);
	}

	public function onNearbyBlockChange() : void{
		parent::onNearbyBlockChange();

		if(!self::canFit($this->getWorld(), $this->blockIn->getSide($this->facing), $this->facing, false, $this->getMotive())){
			$this->kill();
		}
	}

	public function onRandomUpdate() : void{
		//NOOP
	}

	public function hasMovementUpdate() : bool{
		return false;
	}

	protected function updateMovement(bool $teleport = false) : void{

	}

	public function canBeCollidedWith() : bool{
		return false;
	}

	protected function sendSpawnPacket(Player $player) : void{
		$player->getNetworkSession()->sendDataPacket(AddPaintingPacket::create(
			$this->getId(), //TODO: entity unique ID
			$this->getId(),
			new Vector3(
				($this->boundingBox->minX + $this->boundingBox->maxX) / 2,
				($this->boundingBox->minY + $this->boundingBox->maxY) / 2,
				($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2
			),
			self::FACING_TO_DATA[$this->facing],
			$this->motive->getName()
		));
	}

	/**
	 * Returns the painting motive (which image is displayed on the painting)
	 */
	public function getMotive() : PaintingMotive{
		return $this->motive;
	}

	public function getFacing() : int{
		return $this->facing;
	}

	/**
	 * Returns the bounding-box a painting with the specified motive would have at the given position and direction.
	 */
	private static function getPaintingBB(int $facing, PaintingMotive $motive) : AxisAlignedBB{
		$width = $motive->getWidth();
		$height = $motive->getHeight();

		$horizontalStart = (int) (ceil($width / 2) - 1);
		$verticalStart = (int) (ceil($height / 2) - 1);

		return AxisAlignedBB::one()
			->trim($facing, 15 / 16)
			->extend(Facing::rotateY($facing, true), $horizontalStart)
			->extend(Facing::rotateY($facing, false), -$horizontalStart + $width - 1)
			->extend(Facing::DOWN, $verticalStart)
			->extend(Facing::UP, -$verticalStart + $height - 1);
	}

	/**
	 * Returns whether a painting with the specified motive can be placed at the given position.
	 */
	public static function canFit(World $world, Vector3 $blockIn, int $facing, bool $checkOverlap, PaintingMotive $motive) : bool{
		$width = $motive->getWidth();
		$height = $motive->getHeight();

		$horizontalStart = (int) (ceil($width / 2) - 1);
		$verticalStart = (int) (ceil($height / 2) - 1);

		$rotatedFace = Facing::rotateY($facing, false);

		$oppositeSide = Facing::opposite($facing);

		$startPos = $blockIn->asVector3()->getSide(Facing::opposite($rotatedFace), $horizontalStart)->getSide(Facing::DOWN, $verticalStart);

		for($w = 0; $w < $width; ++$w){
			for($h = 0; $h < $height; ++$h){
				$pos = $startPos->getSide($rotatedFace, $w)->getSide(Facing::UP, $h);

				$block = $world->getBlockAt($pos->x, $pos->y, $pos->z);
				if($block->isSolid() || !$block->getSide($oppositeSide)->isSolid()){
					return false;
				}
			}
		}

		if($checkOverlap){
			$bb = self::getPaintingBB($facing, $motive)->offset($blockIn->x, $blockIn->y, $blockIn->z);

			foreach($world->getNearbyEntities($bb) as $entity){
				if($entity instanceof self){
					return false;
				}
			}
		}

		return true;
	}
}
