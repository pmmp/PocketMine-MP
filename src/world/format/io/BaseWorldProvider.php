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

namespace pocketmine\world\format\io;

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\WorldException;
use function file_exists;

abstract class BaseWorldProvider implements WorldProvider{
	protected WorldData $worldData;

	public function __construct(
		protected string $path
	){
		if(!file_exists($path)){
			throw new WorldException("World does not exist");
		}

		$this->worldData = $this->loadLevelData();
	}

	/**
	 * @throws CorruptedWorldException
	 * @throws UnsupportedWorldFormatException
	 */
	abstract protected function loadLevelData() : WorldData;

	private function translatePalette(PalettedBlockArray $blockArray) : PalettedBlockArray{
		$palette = $blockArray->getPalette();

		//TODO: this should be dependency-injected so it can be replaced, but that would break BC
		//also, we want it to be lazy-loaded ...
		$blockDataUpgrader = GlobalBlockStateHandlers::getUpgrader();
		$blockStateDeserializer = GlobalBlockStateHandlers::getDeserializer();
		$newPalette = [];
		foreach($palette as $k => $legacyIdMeta){
			$newStateData = $blockDataUpgrader->upgradeIntIdMeta($legacyIdMeta >> 4, $legacyIdMeta & 0xf);
			if($newStateData === null){
				//TODO: remember data for unknown states so we can implement them later
				$newStateData = new BlockStateData(BlockTypeNames::INFO_UPDATE, CompoundTag::create(), BlockStateData::CURRENT_VERSION);
			}

			$newPalette[$k] = $blockStateDeserializer->deserialize($newStateData);
		}

		//TODO: this is sub-optimal since it reallocates the offset table multiple times
		return PalettedBlockArray::fromData(
			$blockArray->getBitsPerBlock(),
			$blockArray->getWordArray(),
			$newPalette
		);
	}

	protected function palettizeLegacySubChunkXZY(string $idArray, string $metaArray) : PalettedBlockArray{
		return $this->translatePalette(SubChunkConverter::convertSubChunkXZY($idArray, $metaArray));
	}

	protected function palettizeLegacySubChunkYZX(string $idArray, string $metaArray) : PalettedBlockArray{
		return $this->translatePalette(SubChunkConverter::convertSubChunkYZX($idArray, $metaArray));
	}

	protected function palettizeLegacySubChunkFromColumn(string $idArray, string $metaArray, int $yOffset) : PalettedBlockArray{
		return $this->translatePalette(SubChunkConverter::convertSubChunkFromLegacyColumn($idArray, $metaArray, $yOffset));
	}

	public function getPath() : string{
		return $this->path;
	}

	public function getWorldData() : WorldData{
		return $this->worldData;
	}
}
