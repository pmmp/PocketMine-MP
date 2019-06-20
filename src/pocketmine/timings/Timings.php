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

	/** @var TimingsHandler */
	public static $fullTickTimer;
	/** @var TimingsHandler */
	public static $serverTickTimer;
	/** @var TimingsHandler */
	public static $memoryManagerTimer;
	/** @var TimingsHandler */
	public static $garbageCollectorTimer;
	/** @var TimingsHandler */
	public static $titleTickTimer;
	/** @var TimingsHandler */
	public static $playerNetworkSendTimer;
	/** @var TimingsHandler */
	public static $playerNetworkSendCompressTimer;
	/** @var TimingsHandler */
	public static $playerNetworkSendEncryptTimer;
	/** @var TimingsHandler */
	public static $playerNetworkReceiveTimer;
	/** @var TimingsHandler */
	public static $playerNetworkReceiveDecompressTimer;
	/** @var TimingsHandler */
	public static $playerNetworkReceiveDecryptTimer;
	/** @var TimingsHandler */
	public static $playerChunkOrderTimer;
	/** @var TimingsHandler */
	public static $playerChunkSendTimer;
	/** @var TimingsHandler */
	public static $connectionTimer;
	/** @var TimingsHandler */
	public static $schedulerTimer;
	/** @var TimingsHandler */
	public static $serverCommandTimer;
	/** @var TimingsHandler */
	public static $worldSaveTimer;
	/** @var TimingsHandler */
	public static $populationTimer;
	/** @var TimingsHandler */
	public static $generationCallbackTimer;
	/** @var TimingsHandler */
	public static $permissibleCalculationTimer;
	/** @var TimingsHandler */
	public static $permissionDefaultTimer;

	/** @var TimingsHandler */
	public static $entityMoveTimer;
	/** @var TimingsHandler */
	public static $playerCheckNearEntitiesTimer;
	/** @var TimingsHandler */
	public static $tickEntityTimer;
	/** @var TimingsHandler */
	public static $tickTileEntityTimer;

	/** @var TimingsHandler */
	public static $timerEntityBaseTick;
	/** @var TimingsHandler */
	public static $timerLivingEntityBaseTick;

	/** @var TimingsHandler */
	public static $schedulerSyncTimer;
	/** @var TimingsHandler */
	public static $schedulerAsyncTimer;

	/** @var TimingsHandler */
	public static $playerCommandTimer;

	/** @var TimingsHandler */
	public static $craftingDataCacheRebuildTimer;

	/** @var TimingsHandler[] */
	public static $entityTypeTimingMap = [];
	/** @var TimingsHandler[] */
	public static $tileEntityTypeTimingMap = [];
	/** @var TimingsHandler[] */
	public static $packetReceiveTimingMap = [];
	/** @var TimingsHandler[] */
	public static $packetSendTimingMap = [];
	/** @var TimingsHandler[] */
	public static $pluginTaskTimingMap = [];

	public static function init() : void{
		if(self::$serverTickTimer instanceof TimingsHandler){
			return;
		}

		self::$fullTickTimer = new TimingsHandler("Full Server Tick");
		self::$serverTickTimer = new TimingsHandler("** Full Server Tick", self::$fullTickTimer);
		self::$memoryManagerTimer = new TimingsHandler("Memory Manager");
		self::$garbageCollectorTimer = new TimingsHandler("Garbage Collector", self::$memoryManagerTimer);
		self::$titleTickTimer = new TimingsHandler("Console Title Tick");

		self::$playerNetworkSendTimer = new TimingsHandler("Player Network Send");
		self::$playerNetworkSendCompressTimer = new TimingsHandler("** Player Network Send - Compression", self::$playerNetworkSendTimer);
		self::$playerNetworkSendEncryptTimer = new TimingsHandler("** Player Network Send - Encryption", self::$playerNetworkSendTimer);

		self::$playerNetworkReceiveTimer = new TimingsHandler("Player Network Receive");
		self::$playerNetworkReceiveDecompressTimer = new TimingsHandler("** Player Network Receive - Decompression", self::$playerNetworkReceiveTimer);
		self::$playerNetworkReceiveDecryptTimer = new TimingsHandler("** Player Network Receive - Decryption", self::$playerNetworkReceiveTimer);

		self::$playerChunkOrderTimer = new TimingsHandler("Player Order Chunks");
		self::$playerChunkSendTimer = new TimingsHandler("Player Send Chunks");
		self::$connectionTimer = new TimingsHandler("Connection Handler");
		self::$schedulerTimer = new TimingsHandler("Scheduler");
		self::$serverCommandTimer = new TimingsHandler("Server Command");
		self::$worldSaveTimer = new TimingsHandler("World Save");
		self::$populationTimer = new TimingsHandler("World Population");
		self::$generationCallbackTimer = new TimingsHandler("World Generation Callback");
		self::$permissibleCalculationTimer = new TimingsHandler("Permissible Calculation");
		self::$permissionDefaultTimer = new TimingsHandler("Default Permission Calculation");

		self::$entityMoveTimer = new TimingsHandler("** entityMove");
		self::$playerCheckNearEntitiesTimer = new TimingsHandler("** checkNearEntities");
		self::$tickEntityTimer = new TimingsHandler("** tickEntity");
		self::$tickTileEntityTimer = new TimingsHandler("** tickTileEntity");

		self::$timerEntityBaseTick = new TimingsHandler("** entityBaseTick");
		self::$timerLivingEntityBaseTick = new TimingsHandler("** livingEntityBaseTick");

		self::$schedulerSyncTimer = new TimingsHandler("** Scheduler - Sync Tasks");
		self::$schedulerAsyncTimer = new TimingsHandler("** Scheduler - Async Tasks");

		self::$playerCommandTimer = new TimingsHandler("** playerCommand");
		self::$craftingDataCacheRebuildTimer = new TimingsHandler("** craftingDataCacheRebuild");

	}

	/**
	 * @param TaskHandler $task
	 * @param int         $period
	 *
	 * @return TimingsHandler
	 */
	public static function getScheduledTaskTimings(TaskHandler $task, int $period) : TimingsHandler{
		$name = "Task: " . ($task->getOwnerName() ?? "Unknown") . " Runnable: " . $task->getTaskName();

		if($period > 0){
			$name .= "(interval:" . $period . ")";
		}else{
			$name .= "(Single)";
		}

		if(!isset(self::$pluginTaskTimingMap[$name])){
			self::$pluginTaskTimingMap[$name] = new TimingsHandler($name, self::$schedulerSyncTimer);
		}

		return self::$pluginTaskTimingMap[$name];
	}

	/**
	 * @param Entity $entity
	 *
	 * @return TimingsHandler
	 */
	public static function getEntityTimings(Entity $entity) : TimingsHandler{
		$entityType = (new \ReflectionClass($entity))->getShortName();
		if(!isset(self::$entityTypeTimingMap[$entityType])){
			if($entity instanceof Player){
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler("** tickEntity - EntityPlayer", self::$tickEntityTimer);
			}else{
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler("** tickEntity - " . $entityType, self::$tickEntityTimer);
			}
		}

		return self::$entityTypeTimingMap[$entityType];
	}

	/**
	 * @param Tile $tile
	 *
	 * @return TimingsHandler
	 */
	public static function getTileEntityTimings(Tile $tile) : TimingsHandler{
		$tileType = (new \ReflectionClass($tile))->getShortName();
		if(!isset(self::$tileEntityTypeTimingMap[$tileType])){
			self::$tileEntityTypeTimingMap[$tileType] = new TimingsHandler("** tickTileEntity - " . $tileType, self::$tickTileEntityTimer);
		}

		return self::$tileEntityTypeTimingMap[$tileType];
	}

	/**
	 * @param ServerboundPacket $pk
	 *
	 * @return TimingsHandler
	 */
	public static function getReceiveDataPacketTimings(ServerboundPacket $pk) : TimingsHandler{
		$pid = $pk->pid();
		if(!isset(self::$packetReceiveTimingMap[$pid])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetReceiveTimingMap[$pid] = new TimingsHandler("** receivePacket - " . $pkName . " [0x" . dechex($pid) . "]", self::$playerNetworkReceiveTimer);
		}

		return self::$packetReceiveTimingMap[$pid];
	}


	/**
	 * @param ClientboundPacket $pk
	 *
	 * @return TimingsHandler
	 */
	public static function getSendDataPacketTimings(ClientboundPacket $pk) : TimingsHandler{
		$pid = $pk->pid();
		if(!isset(self::$packetSendTimingMap[$pid])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetSendTimingMap[$pid] = new TimingsHandler("** sendPacket - " . $pkName . " [0x" . dechex($pid) . "]", self::$playerNetworkSendTimer);
		}

		return self::$packetSendTimingMap[$pid];
	}
}
