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

namespace pocketmine\world;

use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;

class WorldTimings{

	public TimingsHandler $setBlock;
	public TimingsHandler $doBlockLightUpdates;
	public TimingsHandler $doBlockSkyLightUpdates;

	public TimingsHandler $doChunkUnload;
	public TimingsHandler $scheduledBlockUpdates;
	public TimingsHandler $randomChunkUpdates;
	public TimingsHandler $randomChunkUpdatesChunkSelection;
	public TimingsHandler $doChunkGC;
	public TimingsHandler $entityTick;
	public TimingsHandler $doTick;

	public TimingsHandler $syncChunkSend;
	public TimingsHandler $syncChunkSendPrepare;

	public TimingsHandler $syncChunkLoad;
	public TimingsHandler $syncChunkLoadData;
	public TimingsHandler $syncChunkLoadEntities;
	public TimingsHandler $syncChunkLoadTileEntities;
	public TimingsHandler $syncChunkSave;

	public function __construct(World $world){
		$name = $world->getFolderName() . " - ";

		$this->setBlock = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "setBlock");
		$this->doBlockLightUpdates = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "Block Light Updates");
		$this->doBlockSkyLightUpdates = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "Sky Light Updates");

		$this->doChunkUnload = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "Unload Chunks");
		$this->scheduledBlockUpdates = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "Scheduled Block Updates");
		$this->randomChunkUpdates = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "Random Chunk Updates");
		$this->randomChunkUpdatesChunkSelection = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "Random Chunk Updates - Chunk Selection");
		$this->doChunkGC = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "Garbage Collection");
		$this->entityTick = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "Tick Entities");

		Timings::init(); //make sure the timers we want are available
		$this->syncChunkSend = new TimingsHandler("** " . $name . "Player Send Chunks", Timings::$playerChunkSend);
		$this->syncChunkSendPrepare = new TimingsHandler("** " . $name . "Player Send Chunk Prepare", Timings::$playerChunkSend);

		$this->syncChunkLoad = new TimingsHandler("** " . $name . "Chunk Load", Timings::$worldLoad);
		$this->syncChunkLoadData = new TimingsHandler("** " . $name . "Chunk Load - Data");
		$this->syncChunkLoadEntities = new TimingsHandler("** " . $name . "Chunk Load - Entities");
		$this->syncChunkLoadTileEntities = new TimingsHandler("** " . $name . "Chunk Load - TileEntities");
		$this->syncChunkSave = new TimingsHandler("** " . $name . "Chunk Save", Timings::$worldSave);

		$this->doTick = new TimingsHandler($name . "World Tick");
	}
}
