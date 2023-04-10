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
	public TimingsHandler $syncChunkLoadFixInvalidBlocks;
	public TimingsHandler $syncChunkLoadEntities;
	public TimingsHandler $syncChunkLoadTileEntities;
	public TimingsHandler $syncChunkSave;

	public function __construct(World $world){
		$name = $world->getFolderName() . " - ";

		$this->setBlock = new TimingsHandler($name . "setBlock", group: Timings::GROUP_BREAKDOWN);
		$this->doBlockLightUpdates = new TimingsHandler($name . "Block Light Updates", group: Timings::GROUP_BREAKDOWN);
		$this->doBlockSkyLightUpdates = new TimingsHandler($name . "Sky Light Updates", group: Timings::GROUP_BREAKDOWN);

		$this->doChunkUnload = new TimingsHandler($name . "Unload Chunks", group: Timings::GROUP_BREAKDOWN);
		$this->scheduledBlockUpdates = new TimingsHandler($name . "Scheduled Block Updates", group: Timings::GROUP_BREAKDOWN);
		$this->randomChunkUpdates = new TimingsHandler($name . "Random Chunk Updates", group: Timings::GROUP_BREAKDOWN);
		$this->randomChunkUpdatesChunkSelection = new TimingsHandler($name . "Random Chunk Updates - Chunk Selection", group: Timings::GROUP_BREAKDOWN);
		$this->doChunkGC = new TimingsHandler($name . "Garbage Collection", group: Timings::GROUP_BREAKDOWN);
		$this->entityTick = new TimingsHandler($name . "Tick Entities", group: Timings::GROUP_BREAKDOWN);

		Timings::init(); //make sure the timers we want are available
		$this->syncChunkSend = new TimingsHandler($name . "Player Send Chunks", Timings::$playerChunkSend, group: Timings::GROUP_BREAKDOWN);
		$this->syncChunkSendPrepare = new TimingsHandler($name . "Player Send Chunk Prepare", Timings::$playerChunkSend, group: Timings::GROUP_BREAKDOWN);

		$this->syncChunkLoad = new TimingsHandler($name . "Chunk Load", Timings::$worldLoad, group: Timings::GROUP_BREAKDOWN);
		$this->syncChunkLoadData = new TimingsHandler($name . "Chunk Load - Data", group: Timings::GROUP_BREAKDOWN);
		$this->syncChunkLoadFixInvalidBlocks = new TimingsHandler($name . "Chunk Load - Fix Invalid Blocks", group: Timings::GROUP_BREAKDOWN);
		$this->syncChunkLoadEntities = new TimingsHandler($name . "Chunk Load - Entities", group: Timings::GROUP_BREAKDOWN);
		$this->syncChunkLoadTileEntities = new TimingsHandler($name . "Chunk Load - TileEntities", group: Timings::GROUP_BREAKDOWN);
		$this->syncChunkSave = new TimingsHandler($name . "Chunk Save", Timings::$worldSave, group: Timings::GROUP_BREAKDOWN);

		$this->doTick = new TimingsHandler($name . "World Tick");
	}
}
