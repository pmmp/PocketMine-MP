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

namespace pocketmine\maps;

use pocketmine\math\Vector2;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\MapDecoration;
use pocketmine\network\mcpe\protocol\types\MapTrackedObject;
use pocketmine\utils\Color;

class MapData{

	/** @var int */
	protected $mapId = 0;
	/** @var int */
	protected $xCenter = 0, $zCenter = 0;
	/** @var int */
	protected $dimension = DimensionIds::OVERWORLD;
	/** @var int */
	protected $scale = 0;
	/** @var Color[][] */
	protected $colors = [];
	/** @var MapDecoration[] */
	protected $decorations = [];
	/** @var MapTrackedObject[] */
	protected $trackedObjects = [];

	/** @var ClientboundMapItemDataPacket|null */
	protected $cachedDataPacket = null;
	/** @var bool */
	protected $dirty = true;
	/** @var bool */
	protected $fullyExplored = true;

	public function __construct(int $mapId){
		$this->mapId = $mapId;
	}

	/**
	 * @return int
	 */
	public function getMapId() : int{
		return $this->mapId;
	}

	/**
	 * @return int
	 */
	public function getDimension() : int{
		return $this->dimension;
	}

	/**
	 * @param int $dimension
	 */
	public function setDimension(int $dimension) : void{
		$this->dimension = $dimension;
		$this->markDirty();
	}

	/**
	 * @return int
	 */
	public function getScale() : int{
		return $this->scale;
	}

	/**
	 * @param int $scale
	 */
	public function setScale(int $scale) : void{
		$this->scale = $scale;
		$this->markDirty();
	}

	/**
	 * @return Color[][]
	 */
	public function getColors() : array{
		return $this->colors;
	}

	/**
	 * @param Color[][] $colors
	 */
	public function setColors(array $colors) : void{
		$this->colors = $colors;
		$this->markDirty();
	}

	/**
	 * @param int   $x
	 * @param int   $y
	 * @param Color $color
	 */
	public function setColorAt(int $x, int $y, Color $color) : void{
		$this->colors[$y][$x] = $color;
		$this->markDirty();
	}

	/**
	 * @param int $x
	 * @param int $y
	 *
	 * @return null|Color
	 */
	public function getColorAt(int $x, int $y) : ?Color{
		return $this->colors[$y][$x] ?? null;
	}

	/**
	 * @param int $x
	 * @param int $z
	 */
	public function setCenter(int $x, int $z) : void{
		$this->xCenter = $x;
		$this->zCenter = $z;
		$this->markDirty();
	}

	/**
	 * @return Vector2
	 */
	public function getCenter() : Vector2{
		return new Vector2($this->xCenter, $this->zCenter);
	}

	public function calculateMapCenter(int $x, int $z, int $scale) : void{
		$i = 128 * (1 << $scale);
		$j = (int) floor(($x + 64.0) / $i);
		$k = (int) floor(($z + 64.0) / $i);

		$this->setCenter($j * $i + $i / 2 - 64, $k * $i + $i / 2 - 64);
	}

	/**
	 * @return bool
	 */
	public function isDirty() : bool{
		return $this->dirty;
	}

	/**
	 * Marks map data as dirty to send data packet
	 */
	public function markDirty() : void{
		$this->dirty = true;
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->dimension = $nbt->getByte("dimension", 0);
		$this->xCenter = $nbt->getInt("xCenter", 0);
		$this->zCenter = $nbt->getInt("zCenter", 0);
		$this->scale = $nbt->getByte("scale", 0);
		$this->fullyExplored = boolval($nbt->getByte("fullyExplored", 1));
		if($this->scale > 4) $this->scale = 0;

		$height = $nbt->getShort("height", 128);
		$width = $nbt->getShort("width", 128);

		if($nbt->hasTag("colors", IntArrayTag::class)){
			$byteColors = $nbt->getIntArray("colors", []);

			for($y = 0; $y < $height; $y++){
				for($x = 0; $x < $width; $x++){
					$this->colors[$y][$x] = Color::fromABGR($byteColors[$x + $y * $width] ?? 0);
				}
			}
		}

		if($nbt->hasTag("decorations", CompoundTag::class)){
			$decos = $nbt->getCompoundTag("decorations");
			for($i = 0; $i < $decos->getCount(); $i++){
				$this->decorations[$i] = MapDecoration::fromNBT($decos->getListTag(strval($i)));
			}
		}
	}

	public function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte("dimension", $this->dimension);
		$nbt->setInt("xCenter", $this->xCenter);
		$nbt->setInt("zCenter", $this->zCenter);
		$nbt->setByte("scale", $this->scale);
		$nbt->setShort("width", $w = 128 * ($this->scale + 1));
		$nbt->setShort("height", $h = 128 * ($this->scale + 1));
		$nbt->setByte("fullyExplored", intval($this->fullyExplored));

		if(count($this->colors) > 0){
			$byteColors = [];
			for($y = 0; $y < $h; $y++){
				for($x = 0; $x < $w; $x++){
					$color = $this->colors[$y][$x] ?? new Color(0, 0, 0);
					$byteColors[$x + $y * $w] = $color->toABGR();
				}
			}

			$nbt->setIntArray("colors", $byteColors);
		}

		$decos = new CompoundTag("decorations");
		foreach($this->decorations as $i => $decoration){
			$decos->setTag($decoration->toNBT(strval($i)));
		}

		$nbt->setTag($decos);
	}

	/**
	 * @return MapDecoration[]
	 */
	public function getDecorations() : array{
		return $this->decorations;
	}

	/**
	 * @param MapDecoration[] $decorations
	 */
	public function setDecorations(array $decorations) : void{
		$this->decorations = array_keys($decorations);
		$this->markDirty();
	}

	/**
	 * @return MapTrackedObject[]
	 */
	public function getTrackedObjects() : array{
		return $this->trackedObjects;
	}

	/**
	 * @param MapTrackedObject[] $trackedObjects
	 */
	public function setTrackedObjects(array $trackedObjects) : void{
		$this->trackedObjects = $trackedObjects;
		$this->markDirty();
	}

	/**
	 * @return ClientboundMapItemDataPacket
	 */
	public function createDataPacket() : ClientboundMapItemDataPacket{
		$pk = new ClientboundMapItemDataPacket();
		$pk->mapId = $this->mapId;
		$pk->dimensionId = $this->dimension;
		$pk->height = ($this->scale + 1) * 128;
		$pk->width = ($this->scale + 1) * 128;
		$pk->trackedEntities = $this->trackedObjects;
		$pk->decorations = $this->decorations;
		$pk->colors = $this->colors;
		$pk->scale = $this->scale;
		$pk->eids = []; // why??
		$pk->xOffset = $this->xCenter;
		$pk->yOffset = $this->zCenter;

		return $pk;
	}

	/**
	 * @return ClientboundMapItemDataPacket
	 */
	public function getDataPacket() : ClientboundMapItemDataPacket{
		if($this->isDirty()){
			$this->cachedDataPacket = $this->createDataPacket();

			$this->dirty = false;
		}
		return $this->cachedDataPacket;
	}

	/**
	 * @return bool
	 */
	public function isFullyExplored() : bool{
		return $this->fullyExplored;
	}

	/**
	 * @param bool $fullyExplored
	 */
	public function setFullyExplored(bool $fullyExplored) : void{
		$this->fullyExplored = $fullyExplored;
	}
}
