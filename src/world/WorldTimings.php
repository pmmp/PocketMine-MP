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

	/** @var TimingsHandler */
	public $setBlock;
	/** @var TimingsHandler */
	public $doBlockLightUpdates;
	/** @var TimingsHandler */
	public $doBlockSkyLightUpdates;

	/** @var TimingsHandler */
	public $doChunkUnload;
	/** @var TimingsHandler */
	public $doTickPending;
	/** @var TimingsHandler */
	public $doTickTiles;
	/** @var TimingsHandler */
	public $doChunkGC;
	/** @var TimingsHandler */
	public $entityTick;
	/** @var TimingsHandler */
	public $doTick;

	/** @var TimingsHandler */
	public $syncChunkSendTimer;
	/** @var TimingsHandler */
	public $syncChunkSendPrepareTimer;

	/** @var TimingsHandler */
	public $syncChunkLoadTimer;
	/** @var TimingsHandler */
	public $syncChunkLoadDataTimer;
	/** @var TimingsHandler */
	public $syncChunkLoadEntitiesTimer;
	/** @var TimingsHandler */
	public $syncChunkLoadTileEntitiesTimer;
	/** @var TimingsHandler */
	public $syncChunkSaveTimer;

	public function __construct(World $world){
		$name = $world->getFolderName() . " - ";

		$this->setBlock = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "setBlock");
		$this->doBlockLightUpdates = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "doBlockLightUpdates");
		$this->doBlockSkyLightUpdates = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "doBlockSkyLightUpdates");

		$this->doChunkUnload = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "doChunkUnload");
		$this->doTickPending = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "doTickPending");
		$this->doTickTiles = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "doTickTiles");
		$this->doChunkGC = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "doChunkGC");
		$this->entityTick = new TimingsHandler(Timings::INCLUDED_BY_OTHER_TIMINGS_PREFIX . $name . "entityTick");

		Timings::init(); //make sure the timers we want are available
		$this->syncChunkSendTimer = new TimingsHandler("** " . $name . "syncChunkSend", Timings::$playerChunkSendTimer);
		$this->syncChunkSendPrepareTimer = new TimingsHandler("** " . $name . "syncChunkSendPrepare", Timings::$playerChunkSendTimer);

		$this->syncChunkLoadTimer = new TimingsHandler("** " . $name . "syncChunkLoad", Timings::$worldLoadTimer);
		$this->syncChunkLoadDataTimer = new TimingsHandler("** " . $name . "syncChunkLoad - Data");
		$this->syncChunkLoadEntitiesTimer = new TimingsHandler("** " . $name . "syncChunkLoad - Entities");
		$this->syncChunkLoadTileEntitiesTimer = new TimingsHandler("** " . $name . "syncChunkLoad - TileEntities");
		$this->syncChunkSaveTimer = new TimingsHandler("** " . $name . "syncChunkSave", Timings::$worldSaveTimer);

		$this->doTick = new TimingsHandler($name . "doTick");
	}
}
