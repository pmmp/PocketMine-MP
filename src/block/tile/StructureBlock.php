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

use pocketmine\block\utils\StructureBlockType;
use pocketmine\math\Vector3;
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

	private int $animationMode = 0;	// byte
	private float $animationSeconds = 0;
	private StructureBlockType $type = StructureBlockType::SAVE;
	private string $dataField = "";	// unused
	private bool $ignoreEntities = false;
	private float $integrityValue = 100;
	private int $integritySeed = 0;	// long, 0 means random
	private bool $isPowered = false; // TODO : set by client, should be server side
	private int $mirror = 0;	// byte
	private int $redstoneSaveMode = 0;
	private bool $removeBlocks = false;
	private int $rotation = 0;	// byte
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
		$this->structureOffset = new Vector3(0, -1, 0);
		$this->structureSize = new Vector3(5, 5, 5);
		parent::__construct($world, $pos);
	}

	// TODO : check values
	public function readSaveData(CompoundTag $nbt) : void{
		$this->animationMode = $nbt->getByte(self::TAG_ANIMATION_MODE, $this->animationMode);
		$this->animationSeconds = $nbt->getFloat(self::TAG_ANIMATION_SECONDS, $this->animationSeconds);
		$this->type = StructureBlockType::fromInt($nbt->getByte(self::TAG_DATA, StructureBlockType::toInt($this->type)));
		$this->dataField = $nbt->getString(self::TAG_DATA_FIELD, $this->dataField);
		$this->ignoreEntities = (bool) $nbt->getByte(self::TAG_IGNORE_ENTITIES, (int) $this->ignoreEntities);
		$this->integrityValue = $nbt->getFloat(self::TAG_INTEGRITY, $this->integrityValue);
		$this->isPowered = (bool) $nbt->getByte(self::TAG_IS_POWERED, (int) $this->isPowered);
		$this->mirror = $nbt->getByte(self::TAG_MIRROR, $this->mirror);
		$this->redstoneSaveMode = $nbt->getByte(self::TAG_REDSTONE_SAVE_MODE, $this->redstoneSaveMode);
		$this->removeBlocks = (bool) $nbt->getByte(self::TAG_REMOVE_BLOCKS, (int) $this->removeBlocks);
		$this->rotation = $nbt->getByte(self::TAG_ROTATION, $this->rotation);
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
		$nbt->setByte(self::TAG_ANIMATION_MODE, $this->animationMode);
		$nbt->setFloat(self::TAG_ANIMATION_SECONDS, $this->animationSeconds);
		$nbt->setInt(self::TAG_DATA, StructureBlockType::toInt($this->type));
		$nbt->setString(self::TAG_DATA_FIELD, $this->dataField);
		$nbt->setByte(self::TAG_IGNORE_ENTITIES, (int) $this->ignoreEntities);
		$nbt->setFloat(self::TAG_INTEGRITY, $this->integrityValue);
		$nbt->setByte(self::TAG_IS_POWERED, (int) $this->isPowered);
		$nbt->setByte(self::TAG_MIRROR, $this->mirror);
		$nbt->setByte(self::TAG_REDSTONE_SAVE_MODE, $this->redstoneSaveMode);
		$nbt->setByte(self::TAG_REMOVE_BLOCKS, (int) $this->removeBlocks);
		$nbt->setByte(self::TAG_ROTATION, $this->rotation);
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
	 *  @throws \UnexpectedValueException
	 */
		public function updateFromPacket(StructureBlockUpdatePacket $packet) : void{
		//waterlogged
		$this->isPowered = $packet->isPowered;

		$data = $packet->structureEditorData;
		$this->structureName = $data->structureName;
		$this->dataField = $data->structureDataField;
		//includePlayers
		$this->showBoundingBox = $data->showBoundingBox;
		$this->type = StructureBlockType::fromInt($data->structureBlockType);
		$this->redstoneSaveMode = $data->structureRedstoneSaveMove;

		$settings = $data->structureSettings;
		//paletteName
		$this->ignoreEntities = $settings->ignoreEntities;
		$this->removeBlocks = $settings->ignoreBlocks;
		//allowNonTickingChunks
		$this->structureSize = new Vector3($settings->dimensions->getX(), $settings->dimensions->getY(), $settings->dimensions->getZ());
		$this->structureOffset = new Vector3($settings->offset->getX(), $settings->offset->getY(), $settings->offset->getZ());
		//lastTouchedByPlayerId
		$this->rotation = $settings->rotation;
		$this->mirror = $settings->mirror;
		$this->animationMode = $settings->animationMode;
		$this->animationSeconds = $settings->animationSeconds;
		$this->integrityValue = $settings->integrityValue;
		$this->integritySeed = $settings->integritySeed;
		//pivot
	}
}
