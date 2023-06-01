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

namespace pocketmine\timings;

use pocketmine\block\tile\Tile;
use pocketmine\entity\Entity;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use function get_class;
use function str_starts_with;

abstract class Timings{
	public const GROUP_BREAKDOWN = "Minecraft - Breakdown";

	private static bool $initialized = false;

	public static TimingsHandler $fullTick;
	public static TimingsHandler $serverTick;
	public static TimingsHandler $serverInterrupts;
	public static TimingsHandler $memoryManager;
	public static TimingsHandler $garbageCollector;
	public static TimingsHandler $titleTick;
	public static TimingsHandler $playerNetworkSend;
	public static TimingsHandler $playerNetworkSendCompress;
	public static TimingsHandler $playerNetworkSendCompressBroadcast;
	public static TimingsHandler $playerNetworkSendCompressSessionBuffer;
	public static TimingsHandler $playerNetworkSendEncrypt;
	public static TimingsHandler $playerNetworkSendInventorySync;
	public static TimingsHandler $playerNetworkSendPreSpawnGameData;
	public static TimingsHandler $playerNetworkReceive;
	public static TimingsHandler $playerNetworkReceiveDecompress;
	public static TimingsHandler $playerNetworkReceiveDecrypt;
	public static TimingsHandler $playerChunkOrder;
	public static TimingsHandler $playerChunkSend;
	public static TimingsHandler $connection;
	public static TimingsHandler $scheduler;
	public static TimingsHandler $serverCommand;
	public static TimingsHandler $permissibleCalculation;
	public static TimingsHandler $permissibleCalculationDiff;
	public static TimingsHandler $permissibleCalculationCallback;
	public static TimingsHandler $entityMove;
	public static TimingsHandler $entityMoveCollision;
	public static TimingsHandler $projectileMove;
	public static TimingsHandler $projectileMoveRayTrace;
	public static TimingsHandler $playerCheckNearEntities;
	public static TimingsHandler $entityBaseTick;
	public static TimingsHandler $livingEntityBaseTick;
	public static TimingsHandler $itemEntityBaseTick;

	public static TimingsHandler $schedulerSync;
	public static TimingsHandler $schedulerAsync;

	public static TimingsHandler $playerCommand;
	public static TimingsHandler $craftingDataCacheRebuild;

	public static TimingsHandler $syncPlayerDataLoad;
	public static TimingsHandler $syncPlayerDataSave;

	/** @var TimingsHandler[] */
	public static array $entityTypeTimingMap = [];
	/** @var TimingsHandler[] */
	public static array $tileEntityTypeTimingMap = [];
	/** @var TimingsHandler[] */
	public static array $packetReceiveTimingMap = [];

	/** @var TimingsHandler[] */
	private static array $packetDecodeTimingMap = [];
	/** @var TimingsHandler[] */
	private static array $packetHandleTimingMap = [];

	/** @var TimingsHandler[] */
	private static array $packetEncodeTimingMap = [];

	/** @var TimingsHandler[] */
	public static array $packetSendTimingMap = [];
	/** @var TimingsHandler[] */
	public static array $pluginTaskTimingMap = [];

	/**
	 * @var TimingsHandler[]
	 * @phpstan-var array<string, TimingsHandler>
	 */
	private static array $commandTimingMap = [];

	public static TimingsHandler $broadcastPackets;

	public static TimingsHandler $playerMove;

	/** @var TimingsHandler[] */
	private static array $events = [];
	/** @var TimingsHandler[][] */
	private static array $eventHandlers = [];

	public static function init() : void{
		if(self::$initialized){
			return;
		}
		self::$initialized = true;

		self::$fullTick = new TimingsHandler("Full Server Tick");
		self::$serverTick = new TimingsHandler("Server Tick Update Cycle", self::$fullTick, group: self::GROUP_BREAKDOWN);
		self::$serverInterrupts = new TimingsHandler("Server Mid-Tick Processing", self::$fullTick, group: self::GROUP_BREAKDOWN);
		self::$memoryManager = new TimingsHandler("Memory Manager");
		self::$garbageCollector = new TimingsHandler("Garbage Collector", self::$memoryManager);
		self::$titleTick = new TimingsHandler("Console Title Tick");

		self::$connection = new TimingsHandler("Connection Handler");

		self::$playerNetworkSend = new TimingsHandler("Player Network Send", self::$connection);
		self::$playerNetworkSendCompress = new TimingsHandler("Player Network Send - Compression", self::$playerNetworkSend, group: self::GROUP_BREAKDOWN);
		self::$playerNetworkSendCompressBroadcast = new TimingsHandler("Player Network Send - Compression (Broadcast)", self::$playerNetworkSendCompress, group: self::GROUP_BREAKDOWN);
		self::$playerNetworkSendCompressSessionBuffer = new TimingsHandler("Player Network Send - Compression (Session Buffer)", self::$playerNetworkSendCompress, group: self::GROUP_BREAKDOWN);
		self::$playerNetworkSendEncrypt = new TimingsHandler("Player Network Send - Encryption", self::$playerNetworkSend, group: self::GROUP_BREAKDOWN);
		self::$playerNetworkSendInventorySync = new TimingsHandler("Player Network Send - Inventory Sync", self::$playerNetworkSend, group: self::GROUP_BREAKDOWN);
		self::$playerNetworkSendPreSpawnGameData = new TimingsHandler("Player Network Send - Pre-Spawn Game Data", self::$playerNetworkSend, group: self::GROUP_BREAKDOWN);

		self::$playerNetworkReceive = new TimingsHandler("Player Network Receive", self::$connection);
		self::$playerNetworkReceiveDecompress = new TimingsHandler("Player Network Receive - Decompression", self::$playerNetworkReceive, group: self::GROUP_BREAKDOWN);
		self::$playerNetworkReceiveDecrypt = new TimingsHandler("Player Network Receive - Decryption", self::$playerNetworkReceive, group: self::GROUP_BREAKDOWN);

		self::$broadcastPackets = new TimingsHandler("Broadcast Packets", self::$playerNetworkSend, group: self::GROUP_BREAKDOWN);

		self::$playerMove = new TimingsHandler("Player Movement");
		self::$playerChunkOrder = new TimingsHandler("Player Order Chunks");
		self::$playerChunkSend = new TimingsHandler("Player Network Send - Chunks", self::$playerNetworkSend, group: self::GROUP_BREAKDOWN);
		self::$scheduler = new TimingsHandler("Scheduler");
		self::$serverCommand = new TimingsHandler("Server Command");
		self::$permissibleCalculation = new TimingsHandler("Permissible Calculation");
		self::$permissibleCalculationDiff = new TimingsHandler("Permissible Calculation - Diff", self::$permissibleCalculation, group: self::GROUP_BREAKDOWN);
		self::$permissibleCalculationCallback = new TimingsHandler("Permissible Calculation - Callbacks", self::$permissibleCalculation, group: self::GROUP_BREAKDOWN);

		self::$syncPlayerDataLoad = new TimingsHandler("Player Data Load");
		self::$syncPlayerDataSave = new TimingsHandler("Player Data Save");

		self::$entityMove = new TimingsHandler("Entity Movement", group: self::GROUP_BREAKDOWN);
		self::$entityMoveCollision = new TimingsHandler("Entity Movement - Collision Checks", self::$entityMove, group: self::GROUP_BREAKDOWN);

		self::$projectileMove = new TimingsHandler("Projectile Movement", self::$entityMove, group: self::GROUP_BREAKDOWN);
		self::$projectileMoveRayTrace = new TimingsHandler("Projectile Movement - Ray Tracing", self::$projectileMove, group: self::GROUP_BREAKDOWN);

		self::$playerCheckNearEntities = new TimingsHandler("checkNearEntities", group: self::GROUP_BREAKDOWN);
		self::$entityBaseTick = new TimingsHandler("Entity Base Tick", group: self::GROUP_BREAKDOWN);
		self::$livingEntityBaseTick = new TimingsHandler("Entity Base Tick - Living", group: self::GROUP_BREAKDOWN);
		self::$itemEntityBaseTick = new TimingsHandler("Entity Base Tick - ItemEntity", group: self::GROUP_BREAKDOWN);

		self::$schedulerSync = new TimingsHandler("Scheduler - Sync Tasks", group: self::GROUP_BREAKDOWN);
		self::$schedulerAsync = new TimingsHandler("Scheduler - Async Tasks", group: self::GROUP_BREAKDOWN);

		self::$playerCommand = new TimingsHandler("Player Command", group: self::GROUP_BREAKDOWN);
		self::$craftingDataCacheRebuild = new TimingsHandler("Build CraftingDataPacket Cache", group: self::GROUP_BREAKDOWN);

	}

	public static function getScheduledTaskTimings(TaskHandler $task, int $period) : TimingsHandler{
		self::init();
		$name = "Task: " . $task->getTaskName();

		if($period > 0){
			$name .= "(interval:" . $period . ")";
		}else{
			$name .= "(Single)";
		}

		if(!isset(self::$pluginTaskTimingMap[$name])){
			self::$pluginTaskTimingMap[$name] = new TimingsHandler($name, self::$schedulerSync, $task->getOwnerName());
		}

		return self::$pluginTaskTimingMap[$name];
	}

	/**
	 * @phpstan-template T of object
	 * @phpstan-param class-string<T> $class
	 */
	private static function shortenCoreClassName(string $class, string $prefix) : string{
		if(str_starts_with($class, $prefix)){
			return (new \ReflectionClass($class))->getShortName();
		}
		return $class;
	}

	public static function getEntityTimings(Entity $entity) : TimingsHandler{
		self::init();
		if(!isset(self::$entityTypeTimingMap[$entity::class])){
			if($entity instanceof Player){
				//the timings viewer calculates average player count by looking at this timer, so we need to ensure it has
				//a name it can identify. However, we also want to make it obvious if this is a custom Player class.
				$displayName = $entity::class !== Player::class ? "Player (" . $entity::class . ")" : "Player";
			}else{
				$displayName = self::shortenCoreClassName($entity::class, "pocketmine\\entity\\");
			}
			self::$entityTypeTimingMap[$entity::class] = new TimingsHandler("Entity Tick - " . $displayName, group: self::GROUP_BREAKDOWN);
		}

		return self::$entityTypeTimingMap[$entity::class];
	}

	public static function getTileEntityTimings(Tile $tile) : TimingsHandler{
		self::init();
		if(!isset(self::$tileEntityTypeTimingMap[$tile::class])){
			self::$tileEntityTypeTimingMap[$tile::class] = new TimingsHandler(
				"Block Entity Tick - " . self::shortenCoreClassName($tile::class, "pocketmine\\block\\tile\\"),
				group: self::GROUP_BREAKDOWN
			);
		}

		return self::$tileEntityTypeTimingMap[$tile::class];
	}

	public static function getReceiveDataPacketTimings(ServerboundPacket $pk) : TimingsHandler{
		self::init();
		if(!isset(self::$packetReceiveTimingMap[$pk::class])){
			self::$packetReceiveTimingMap[$pk::class] = new TimingsHandler("Receive - " . $pk->getName(), self::$playerNetworkReceive, group: self::GROUP_BREAKDOWN);
		}

		return self::$packetReceiveTimingMap[$pk::class];
	}

	public static function getDecodeDataPacketTimings(ServerboundPacket $pk) : TimingsHandler{
		return self::$packetDecodeTimingMap[$pk::class] ??= new TimingsHandler(
			"Decode - " . $pk->getName(),
			self::getReceiveDataPacketTimings($pk),
			group: self::GROUP_BREAKDOWN
		);
	}

	public static function getHandleDataPacketTimings(ServerboundPacket $pk) : TimingsHandler{
		return self::$packetHandleTimingMap[$pk::class] ??= new TimingsHandler(
			"Handler - " . $pk->getName(),
			self::getReceiveDataPacketTimings($pk),
			group: self::GROUP_BREAKDOWN
		);
	}

	public static function getEncodeDataPacketTimings(ClientboundPacket $pk) : TimingsHandler{
		return self::$packetEncodeTimingMap[$pk::class] ??= new TimingsHandler(
			"Encode - " . $pk->getName(),
			self::getSendDataPacketTimings($pk),
			group: self::GROUP_BREAKDOWN
		);
	}

	public static function getSendDataPacketTimings(ClientboundPacket $pk) : TimingsHandler{
		self::init();
		if(!isset(self::$packetSendTimingMap[$pk::class])){
			self::$packetSendTimingMap[$pk::class] = new TimingsHandler("Send - " . $pk->getName(), self::$playerNetworkSend, group: self::GROUP_BREAKDOWN);
		}

		return self::$packetSendTimingMap[$pk::class];
	}

	public static function getCommandDispatchTimings(string $commandName) : TimingsHandler{
		self::init();

		return self::$commandTimingMap[$commandName] ??= new TimingsHandler("Command - " . $commandName, group: self::GROUP_BREAKDOWN);
	}

	public static function getEventTimings(Event $event) : TimingsHandler{
		$eventClass = get_class($event);
		if(!isset(self::$events[$eventClass])){
			self::$events[$eventClass] = new TimingsHandler(self::shortenCoreClassName($eventClass, "pocketmine\\event\\"), group: "Events");
		}

		return self::$events[$eventClass];
	}

	/**
	 * @phpstan-template TEvent of Event
	 * @phpstan-param class-string<TEvent> $event
	 */
	public static function getEventHandlerTimings(string $event, string $handlerName, string $group) : TimingsHandler{
		if(!isset(self::$eventHandlers[$event][$handlerName])){
			self::$eventHandlers[$event][$handlerName] = new TimingsHandler($handlerName . "(" . self::shortenCoreClassName($event, "pocketmine\\event\\") . ")", group: $group);
		}

		return self::$eventHandlers[$event][$handlerName];
	}
}
