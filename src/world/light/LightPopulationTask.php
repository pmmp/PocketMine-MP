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

namespace pocketmine\world\light;

use pocketmine\block\BlockFactory;
use pocketmine\scheduler\AsyncTask;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\World;

class LightPopulationTask extends AsyncTask{
	private const TLS_KEY_WORLD = "world";

	public $chunk;

	public function __construct(World $world, Chunk $chunk){
		$this->storeLocal(self::TLS_KEY_WORLD, $world);
		$this->chunk = FastChunkSerializer::serialize($chunk);
	}

	public function onRun() : void{
		if(!BlockFactory::isInit()){
			BlockFactory::init();
		}
		/** @var Chunk $chunk */
		$chunk = FastChunkSerializer::deserialize($this->chunk);

		$chunk->recalculateHeightMap();
		$chunk->populateSkyLight();
		$chunk->setLightPopulated();

		$this->chunk = FastChunkSerializer::serialize($chunk);
	}

	public function onCompletion() : void{
		/** @var World $world */
		$world = $this->fetchLocal(self::TLS_KEY_WORLD);
		if(!$world->isClosed()){
			/** @var Chunk $chunk */
			$chunk = FastChunkSerializer::deserialize($this->chunk);
			$world->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}
	}
}
