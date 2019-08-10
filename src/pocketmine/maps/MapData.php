<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\maps;

use pocketmine\item\Map;
use pocketmine\level\Level;
use pocketmine\maps\renderer\MapRenderer;
use pocketmine\maps\renderer\VanillaMapRenderer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\MapDecoration;
use pocketmine\network\mcpe\protocol\types\MapTrackedObject;
use pocketmine\Player;
use pocketmine\utils\Color;

class MapData{

	/** @var int */
	protected $id = 0;
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

	/** @var bool */
	protected $isLocked = false;
	/** @var bool */
	protected $unlimitedTracking = true;
	/** @var bool */
	protected $virtual = false;

	/** @var MapInfo[] */
	protected $playersMap = [];
	/** @var MapRenderer[] */
	protected $renderers = [];
	/** @var bool */
	protected $dirty = false;
	/** @var string */
	protected $levelName = "";

	/** @var Color[] */
	protected static $emptyColors = [];

	/**
	 * MapData constructor.
	 *
	 * @param int   $mapId
	 * @param MapRenderer[]|null $renderers
	 */
	public function __construct(int $mapId, ?array $renderers = null){
		$this->id = $mapId;

		if($renderers === null){
			$renderers = [new VanillaMapRenderer()];
		}
		$this->renderers = $renderers;

		foreach($renderers as $renderer){
			$renderer->initialize($this);
		}

		if(empty(self::$emptyColors)){
			self::$emptyColors = array_fill(0, 128, array_fill(0, 128, new Color(0, 0, 0, 0)));
		}

		$this->colors = self::$emptyColors;
	}

	/**
	 * @return int
	 */
	public function getId() : int{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id) : void{
		$this->id = $id;
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
		$this->markAsDirty();
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
		$this->markAsDirty();
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
		$this->markAsDirty();
	}

	/**
	 * @param int   $x
	 * @param int   $y
	 * @param Color $color
	 */
	public function setColorAt(int $x, int $y, Color $color) : void{
		$this->colors[$y][$x] = $color;
		$this->markAsDirty();
	}

	/**
	 * @param int $x
	 * @param int $y
	 *
	 * @return Color
	 */
	public function getColorAt(int $x, int $y) : Color{
		return $this->colors[$y][$x] ?? new Color(0, 0, 0, 0);
	}

	/**
	 * @param int $x
	 * @param int $z
	 */
	public function setCenter(int $x, int $z) : void{
		$this->xCenter = $x;
		$this->zCenter = $z;

		$this->markAsDirty();
	}

	public function getCenterX() : int{
		return $this->xCenter;
	}

	public function getCenterZ() : int{
		return $this->zCenter;
	}

	public function calculateMapCenter(int $x, int $z) : void{
		$i = 128 * (1 << $this->getScale());
		$j = (int) floor(($x + 64.0) / $i);
		$k = (int) floor(($z + 64.0) / $i);
		$this->setCenter($j * $i + $i / 2 - 64, $k * $i + $i / 2 - 64);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->setLevelName($nbt->getString("worldName", ""));
		$this->dimension = $nbt->getByte("dimension", 0);
		$this->xCenter = $nbt->getInt("xCenter", 0);
		$this->zCenter = $nbt->getInt("zCenter", 0);
		$this->scale = $nbt->getByte("scale", 0);
		$this->isLocked = boolval($nbt->getByte("isLocked", 0));
		if($this->scale > 4) $this->scale = 0;
		if($nbt->hasTag("colors", IntArrayTag::class)){
			$colors = $nbt->getIntArray("colors");
			for($y = 0; $y < 128; $y++){
				for($x = 0; $x < 128; $x++){
					if(isset($colors[$x + $y * 128])){
						$this->colors[$y][$x] = Color::fromABGR($colors[$x + $y * 128]);
					}
				}
			}
		}
	}

	public function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setString("worldName", $this->levelName);
		$nbt->setByte("dimension", $this->dimension);
		$nbt->setInt("xCenter", $this->xCenter);
		$nbt->setInt("zCenter", $this->zCenter);
		$nbt->setByte("scale", $this->scale);
		$nbt->setByte("isLocked", intval($this->isLocked));
		if(count($this->colors) > 0){
			$colors = [];
			for($y = 0; $y < 128; $y++){
				for($x = 0; $x < 128; $x++){
					if(isset($this->colors[$y]) and isset($this->colors[$y][$x])){
						$colors[$x + $y * 128] = $this->colors[$y][$x]->toABGR();
					}
				}
			}

			$nbt->setIntArray("colors", $colors, true);
		}
	}

	/**
	 * @return MapDecoration[]
	 */
	public function getDecorations() : array{
		return $this->decorations;
	}

	/**
	 * @param string $identifier
	 *
	 * @return MapDecoration|null
	 */
	public function getDecoration(string $identifier) : ?MapDecoration{
		return $this->decorations[$identifier] ?? null;
	}

	/**
	 * @param string        $identifier
	 * @param MapDecoration $decoration
	 */
	public function setDecoration(string $identifier, MapDecoration $decoration) : void{
		$this->decorations[$identifier] = $decoration;
	}

	/**
	 * @param string $identifier
	 */
	public function removeDecoration(string $identifier) : void{
		unset($this->decorations[$identifier]);
	}

	/**
	 * @param MapDecoration[] $decorations
	 */
	public function setDecorations(array $decorations) : void{
		$this->decorations = array_keys($decorations);
		$this->markAsDirty();
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
		$this->markAsDirty();
	}

	public function onMapCrated(Player $player) : void{
		foreach($this->renderers as $renderer){
			$renderer->onMapCreated($player, $this);
		}

		$this->setLevelName($player->level->getFolderName());
	}

	public function isLocked() : bool{
		return $this->isLocked;
	}

	public function setLocked(bool $locked) : void{
		$this->isLocked = $locked;
	}

	public function getMapInfo(Player $player) : MapInfo{
		if(!isset($this->playersMap[spl_object_hash($player)])){
			$this->playersMap[spl_object_hash($player)] = new MapInfo($player);

			$mo = new MapTrackedObject();
			$mo->entityUniqueId = $player->getId();
			$mo->type = MapTrackedObject::TYPE_PLAYER;
			$this->trackedObjects[$player->getName()] = $mo;
		}
		return $this->playersMap[spl_object_hash($player)];
	}

	/**
	 * @param int $x
	 * @param int $y
	 */
	public function updateTextureAt(int $x, int $y) : void{
		foreach($this->playersMap as $info){
			$info->updateTextureAt($x, $y);
		}

		$this->markAsDirty();
	}

	/**
	 * Renders map for holder player
	 *
	 * @param Player $holder
	 */
	public function renderMap(Player $holder) : void{
		foreach($this->renderers as $renderer){
			$renderer->render($this, $holder);
		}
	}

	/**
	 * Adds the player passed to the list of visible players and checks to see which players are visible
	 *
	 * @param Player $player
	 * @param Map    $mapStack
	 */
	public function updateVisiblePlayers(Player $player, Map $mapStack){
		if(!isset($this->playersMap[$hash = spl_object_hash($player)])){
			$this->playersMap[$hash] = new MapInfo($player);
			$mo = new MapTrackedObject();
			$mo->entityUniqueId = $player->getId();
			$mo->type = MapTrackedObject::TYPE_PLAYER;
			$this->trackedObjects[$player->getName()] = $mo;
		}

		if($mapStack->isMapDisplayPlayers()){
			foreach($this->playersMap as $info){
				$pi = $info->player;
				if($pi->isOnline() and $pi->isAlive() and $pi->level->getDimension() === $this->dimension and ($pi->getInventory()->contains($mapStack) or $pi->getOffHandInventory()->contains($mapStack))){
					if(!$mapStack->isOnItemFrame()){
						$this->updateDecorations(MapDecoration::TYPE_PLAYER, $pi->level, $pi->getName(), $pi->getFloorX(), $pi->getFloorZ(), $pi->getYaw());
					}
				}else{
					unset($this->playersMap[spl_object_hash($pi)]);
				}
			}
		}

		$mapNbt = $mapStack->getNamedTag();
		if($mapNbt->hasTag("Decorations", ListTag::class)){
			$decos = $mapNbt->getListTag("Decorations");
			foreach($decos->getValue() as $v){
				if($v instanceof CompoundTag){
					if(!isset($this->decorations[$v->getString("id")])){
						$this->updateDecorations($v->getByte("type"), $player->level, $v->getString("id"), (int) $v->getDouble("x"), (int) $v->getDouble("z"), $v->getDouble("rot"));
					}
				}
			}
		}
	}

	/**
	 * Updates decorations
	 *
	 * @param int        $type
	 * @param Level      $worldIn
	 * @param String     $entityIdentifier
	 * @param int        $worldX
	 * @param int        $worldZ
	 * @param float      $rotation
	 * @param Color|null $color
	 */
	public function updateDecorations(int $type, Level $worldIn, string $entityIdentifier, int $worldX, int $worldZ, float $rotation, ?Color $color = null){
		$i = 1 << $this->scale;
		$f = ($worldX - $this->xCenter) / $i;
		$f1 = ($worldZ - $this->zCenter) / $i;
		$b0 = (int) (($f * 2.0) + 0.5);
		$b1 = (int) (($f1 * 2.0) + 0.5);
		$j = 63;

		$rotation = $rotation + ($rotation < 0.0 ? -8.0 : 8.0);
		$b2 = ((int) ($rotation * 16.0 / 360.0));

		if($f >= (-$j) and $f1 >= (-$j) and $f <= $j and $f1 <= $j){
			if($this->dimension > DimensionIds::OVERWORLD){
				$k = (int) ($worldIn->getTime() / 10);
				$b2 = (int) ($k * $k * 34187121 + $k * 121 >> 15 & 15);
			}
		}else{
			if(abs($f) >= 320.0 or abs($f1) >= 320.0){
				unset($this->decorations[$entityIdentifier]);
				return;
			}

			if($type === MapDecoration::TYPE_PLAYER and !$this->isUnlimitedTracking()){
				$type = MapDecoration::TYPE_PLAYER_OFF_MAP;
			}

			if($f <= -$j){
				$b0 = (int) (($j * 2) + 2.5);
			}

			if($f1 <= -$j){
				$b1 = (int) (($j * 2) + 2.5);
			}

			if($f >= $j){
				$b0 = (int) ($j * 2 + 1);
			}
			if($f1 >= $j){
				$b1 = (int) ($j * 2 + 1);
			}
		}

		$deco = new MapDecoration();
		$deco->icon = $type;
		$deco->rot = $b2;
		$deco->xOffset = $b0;
		$deco->yOffset = $b1;
		$deco->color = $color ?? new Color(255, 255, 255);
		$deco->label = $entityIdentifier;

		$this->decorations[$entityIdentifier] = $deco;
	}

	/**
	 * @param MapRenderer $renderer
	 */
	public function addRenderer(MapRenderer $renderer) : void{
		$this->renderers[spl_object_id($renderer)] = $renderer;
		$renderer->initialize($this);
	}

	/**
	 * @param MapRenderer $renderer
	 */
	public function removeRenderer(MapRenderer $renderer) : void{
		unset($this->renderers[spl_object_id($renderer)]);
	}

	/**
	 * @return MapRenderer[]
	 */
	public function getRenderers() : array{
		return $this->renderers;
	}

	/**
	 * @return bool
	 */
	public function isUnlimitedTracking() : bool{
		return $this->unlimitedTracking;
	}

	/**
	 * @param bool $unlimitedTracking
	 */
	public function setUnlimitedTracking(bool $unlimitedTracking) : void{
		$this->unlimitedTracking = $unlimitedTracking;
	}

	/**
	 * @return bool
	 */
	public function isVirtual() : bool{
		return $this->virtual;
	}

	/**
	 * @param bool $virtual
	 */
	public function setVirtual(bool $virtual) : void{
		$this->virtual = $virtual;
	}

	/**
	 * @return bool
	 */
	public function isDirty() : bool{
		return $this->dirty;
	}

	public function markAsDirty() : void{
		$this->setDirty(true);
	}

	/**
	 * @param bool $value
	 */
	public function setDirty(bool $value) : void{
		$this->dirty = $value;
	}

	public function isEmpty() : bool{
		return count($this->colors) === 0;
	}

	/**
	 * @return string
	 */
	public function getLevelName() : string{
		return $this->levelName;
	}

	/**
	 * @param string $levelName
	 */
	public function setLevelName(string $levelName) : void{
		$this->levelName = $levelName;
	}
}
