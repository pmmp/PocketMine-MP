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
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use function dechex;

abstract class Timings{
	public const INCLUDED_BY_OTHER_TIMINGS_PREFIX = "** ";

	private static bool $initialized = false;

	public static TimingsHandler $fullTick;
	public static TimingsHandler $serverTick;
	public static TimingsHandler $memoryManager;
	public static TimingsHandler $garbageCollector;
	public static TimingsHandler $titleTick;
	public static TimingsHandler $playerNetworkSend;
	public static TimingsHandler $playerNetworkSendCompress;
	public static TimingsHandler $playerNetworkSendEncrypt;
	public static TimingsHandler $playerNetworkReceive;
	public static TimingsHandler $playerNetworkReceiveDecompress;
	public static TimingsHandler $playerNetworkReceiveDecrypt;
	public static TimingsHandler $playerChunkOrder;
	public static TimingsHandler $playerChunkSend;
	public static TimingsHandler $connection;
	public static TimingsHandler $scheduler;
	public static TimingsHandler $serverCommand;
	public static TimingsHandler $worldLoad;
	public static TimingsHandler $worldSave;
	public static TimingsHandler $population;
	public static TimingsHandler $generationCallback;
	public static TimingsHandler $permissibleCalculation;
	public static TimingsHandler $permissibleCalculationDiff;
	public static TimingsHandler $permissibleCalculationCallback;
	public static TimingsHandler $entityMove;
	public static TimingsHandler $playerCheckNearEntities;
	public static TimingsHandler $tickEntity;
	public static TimingsHandler $tickTileEntity;
	public static TimingsHandler $entityBaseTick;
	public static TimingsHandler $livingEntityBaseTick;

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
	public static array $packetSendTimingMap = [];
	/** @var TimingsHandler[] */
	public static array $pluginTaskTimingMap = [];

	/**
	 * @var TimingsHandler[]
	 * @phpstan-var array<string, TimingsHandler>
	 */
	private static array $commandTimingMap = [];

	public static TimingsHandler $broadcastPackets;

	public static function init() : void{
		if(self::$initialized){
			return;
		}
		self::$initialized = true;

		self::$fullTick = new TimingsHandler("Full Server Tick");
		self::$serverTick = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Full Server Tick", self::$fullTick);
		self::$memoryManager = new TimingsHandler("Memory Manager");
		self::$garbageCollector = new TimingsHandler("Garbage Collector", self::$memoryManager);
		self::$titleTick = new TimingsHandler("Console Title Tick");

		self::$playerNetworkSend = new TimingsHandler("Player Network Send");
		self::$playerNetworkSendCompress = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Player Network Send - Compression", self::$playerNetworkSend);
		self::$playerNetworkSendEncrypt = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Player Network Send - Encryption", self::$playerNetworkSend);

		self::$playerNetworkReceive = new TimingsHandler("Player Network Receive");
		self::$playerNetworkReceiveDecompress = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Player Network Receive - Decompression", self::$playerNetworkReceive);
		self::$playerNetworkReceiveDecrypt = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Player Network Receive - Decryption", self::$playerNetworkReceive);

		self::$broadcastPackets = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Broadcast Packets", self::$playerNetworkSend);

		self::$playerChunkOrder = new TimingsHandler("Player Order Chunks");
		self::$playerChunkSend = new TimingsHandler("Player Send Chunks");
		self::$connection = new TimingsHandler("Connection Handler");
		self::$scheduler = new TimingsHandler("Scheduler");
		self::$serverCommand = new TimingsHandler("Server Command");
		self::$worldLoad = new TimingsHandler("World Load");
		self::$worldSave = new TimingsHandler("World Save");
		self::$population = new TimingsHandler("World Population");
		self::$generationCallback = new TimingsHandler("World Generation Callback");
		self::$permissibleCalculation = new TimingsHandler("Permissible Calculation");
		self::$permissibleCalculationDiff = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Permissible Calculation - Diff", self::$permissibleCalculation);
		self::$permissibleCalculationCallback = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Permissible Calculation - Callbacks", self::$permissibleCalculation);

		self::$syncPlayerDataLoad = new TimingsHandler("Player Data Load");
		self::$syncPlayerDataSave = new TimingsHandler("Player Data Save");

		self::$entityMove = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "entityMove");
		self::$playerCheckNearEntities = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "checkNearEntities");
		self::$tickEntity = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "tickEntity");
		self::$tickTileEntity = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "tickTileEntity");

		self::$entityBaseTick = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "entityBaseTick");
		self::$livingEntityBaseTick = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "livingEntityBaseTick");

		self::$schedulerSync = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Scheduler - Sync Tasks");
		self::$schedulerAsync = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Scheduler - Async Tasks");

		self::$playerCommand = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "playerCommand");
		self::$craftingDataCacheRebuild = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "craftingDataCacheRebuild");

	}

	public static function getScheduledTaskTimings(TaskHandler $task, int $period) : TimingsHandler{
		self::init();
		$name = "Task: " . $task->getOwnerName() . " Runnable: " . $task->getTaskName();

		if($period > 0){
			$name .= "(interval:" . $period . ")";
		}else{
			$name .= "(Single)";
		}

		if(!isset(self::$pluginTaskTimingMap[$name])){
			self::$pluginTaskTimingMap[$name] = new TimingsHandler($name, self::$schedulerSync);
		}

		return self::$pluginTaskTimingMap[$name];
	}

	public static function getEntityTimings(Entity $entity) : TimingsHandler{
		self::init();
		$entityType = (new \ReflectionClass($entity))->getShortName();
		if(!isset(self::$entityTypeTimingMap[$entityType])){
			if($entity instanceof Player){
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "tickEntity - EntityPlayer", self::$tickEntity);
			}else{
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "tickEntity - " . $entityType, self::$tickEntity);
			}
		}

		return self::$entityTypeTimingMap[$entityType];
	}

	public static function getTileEntityTimings(Tile $tile) : TimingsHandler{
		self::init();
		$tileType = (new \ReflectionClass($tile))->getShortName();
		if(!isset(self::$tileEntityTypeTimingMap[$tileType])){
			self::$tileEntityTypeTimingMap[$tileType] = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "tickTileEntity - " . $tileType, self::$tickTileEntity);
		}

		return self::$tileEntityTypeTimingMap[$tileType];
	}

	public static function getReceiveDataPacketTimings(ServerboundPacket $pk) : TimingsHandler{
		self::init();
		$pid = $pk->pid();
		if(!isset(self::$packetReceiveTimingMap[$pid])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetReceiveTimingMap[$pid] = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "receivePacket - " . $pkName . " [0x" . dechex($pid) . "]", self::$playerNetworkReceive);
		}

		return self::$packetReceiveTimingMap[$pid];
	}

	public static function getDecodeDataPacketTimings(ServerboundPacket $pk) : TimingsHandler{
		$pid = $pk->pid();
		return self::$packetDecodeTimingMap[$pid] ??= new TimingsHandler(
			self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Decode - " . $pk->getName() . " [0x" . dechex($pid) . "]",
			self::getReceiveDataPacketTimings($pk)
		);
	}

	public static function getHandleDataPacketTimings(ServerboundPacket $pk) : TimingsHandler{
		$pid = $pk->pid();
		return self::$packetHandleTimingMap[$pid] ??= new TimingsHandler(
			self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Handler - " . $pk->getName() . " [0x" . dechex($pid) . "]",
			self::getReceiveDataPacketTimings($pk)
		);
	}

	public static function getSendDataPacketTimings(ClientboundPacket $pk) : TimingsHandler{
		self::init();
		$pid = $pk->pid();
		if(!isset(self::$packetSendTimingMap[$pid])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetSendTimingMap[$pid] = new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "sendPacket - " . $pkName . " [0x" . dechex($pid) . "]", self::$playerNetworkSend);
		}

		return self::$packetSendTimingMap[$pid];
	}

	public static function getCommandDispatchTimings(string $commandName) : TimingsHandler{
		self::init();

		return self::$commandTimingMap[$commandName] ??= new TimingsHandler(self::INCLUDED_BY_OTHER_TIMINGS_PREFIX . "Command - " . $commandName);
	}
}
