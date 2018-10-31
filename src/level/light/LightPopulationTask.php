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

namespace pocketmine\level\light;

use pocketmine\block\BlockFactory;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;

class LightPopulationTask extends AsyncTask{

	public $chunk;

	public function __construct(Level $level, Chunk $chunk){
		$this->storeLocal($level);
		$this->chunk = $chunk->fastSerialize();
	}

	public function onRun() : void{
		if(!BlockFactory::isInit()){
			BlockFactory::init();
		}
		/** @var Chunk $chunk */
		$chunk = Chunk::fastDeserialize($this->chunk);

		$chunk->recalculateHeightMap();
		$chunk->populateSkyLight();
		$chunk->setLightPopulated();

		$this->chunk = $chunk->fastSerialize();
	}

	public function onCompletion() : void{
		/** @var Level $level */
		$level = $this->fetchLocal();
		if(!$level->isClosed()){
			/** @var Chunk $chunk */
			$chunk = Chunk::fastDeserialize($this->chunk);
			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}
	}
}
