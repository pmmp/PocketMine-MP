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

use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockStateDeserializer;
use pocketmine\data\bedrock\block\BlockStateSerializer;
use pocketmine\data\bedrock\block\upgrade\BlockDataUpgrader;
use pocketmine\world\format\io\exception\CorruptedWorldException;
use pocketmine\world\format\io\exception\UnsupportedWorldFormatException;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\WorldException;
use function count;
use function file_exists;
use function implode;

abstract class BaseWorldProvider implements WorldProvider{
	protected WorldData $worldData;

	protected BlockStateDeserializer $blockStateDeserializer;
	protected BlockDataUpgrader $blockDataUpgrader;
	protected BlockStateSerializer $blockStateSerializer;

	public function __construct(
		protected string $path,
		protected \Logger $logger
	){
		if(!file_exists($path)){
			throw new WorldException("World does not exist");
		}

		//TODO: this should not rely on singletons
		$this->blockStateDeserializer = GlobalBlockStateHandlers::getDeserializer();
		$this->blockDataUpgrader = GlobalBlockStateHandlers::getUpgrader();
		$this->blockStateSerializer = GlobalBlockStateHandlers::getSerializer();

		$this->worldData = $this->loadLevelData();
	}

	/**
	 * @throws CorruptedWorldException
	 * @throws UnsupportedWorldFormatException
	 */
	abstract protected function loadLevelData() : WorldData;

	private function translatePalette(PalettedBlockArray $blockArray, \Logger $logger) : PalettedBlockArray{
		//TODO: missing type info in stubs
		/** @phpstan-var list<int> $palette */
		$palette = $blockArray->getPalette();

		$newPalette = [];
		$blockDecodeErrors = [];
		foreach($palette as $k => $legacyIdMeta){
			//TODO: remember data for unknown states so we can implement them later
			$id = $legacyIdMeta >> 4;
			$meta = $legacyIdMeta & 0xf;
			try{
				$newStateData = $this->blockDataUpgrader->upgradeIntIdMeta($id, $meta);
			}catch(BlockStateDeserializeException $e){
				$blockDecodeErrors[] = "Palette offset $k / Failed to upgrade legacy ID/meta $id:$meta: " . $e->getMessage();
				$newStateData = GlobalBlockStateHandlers::getUnknownBlockStateData();
			}

			try{
				$newPalette[$k] = $this->blockStateDeserializer->deserialize($newStateData);
			}catch(BlockStateDeserializeException $e){
				//this should never happen anyway - if the upgrader returned an invalid state, we have bigger problems
				$blockDecodeErrors[] = "Palette offset $k / Failed to deserialize upgraded state $id:$meta: " . $e->getMessage();
				$newPalette[$k] = $this->blockStateDeserializer->deserialize(GlobalBlockStateHandlers::getUnknownBlockStateData());
			}
		}

		if(count($blockDecodeErrors) > 0){
			$logger->error("Errors decoding/upgrading blocks:\n - " . implode("\n - ", $blockDecodeErrors));
		}

		//TODO: this is sub-optimal since it reallocates the offset table multiple times
		return PalettedBlockArray::fromData(
			$blockArray->getBitsPerBlock(),
			$blockArray->getWordArray(),
			$newPalette
		);
	}

	protected function palettizeLegacySubChunkXZY(string $idArray, string $metaArray, \Logger $logger) : PalettedBlockArray{
		return $this->translatePalette(SubChunkConverter::convertSubChunkXZY($idArray, $metaArray), $logger);
	}

	protected function palettizeLegacySubChunkYZX(string $idArray, string $metaArray, \Logger $logger) : PalettedBlockArray{
		return $this->translatePalette(SubChunkConverter::convertSubChunkYZX($idArray, $metaArray), $logger);
	}

	protected function palettizeLegacySubChunkFromColumn(string $idArray, string $metaArray, int $yOffset, \Logger $logger) : PalettedBlockArray{
		return $this->translatePalette(SubChunkConverter::convertSubChunkFromLegacyColumn($idArray, $metaArray, $yOffset), $logger);
	}

	public function getPath() : string{
		return $this->path;
	}

	public function getWorldData() : WorldData{
		return $this->worldData;
	}
}
