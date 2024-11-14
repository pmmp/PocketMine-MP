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

use pocketmine\timings\TimingsHandler;

class WorldTimings{

	public TimingsHandler $setBlock;
	public TimingsHandler $doBlockLightUpdates;
	public TimingsHandler $doBlockSkyLightUpdates;

	public TimingsHandler $doChunkUnload;
	public TimingsHandler $scheduledBlockUpdates;
	public TimingsHandler $neighbourBlockUpdates;
	public TimingsHandler $randomChunkUpdates;
	public TimingsHandler $randomChunkUpdatesChunkSelection;
	public TimingsHandler $doChunkGC;
	public TimingsHandler $entityTick;
	public TimingsHandler $tileTick;
	public TimingsHandler $doTick;

	public TimingsHandler $syncChunkSend;
	public TimingsHandler $syncChunkSendPrepare;

	public TimingsHandler $syncChunkLoad;
	public TimingsHandler $syncChunkLoadData;
	public TimingsHandler $syncChunkLoadFixInvalidBlocks;
	public TimingsHandler $syncChunkLoadEntities;
	public TimingsHandler $syncChunkLoadTileEntities;

	public TimingsHandler $syncDataSave;
	public TimingsHandler $syncChunkSave;

	public TimingsHandler $chunkPopulationOrder;
	public TimingsHandler $chunkPopulationCompletion;

	/**
	 * @var TimingsHandler[]
	 * @phpstan-var array<string, TimingsHandler>
	 */
	private static array $aggregators = [];

	private static function newTimer(string $worldName, string $timerName) : TimingsHandler{
		$aggregator = self::$aggregators[$timerName] ??= new TimingsHandler("Worlds - $timerName"); //displayed in Minecraft primary table

		return new TimingsHandler("$worldName - $timerName", $aggregator);
	}

	public function __construct(World $world){
		$name = $world->getFolderName();

		$this->setBlock = self::newTimer($name, "Set Blocks");
		$this->doBlockLightUpdates = self::newTimer($name, "Block Light Updates");
		$this->doBlockSkyLightUpdates = self::newTimer($name, "Sky Light Updates");

		$this->doChunkUnload = self::newTimer($name, "Unload Chunks");
		$this->scheduledBlockUpdates = self::newTimer($name, "Scheduled Block Updates");
		$this->neighbourBlockUpdates = self::newTimer($name, "Neighbour Block Updates");
		$this->randomChunkUpdates = self::newTimer($name, "Random Chunk Updates");
		$this->randomChunkUpdatesChunkSelection = self::newTimer($name, "Random Chunk Updates - Chunk Selection");
		$this->doChunkGC = self::newTimer($name, "Garbage Collection");
		$this->entityTick = self::newTimer($name, "Entity Tick");
		$this->tileTick = self::newTimer($name, "Block Entity Tick");
		$this->doTick = self::newTimer($name, "World Tick");

		$this->syncChunkSend = self::newTimer($name, "Player Send Chunks");
		$this->syncChunkSendPrepare = self::newTimer($name, "Player Send Chunk Prepare");

		$this->syncChunkLoad = self::newTimer($name, "Chunk Load");
		$this->syncChunkLoadData = self::newTimer($name, "Chunk Load - Data");
		$this->syncChunkLoadFixInvalidBlocks = self::newTimer($name, "Chunk Load - Fix Invalid Blocks");
		$this->syncChunkLoadEntities = self::newTimer($name, "Chunk Load - Entities");
		$this->syncChunkLoadTileEntities = self::newTimer($name, "Chunk Load - Block Entities");

		$this->syncDataSave = self::newTimer($name, "Data Save");
		$this->syncChunkSave = self::newTimer($name, "Chunk Save");

		$this->chunkPopulationOrder = self::newTimer($name, "Chunk Population - Order");
		$this->chunkPopulationCompletion = self::newTimer($name, "Chunk Population - Completion");
	}
}
