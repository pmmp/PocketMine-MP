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

namespace pocketmine\block\tile;

use pocketmine\block\utils\StructureAnimationMode;
use pocketmine\block\utils\StructureAxes;
use pocketmine\block\utils\StructureBlockType;
use pocketmine\block\utils\StructureRotation;
use pocketmine\math\Vector3;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\StructureBlockUpdatePacket;
use pocketmine\world\World;

class StructureBlock extends Spawnable{
	public const TAG_ANIMATION_MODE = "animationMode";
	public const TAG_ANIMATION_SECONDS = "animationSeconds";
	public const TAG_DATA = "data";
	public const TAG_DATA_FIELD = "dataField";
	public const TAG_IGNORE_ENTITIES = "ignoreEntities";
	public const TAG_INTEGRITY = "integrity";
	public const TAG_IS_POWERED = "isPowered";
	public const TAG_MIRROR = "mirror";
	public const TAG_REDSTONE_SAVE_MODE = "redstoneSaveMode";
	public const TAG_REMOVE_BLOCKS = "removeBlocks";
	public const TAG_ROTATION = "rotation";
	public const TAG_SEED = "seed";
	public const TAG_SHOW_BOUNDING_BOX = "showBoundingBox";
	public const TAG_STRUCTURE_NAME = "structureName";
	public const TAG_X_STRUCTURE_OFFSET = "xStructureOffset";
	public const TAG_Y_STRUCTURE_OFFSET = "yStructureOffset";
	public const TAG_Z_STRUCTURE_OFFSET = "zStructureOffset";
	public const TAG_X_STRUCTURE_SIZE = "xStructureSize";
	public const TAG_Y_STRUCTURE_SIZE = "yStructureSize";
	public const TAG_Z_STRUCTURE_SIZE = "zStructureSize";

	private StructureAnimationMode $animationMode = StructureAnimationMode::NONE;
	private float $animationSeconds = 0;
	private StructureBlockType $type = StructureBlockType::SAVE;
	private string $dataField = "";	// unused
	private bool $ignoreEntities = false;
	private float $integrityValue = 100;
	private int $integritySeed = 0;	// 0 means random
	private bool $isPowered = false; // TODO : set by client, should be server side
	private StructureAxes $mirror;
	private int $redstoneSaveMode = 0;
	private bool $removeBlocks = false;
	private StructureRotation $rotation = StructureRotation::_0;
	private bool $showBoundingBox = true;
	private string $structureName = "";
	private Vector3 $structureOffset;
	private Vector3 $structureSize;
	// not stored in nbt but present in packet:
	// bool waterlogged
	// bool includePlayers
	// string paletteName
	// bool allowNonTickingChunks
	// int lastTouchedByPlayerId
	// Vector3 pivot

	public function __construct(World $world, Vector3 $pos){
		$this->mirror = new StructureAxes(false, false);
		$this->structureOffset = new Vector3(0, -1, 0);
		$this->structureSize = new Vector3(5, 5, 5);
		parent::__construct($world, $pos);
	}

	// TODO : check values
	public function readSaveData(CompoundTag $nbt) : void{
		$this->animationMode = StructureAnimationMode::tryFrom($nbt->getByte(self::TAG_ANIMATION_MODE, $this->animationMode->value))
			?? throw new NbtDataException("Invalid StructureAnimationMode value");
		$this->type = StructureBlockType::tryFrom($nbt->getInt(self::TAG_DATA, $this->type->value))
			?? throw new NbtDataException("Invalid StructureBlockType value");
		$this->rotation = StructureRotation::tryFrom($nbt->getByte(self::TAG_ROTATION, $this->rotation->value))
			?? throw new NbtDataException("Invalid StructureRotation value");
		$this->mirror = StructureAxes::fromInt($nbt->getByte(self::TAG_MIRROR, $this->mirror->toInt()));

		$this->animationSeconds = $nbt->getFloat(self::TAG_ANIMATION_SECONDS, $this->animationSeconds);
		$this->dataField = $nbt->getString(self::TAG_DATA_FIELD, $this->dataField);
		$this->ignoreEntities = (bool) $nbt->getByte(self::TAG_IGNORE_ENTITIES, (int) $this->ignoreEntities);
		$this->integrityValue = $nbt->getFloat(self::TAG_INTEGRITY, $this->integrityValue);
		$this->isPowered = (bool) $nbt->getByte(self::TAG_IS_POWERED, (int) $this->isPowered);
		$this->redstoneSaveMode = $nbt->getByte(self::TAG_REDSTONE_SAVE_MODE, $this->redstoneSaveMode);
		$this->removeBlocks = (bool) $nbt->getByte(self::TAG_REMOVE_BLOCKS, (int) $this->removeBlocks);
		$this->integritySeed = $nbt->getLong(self::TAG_SEED, $this->integritySeed);
		$this->showBoundingBox = (bool) $nbt->getByte(self::TAG_SHOW_BOUNDING_BOX, (int) $this->showBoundingBox);
		$this->structureName = $nbt->getString(self::TAG_STRUCTURE_NAME, $this->structureName);

		$this->structureOffset = new Vector3(
			$nbt->getInt(self::TAG_X_STRUCTURE_OFFSET, $this->structureOffset->getFloorX()),
			$nbt->getInt(self::TAG_Y_STRUCTURE_OFFSET, $this->structureOffset->getFloorY()),
			$nbt->getInt(self::TAG_Z_STRUCTURE_OFFSET, $this->structureOffset->getFloorZ()),
		);
		$this->structureSize = new Vector3(
			$nbt->getInt(self::TAG_X_STRUCTURE_SIZE, $this->structureSize->getFloorX()),
			$nbt->getInt(self::TAG_Y_STRUCTURE_SIZE, $this->structureSize->getFloorY()),
			$nbt->getInt(self::TAG_Z_STRUCTURE_SIZE, $this->structureSize->getFloorZ()),
		);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_ANIMATION_MODE, $this->animationMode->value);
		$nbt->setFloat(self::TAG_ANIMATION_SECONDS, $this->animationSeconds);
		$nbt->setInt(self::TAG_DATA, $this->type->value);
		$nbt->setString(self::TAG_DATA_FIELD, $this->dataField);
		$nbt->setByte(self::TAG_IGNORE_ENTITIES, (int) $this->ignoreEntities);
		$nbt->setFloat(self::TAG_INTEGRITY, $this->integrityValue);
		$nbt->setByte(self::TAG_IS_POWERED, (int) $this->isPowered);
		$nbt->setByte(self::TAG_MIRROR, $this->mirror->toInt());
		$nbt->setByte(self::TAG_REDSTONE_SAVE_MODE, $this->redstoneSaveMode);
		$nbt->setByte(self::TAG_REMOVE_BLOCKS, (int) $this->removeBlocks);
		$nbt->setByte(self::TAG_ROTATION, $this->rotation->value);
		$nbt->setLong(self::TAG_SEED, $this->integritySeed);
		$nbt->setByte(self::TAG_SHOW_BOUNDING_BOX, (int) $this->showBoundingBox);
		$nbt->setString(self::TAG_STRUCTURE_NAME, $this->structureName);
		$nbt->setInt(self::TAG_X_STRUCTURE_OFFSET, $this->structureOffset->getFloorX());
		$nbt->setInt(self::TAG_Y_STRUCTURE_OFFSET, $this->structureOffset->getFloorY());
		$nbt->setInt(self::TAG_Z_STRUCTURE_OFFSET, $this->structureOffset->getFloorZ());
		$nbt->setInt(self::TAG_X_STRUCTURE_SIZE, $this->structureSize->getFloorX());
		$nbt->setInt(self::TAG_Y_STRUCTURE_SIZE, $this->structureSize->getFloorY());
		$nbt->setInt(self::TAG_Z_STRUCTURE_SIZE, $this->structureSize->getFloorZ());
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$this->writeSaveData($nbt);
	}

	/**
	 *  @internal
	 *  @throws \ValueError
	 */
	public function updateFromPacket(StructureBlockUpdatePacket $packet) : void{
		//waterlogged
		$this->isPowered = $packet->isPowered;

		$data = $packet->structureEditorData;
		$this->structureName = $data->structureName;
		$this->dataField = $data->structureDataField;
		//includePlayers
		$this->showBoundingBox = $data->showBoundingBox;
		$this->type = StructureBlockType::from($data->structureBlockType);
		$this->redstoneSaveMode = $data->structureRedstoneSaveMove;

		$settings = $data->structureSettings;
		//paletteName
		$this->ignoreEntities = $settings->ignoreEntities;
		$this->removeBlocks = $settings->ignoreBlocks;
		//allowNonTickingChunks
		$this->structureSize = new Vector3($settings->dimensions->getX(), $settings->dimensions->getY(), $settings->dimensions->getZ());
		$this->structureOffset = new Vector3($settings->offset->getX(), $settings->offset->getY(), $settings->offset->getZ());
		//lastTouchedByPlayerId
		$this->rotation = StructureRotation::from($settings->rotation);
		$this->mirror = StructureAxes::fromInt($settings->mirror);
		$this->animationMode = StructureAnimationMode::from($settings->animationMode);
		$this->animationSeconds = $settings->animationSeconds;
		$this->integrityValue = $settings->integrityValue;
		$this->integritySeed = $settings->integritySeed;
		//pivot
	}

	public function getAnimationMode() : StructureAnimationMode{
		return $this->animationMode;
	}

	/**
	 * @return $this
	 */
	public function setAnimationMode(StructureAnimationMode $mode) : self{
		$this->animationMode = $mode;
		return $this;
	}

	public function getAnimationSeconds() : float{
		return $this->animationSeconds;
	}

	/**
	 * @return $this
	 */
	public function setAnimationSeconds(float $seconds) : self{
		$this->animationSeconds = $seconds;
		return $this;
	}

	public function getType() : StructureBlockType{
		return $this->type;
	}

	/**
	 * @return $this
	 */
	public function setType(StructureBlockType $type) : self{
		$this->type = $type;
		return $this;
	}

	public function getDataField() : string{
		return $this->dataField;
	}

	/**
	 * @return $this
	 */
	public function setDataField(string $dataField) : self{
		$this->dataField = $dataField;
		return $this;
	}

	public function getIgnoreEntities() : bool{
		return $this->ignoreEntities;
	}

	/**
	 * @return $this
	 */
	public function setIgnoreEntities(bool $ignore) : self{
		$this->ignoreEntities = $ignore;
		return $this;
	}

	public function getIntegrity() : float{
		return $this->integrityValue;
	}

	/**
	 * @return $this
	 */
	public function setIntegrity(float $value) : self{
		$this->integrityValue = $value;
		return $this;
	}

	public function getIntegritySeed() : int{
		return $this->integritySeed;
	}

	/**
	 * @return $this
	 */
	public function setIntegritySeed(int $seed) : self{
		$this->integritySeed = $seed;
		return $this;
	}

	public function getIsPowered() : bool{
		return $this->isPowered;
	}

	/**
	 * @return $this
	 */
	public function setIsPowered(bool $power) : self{
		$this->isPowered = $power;
		return $this;
	}

	public function getMirroredAxes() : StructureAxes{
		return $this->mirror;
	}

	/**
	 * @return $this
	 */
	public function setMirroredAxes(StructureAxes $axes) : self{
		$this->mirror = $axes;
		return $this;
	}

	// TODO : enum / bool ?
	public function getRedstoneSaveMode() : int{
		return $this->redstoneSaveMode;
	}

	/**
	 * @return $this
	 */
	public function setRedstoneSaveMode(int $saveMode) : self{
		$this->redstoneSaveMode = $saveMode;
		return $this;
	}

	public function getRemoveBlocks() : bool{
		return $this->removeBlocks;
	}

	/**
	 * @return $this
	 */
	public function setRemoveBlocks(bool $remove) : self{
		$this->removeBlocks = $remove;
		return $this;
	}

	public function getRotation() : StructureRotation{
		return $this->rotation;
	}

	/**
	 * @return $this
	 */
	public function setRotation(StructureRotation $rotation) : self{
		$this->rotation = $rotation;
		return $this;
	}

	public function getShowBoundingBox() : bool{
		return $this->showBoundingBox;
	}

	/**
	 * @return $this
	 */
	public function setShowBoundingBox(bool $show) : self{
		$this->showBoundingBox = $show;
		return $this;
	}

	public function getStructureName() : string{
		return $this->structureName;
	}

	/**
	 * @return $this
	 */
	public function setStructureName(string $name) : self{
		$this->structureName = $name;
		return $this;
	}

	public function getStructureOffset() : Vector3{
		return $this->structureOffset;
	}

	/**
	 * @return $this
	 */
	public function setStructureOffset(Vector3 $offset) : self{
		$this->structureOffset = $offset;
		return $this;
	}

	public function getStructureSize() : Vector3{
		return $this->structureSize;
	}

	/**
	 * @return $this
	 */
	public function setStructureSize(Vector3 $size) : self{
		$this->structureSize = $size;
		return $this;
	}
}
