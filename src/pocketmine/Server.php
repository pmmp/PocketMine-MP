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

/**
 * PocketMine-MP is the Minecraft: PE multiplayer server software
 * Homepage: http://www.pocketmine.net/
 */
namespace pocketmine;

use pocketmine\block\BlockFactory;
use pocketmine\command\CommandReader;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\SimpleCommandMap;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\event\HandlerList;
use pocketmine\event\level\LevelInitEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\inventory\CraftingManager;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\lang\BaseLang;
use pocketmine\lang\TextContainer;
use pocketmine\level\biome\Biome;
use pocketmine\level\format\io\LevelProvider;
use pocketmine\level\format\io\LevelProviderManager;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\level\LevelException;
use pocketmine\metadata\EntityMetadataStore;
use pocketmine\metadata\LevelMetadataStore;
use pocketmine\metadata\PlayerMetadataStore;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\AdvancedSourceInterface;
use pocketmine\network\CompressBatchedTask;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\RakLibInterface;
use pocketmine\network\Network;
use pocketmine\network\query\QueryHandler;
use pocketmine\network\rcon\RCON;
use pocketmine\network\upnp\UPnP;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\plugin\PluginManager;
use pocketmine\plugin\ScriptPluginLoader;
use pocketmine\resourcepacks\ResourcePackManager;
use pocketmine\scheduler\FileWriteTask;
use pocketmine\scheduler\SendUsageTask;
use pocketmine\scheduler\ServerScheduler;
use pocketmine\tile\Tile;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;
use pocketmine\updater\AutoUpdater;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;
use pocketmine\utils\VersionString;

/**
 * The class that manages everything
 */
class Server{
	public const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	public const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	/** @var Server */
	private static $instance = null;

	/** @var \Threaded */
	private static $sleeper = null;

	/** @var BanList */
	private $banByName = null;

	/** @var BanList */
	private $banByIP = null;

	/** @var Config */
	private $operators = null;

	/** @var Config */
	private $whitelist = null;

	/** @var bool */
	private $isRunning = true;

	private $hasStopped = false;

	/** @var PluginManager */
	private $pluginManager = null;

	private $profilingTickRate = 20;

	/** @var AutoUpdater */
	private $updater = null;

	/** @var ServerScheduler */
	private $scheduler = null;

	/**
	 * Counts the ticks since the server start
	 *
	 * @var int
	 */
	private $tickCounter = 0;
	private $nextTick = 0;
	private $tickAverage = [20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20];
	private $useAverage = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	private $currentTPS = 20;
	private $currentUse = 0;

	/** @var bool */
	private $doTitleTick = true;

	private $sendUsageTicker = 0;

	private $dispatchSignals = false;

	/** @var \AttachableThreadedLogger */
	private $logger;

	/** @var MemoryManager */
	private $memoryManager;

	/** @var CommandReader */
	private $console = null;

	/** @var SimpleCommandMap */
	private $commandMap = null;

	/** @var CraftingManager */
	private $craftingManager;

	/** @var ResourcePackManager */
	private $resourceManager;

	/** @var ConsoleCommandSender */
	private $consoleSender;

	/** @var int */
	private $maxPlayers;

	/** @var bool */
	private $onlineMode = true;

	/** @var bool */
	private $autoSave;

	/** @var RCON */
	private $rcon;

	/** @var EntityMetadataStore */
	private $entityMetadata;

	/** @var PlayerMetadataStore */
	private $playerMetadata;

	/** @var LevelMetadataStore */
	private $levelMetadata;

	/** @var Network */
	private $network;

	private $networkCompressionAsync = true;
	public $networkCompressionLevel = 7;

	private $autoTickRate = true;
	private $autoTickRateLimit = 20;
	private $alwaysTickPlayers = false;
	private $baseTickRate = 1;

	private $autoSaveTicker = 0;
	private $autoSaveTicks = 6000;

	/** @var BaseLang */
	private $baseLang;

	private $forceLanguage = false;

	private $serverID;

	private $autoloader;
	private $dataPath;
	private $pluginPath;

	private $uniquePlayers = [];

	/** @var QueryHandler */
	private $queryHandler;

	/** @var QueryRegenerateEvent */
	private $queryRegenerateTask = null;

	/** @var Config */
	private $properties;

	private $propertyCache = [];

	/** @var Config */
	private $config;

	/** @var Player[] */
	private $players = [];

	/** @var Player[] */
	private $loggedInPlayers = [];

	/** @var Player[] */
	private $playerList = [];

	/** @var Level[] */
	private $levels = [];

	/** @var Level */
	private $levelDefault = null;

	/**
	 * @return string
	 */
	public function getName() : string{
		return \pocketmine\NAME;
	}

	/**
	 * @return bool
	 */
	public function isRunning() : bool{
		return $this->isRunning;
	}

	/**
	 * @return string
	 */
	public function getPocketMineVersion() : string{
		return \pocketmine\VERSION;
	}

	/**
	 * @return string
	 */
	public function getCodename() : string{
		return \pocketmine\CODENAME;
	}

	/**
	 * @return string
	 */
	public function getVersion() : string{
		return ProtocolInfo::MINECRAFT_VERSION;
	}

	/**
	 * @return string
	 */
	public function getApiVersion() : string{
		return \pocketmine\API_VERSION;
	}

	/**
	 * @return string
	 */
	public function getFilePath() : string{
		return \pocketmine\PATH;
	}

	/**
	 * @return string
	 */
	public function getResourcePath() : string{
		return \pocketmine\RESOURCE_PATH;
	}

	/**
	 * @return string
	 */
	public function getDataPath() : string{
		return $this->dataPath;
	}

	/**
	 * @return string
	 */
	public function getPluginPath() : string{
		return $this->pluginPath;
	}

	/**
	 * @return int
	 */
	public function getMaxPlayers() : int{
		return $this->maxPlayers;
	}

	/**
	 * Returns whether the server requires that players be authenticated to Xbox Live. If true, connecting players who
	 * are not logged into Xbox Live will be disconnected.
	 *
	 * @return bool
	 */
	public function getOnlineMode() : bool{
		return $this->onlineMode;
	}

	/**
	 * Alias of {@link #getOnlineMode()}.
	 * @return bool
	 */
	public function requiresAuthentication() : bool{
		return $this->getOnlineMode();
	}

	/**
	 * @return int
	 */
	public function getPort() : int{
		return $this->getConfigInt("server-port", 19132);
	}

	/**
	 * @return int
	 */
	public function getViewDistance() : int{
		return max(2, $this->getConfigInt("view-distance", 8));
	}

	/**
	 * Returns a view distance up to the currently-allowed limit.
	 *
	 * @param int $distance
	 *
	 * @return int
	 */
	public function getAllowedViewDistance(int $distance) : int{
		return max(2, min($distance, $this->memoryManager->getViewDistance($this->getViewDistance())));
	}

	/**
	 * @return string
	 */
	public function getIp() : string{
		return $this->getConfigString("server-ip", "0.0.0.0");
	}

	/**
	 * @return UUID
	 */
	public function getServerUniqueId(){
		return $this->serverID;
	}

	/**
	 * @return bool
	 */
	public function getAutoSave() : bool{
		return $this->autoSave;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoSave(bool $value){
		$this->autoSave = $value;
		foreach($this->getLevels() as $level){
			$level->setAutoSave($this->autoSave);
		}
	}

	/**
	 * @return string
	 */
	public function getLevelType() : string{
		return $this->getConfigString("level-type", "DEFAULT");
	}

	/**
	 * @return bool
	 */
	public function getGenerateStructures() : bool{
		return $this->getConfigBool("generate-structures", true);
	}

	/**
	 * @return int
	 */
	public function getGamemode() : int{
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * @return bool
	 */
	public function getForceGamemode() : bool{
		return $this->getConfigBool("force-gamemode", false);
	}

	/**
	 * Returns the gamemode text name
	 *
	 * @param int $mode
	 *
	 * @return string
	 */
	public static function getGamemodeString(int $mode) : string{
		switch($mode){
			case Player::SURVIVAL:
				return "%gameMode.survival";
			case Player::CREATIVE:
				return "%gameMode.creative";
			case Player::ADVENTURE:
				return "%gameMode.adventure";
			case Player::SPECTATOR:
				return "%gameMode.spectator";
		}

		return "UNKNOWN";
	}

	public static function getGamemodeName(int $mode) : string{
		switch($mode){
			case Player::SURVIVAL:
				return "Survival";
			case Player::CREATIVE:
				return "Creative";
			case Player::ADVENTURE:
				return "Adventure";
			case Player::SPECTATOR:
				return "Spectator";
			default:
				throw new \InvalidArgumentException("Invalid gamemode $mode");
		}
	}

	/**
	 * Parses a string and returns a gamemode integer, -1 if not found
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function getGamemodeFromString(string $str) : int{
		switch(strtolower(trim($str))){
			case (string) Player::SURVIVAL:
			case "survival":
			case "s":
				return Player::SURVIVAL;

			case (string) Player::CREATIVE:
			case "creative":
			case "c":
				return Player::CREATIVE;

			case (string) Player::ADVENTURE:
			case "adventure":
			case "a":
				return Player::ADVENTURE;

			case (string) Player::SPECTATOR:
			case "spectator":
			case "view":
			case "v":
				return Player::SPECTATOR;
		}
		return -1;
	}

	/**
	 * Returns Server global difficulty. Note that this may be overridden in individual Levels.
	 * @return int
	 */
	public function getDifficulty() : int{
		return $this->getConfigInt("difficulty", 1);
	}

	/**
	 * @return bool
	 */
	public function hasWhitelist() : bool{
		return $this->getConfigBool("white-list", false);
	}

	/**
	 * @return int
	 */
	public function getSpawnRadius() : int{
		return $this->getConfigInt("spawn-protection", 16);
	}

	/**
	 * @return bool
	 */
	public function getAllowFlight() : bool{
		return $this->getConfigBool("allow-flight", false);
	}

	/**
	 * @return bool
	 */
	public function isHardcore() : bool{
		return $this->getConfigBool("hardcore", false);
	}

	/**
	 * @return int
	 */
	public function getDefaultGamemode() : int{
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * @return string
	 */
	public function getMotd() : string{
		return $this->getConfigString("motd", \pocketmine\NAME . " Server");
	}

	/**
	 * @return \ClassLoader
	 */
	public function getLoader(){
		return $this->autoloader;
	}

	/**
	 * @return MainLogger
	 */
	public function getLogger(){
		return $this->logger;
	}

	/**
	 * @return EntityMetadataStore
	 */
	public function getEntityMetadata(){
		return $this->entityMetadata;
	}

	/**
	 * @return PlayerMetadataStore
	 */
	public function getPlayerMetadata(){
		return $this->playerMetadata;
	}

	/**
	 * @return LevelMetadataStore
	 */
	public function getLevelMetadata(){
		return $this->levelMetadata;
	}

	/**
	 * @return AutoUpdater
	 */
	public function getUpdater(){
		return $this->updater;
	}

	/**
	 * @return PluginManager
	 */
	public function getPluginManager(){
		return $this->pluginManager;
	}

	/**
	 * @return CraftingManager
	 */
	public function getCraftingManager(){
		return $this->craftingManager;
	}

	/**
	 * @return ResourcePackManager
	 */
	public function getResourcePackManager() : ResourcePackManager{
		return $this->resourceManager;
	}

	/**
	 * @return ServerScheduler
	 */
	public function getScheduler(){
		return $this->scheduler;
	}

	/**
	 * @return int
	 */
	public function getTick() : int{
		return $this->tickCounter;
	}

	/**
	 * Returns the last server TPS measure
	 *
	 * @return float
	 */
	public function getTicksPerSecond() : float{
		return round($this->currentTPS, 2);
	}

	/**
	 * Returns the last server TPS average measure
	 *
	 * @return float
	 */
	public function getTicksPerSecondAverage() : float{
		return round(array_sum($this->tickAverage) / count($this->tickAverage), 2);
	}

	/**
	 * Returns the TPS usage/load in %
	 *
	 * @return float
	 */
	public function getTickUsage() : float{
		return round($this->currentUse * 100, 2);
	}

	/**
	 * Returns the TPS usage/load average in %
	 *
	 * @return float
	 */
	public function getTickUsageAverage() : float{
		return round((array_sum($this->useAverage) / count($this->useAverage)) * 100, 2);
	}

	/**
	 * @return SimpleCommandMap
	 */
	public function getCommandMap(){
		return $this->commandMap;
	}

	/**
	 * @return Player[]
	 */
	public function getLoggedInPlayers() : array{
		return $this->loggedInPlayers;
	}

	/**
	 * @return Player[]
	 */
	public function getOnlinePlayers() : array{
		return $this->playerList;
	}

	public function shouldSavePlayerData() : bool{
		return (bool) $this->getProperty("player.save-player-data", true);
	}

	/**
	 * @param string $name
	 *
	 * @return OfflinePlayer|Player
	 */
	public function getOfflinePlayer(string $name){
		$name = strtolower($name);
		$result = $this->getPlayerExact($name);

		if($result === null){
			$result = new OfflinePlayer($this, $name);
		}

		return $result;
	}

	/**
	 * @param string $name
	 *
	 * @return CompoundTag
	 */
	public function getOfflinePlayerData(string $name) : CompoundTag{
		$name = strtolower($name);
		$path = $this->getDataPath() . "players/";
		if($this->shouldSavePlayerData()){
			if(file_exists($path . "$name.dat")){
				try{
					$nbt = new BigEndianNBTStream();
					$compound = $nbt->readCompressed(file_get_contents($path . "$name.dat"));
					if(!($compound instanceof CompoundTag)){
						throw new \RuntimeException("Invalid data found in \"$name.dat\", expected " . CompoundTag::class . ", got " . (is_object($compound) ? get_class($compound) : gettype($compound)));
					}

					return $compound;
				}catch(\Throwable $e){ //zlib decode error / corrupt data
					rename($path . "$name.dat", $path . "$name.dat.bak");
					$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerCorrupted", [$name]));
				}
			}else{
				$this->logger->notice($this->getLanguage()->translateString("pocketmine.data.playerNotFound", [$name]));
			}
		}
		$spawn = $this->getDefaultLevel()->getSafeSpawn();
		$currentTimeMillis = (int) (microtime(true) * 1000);

		$nbt = new CompoundTag("", [
			new LongTag("firstPlayed", $currentTimeMillis),
			new LongTag("lastPlayed", $currentTimeMillis),
			new ListTag("Pos", [
				new DoubleTag("", $spawn->x),
				new DoubleTag("", $spawn->y),
				new DoubleTag("", $spawn->z)
			], NBT::TAG_Double),
			new StringTag("Level", $this->getDefaultLevel()->getFolderName()),
			//new StringTag("SpawnLevel", $this->getDefaultLevel()->getFolderName()),
			//new IntTag("SpawnX", $spawn->getFloorX()),
			//new IntTag("SpawnY", $spawn->getFloorY()),
			//new IntTag("SpawnZ", $spawn->getFloorZ()),
			//new ByteTag("SpawnForced", 1), //TODO
			new ListTag("Inventory", [], NBT::TAG_Compound),
			new ListTag("EnderChestInventory", [], NBT::TAG_Compound),
			new CompoundTag("Achievements", []),
			new IntTag("playerGameType", $this->getGamemode()),
			new ListTag("Motion", [
				new DoubleTag("", 0.0),
				new DoubleTag("", 0.0),
				new DoubleTag("", 0.0)
			], NBT::TAG_Double),
			new ListTag("Rotation", [
				new FloatTag("", 0.0),
				new FloatTag("", 0.0)
			], NBT::TAG_Float),
			new FloatTag("FallDistance", 0.0),
			new ShortTag("Fire", 0),
			new ShortTag("Air", 300),
			new ByteTag("OnGround", 1),
			new ByteTag("Invulnerable", 0),
			new StringTag("NameTag", $name)
		]);

		return $nbt;

	}

	/**
	 * @param string      $name
	 * @param CompoundTag $nbtTag
	 * @param bool        $async
	 */
	public function saveOfflinePlayerData(string $name, CompoundTag $nbtTag, bool $async = false){
		$ev = new PlayerDataSaveEvent($nbtTag, $name);
		$ev->setCancelled(!$this->shouldSavePlayerData());

		$this->pluginManager->callEvent($ev);

		if(!$ev->isCancelled()){
			$nbt = new BigEndianNBTStream();
			try{
				if($async){
					$this->getScheduler()->scheduleAsyncTask(new FileWriteTask($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed($ev->getSaveData())));
				}else{
					file_put_contents($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed($ev->getSaveData()));
				}
			}catch(\Throwable $e){
				$this->logger->critical($this->getLanguage()->translateString("pocketmine.data.saveError", [$name, $e->getMessage()]));
				$this->logger->logException($e);
			}
		}
	}

	/**
	 * @param string $name
	 *
	 * @return Player|null
	 */
	public function getPlayer(string $name){
		$found = null;
		$name = strtolower($name);
		$delta = PHP_INT_MAX;
		foreach($this->getOnlinePlayers() as $player){
			if(stripos($player->getName(), $name) === 0){
				$curDelta = strlen($player->getName()) - strlen($name);
				if($curDelta < $delta){
					$found = $player;
					$delta = $curDelta;
				}
				if($curDelta === 0){
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * @param string $name
	 *
	 * @return Player|null
	 */
	public function getPlayerExact(string $name){
		$name = strtolower($name);
		foreach($this->getOnlinePlayers() as $player){
			if($player->getLowerCaseName() === $name){
				return $player;
			}
		}

		return null;
	}

	/**
	 * @param string $partialName
	 *
	 * @return Player[]
	 */
	public function matchPlayer(string $partialName) : array{
		$partialName = strtolower($partialName);
		$matchedPlayers = [];
		foreach($this->getOnlinePlayers() as $player){
			if($player->getLowerCaseName() === $partialName){
				$matchedPlayers = [$player];
				break;
			}elseif(stripos($player->getName(), $partialName) !== false){
				$matchedPlayers[] = $player;
			}
		}

		return $matchedPlayers;
	}

	/**
	 * Returns the player online with the specified raw UUID, or null if not found
	 *
	 * @param string $rawUUID
	 *
	 * @return null|Player
	 */
	public function getPlayerByRawUUID(string $rawUUID) : ?Player{
		return $this->playerList[$rawUUID] ?? null;
	}

	/**
	 * Returns the player online with a UUID equivalent to the specified UUID object, or null if not found
	 *
	 * @param UUID $uuid
	 *
	 * @return null|Player
	 */
	public function getPlayerByUUID(UUID $uuid) : ?Player{
		return $this->getPlayerByRawUUID($uuid->toBinary());
	}

	/**
	 * @return Level[]
	 */
	public function getLevels() : array{
		return $this->levels;
	}

	/**
	 * @return Level|null
	 */
	public function getDefaultLevel() : ?Level{
		return $this->levelDefault;
	}

	/**
	 * Sets the default level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 *
	 * @param Level|null $level
	 */
	public function setDefaultLevel(?Level $level) : void{
		if($level === null or ($this->isLevelLoaded($level->getFolderName()) and $level !== $this->levelDefault)){
			$this->levelDefault = $level;
		}
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelLoaded(string $name) : bool{
		return $this->getLevelByName($name) instanceof Level;
	}

	/**
	 * @param int $levelId
	 *
	 * @return Level|null
	 */
	public function getLevel(int $levelId) : ?Level{
		return $this->levels[$levelId] ?? null;
	}

	/**
	 * NOTE: This matches levels based on the FOLDER name, NOT the display name.
	 *
	 * @param string $name
	 *
	 * @return Level|null
	 */
	public function getLevelByName(string $name) : ?Level{
		foreach($this->getLevels() as $level){
			if($level->getFolderName() === $name){
				return $level;
			}
		}

		return null;
	}

	/**
	 * @param Level $level
	 * @param bool  $forceUnload
	 *
	 * @return bool
	 *
	 * @throws \InvalidStateException
	 */
	public function unloadLevel(Level $level, bool $forceUnload = false) : bool{
		if($level === $this->getDefaultLevel() and !$forceUnload){
			throw new \InvalidStateException("The default level cannot be unloaded while running, please switch levels.");
		}

		return $level->unload($forceUnload);
	}

	/**
	 * @internal
	 * @param Level $level
	 */
	public function removeLevel(Level $level) : void{
		unset($this->levels[$level->getId()]);
	}

	/**
	 * Loads a level from the data directory
	 *
	 * @param string $name
	 *
	 * @return bool
	 *
	 * @throws LevelException
	 */
	public function loadLevel(string $name) : bool{
		if(trim($name) === ""){
			throw new LevelException("Invalid empty level name");
		}
		if($this->isLevelLoaded($name)){
			return true;
		}elseif(!$this->isLevelGenerated($name)){
			$this->logger->notice($this->getLanguage()->translateString("pocketmine.level.notFound", [$name]));

			return false;
		}

		$path = $this->getDataPath() . "worlds/" . $name . "/";

		$providerClass = LevelProviderManager::getProvider($path);

		if($providerClass === null){
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, "Cannot identify format of world"]));

			return false;
		}

		try{
			$level = new Level($this, $name, new $providerClass($path));
		}catch(\Throwable $e){

			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, $e->getMessage()]));
			$this->logger->logException($e);
			return false;
		}

		$this->levels[$level->getId()] = $level;

		$level->initLevel();

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));

		$level->setTickRate($this->baseTickRate);

		return true;
	}

	/**
	 * Generates a new level if it does not exist
	 *
	 * @param string      $name
	 * @param int|null    $seed
	 * @param string|null $generator Class name that extends pocketmine\level\generator\Generator
	 * @param array       $options
	 *
	 * @return bool
	 */
	public function generateLevel(string $name, int $seed = null, $generator = null, array $options = []) : bool{
		if(trim($name) === "" or $this->isLevelGenerated($name)){
			return false;
		}

		$seed = $seed ?? Binary::readInt(random_bytes(4));

		if(!isset($options["preset"])){
			$options["preset"] = $this->getConfigString("generator-settings", "");
		}

		if(!($generator !== null and class_exists($generator, true) and is_subclass_of($generator, Generator::class))){
			$generator = Generator::getGenerator($this->getLevelType());
		}

		if(($providerClass = LevelProviderManager::getProviderByName($this->getProperty("level-settings.default-format", "pmanvil"))) === null){
			$providerClass = LevelProviderManager::getProviderByName("pmanvil");
		}

		try{
			$path = $this->getDataPath() . "worlds/" . $name . "/";
			/** @var LevelProvider $providerClass */
			$providerClass::generate($path, $name, $seed, $generator, $options);

			$level = new Level($this, $name, new $providerClass($path));
			$this->levels[$level->getId()] = $level;

			$level->initLevel();

			$level->setTickRate($this->baseTickRate);
		}catch(\Throwable $e){
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.generationError", [$name, $e->getMessage()]));
			$this->logger->logException($e);
			return false;
		}

		$this->getPluginManager()->callEvent(new LevelInitEvent($level));

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));

		$this->getLogger()->notice($this->getLanguage()->translateString("pocketmine.level.backgroundGeneration", [$name]));

		$spawnLocation = $level->getSpawnLocation();
		$centerX = $spawnLocation->getFloorX() >> 4;
		$centerZ = $spawnLocation->getFloorZ() >> 4;

		$order = [];

		for($X = -3; $X <= 3; ++$X){
			for($Z = -3; $Z <= 3; ++$Z){
				$distance = $X ** 2 + $Z ** 2;
				$chunkX = $X + $centerX;
				$chunkZ = $Z + $centerZ;
				$index = Level::chunkHash($chunkX, $chunkZ);
				$order[$index] = $distance;
			}
		}

		asort($order);

		foreach($order as $index => $distance){
			Level::getXZ($index, $chunkX, $chunkZ);
			$level->populateChunk($chunkX, $chunkZ, true);
		}

		return true;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelGenerated(string $name) : bool{
		if(trim($name) === ""){
			return false;
		}
		$path = $this->getDataPath() . "worlds/" . $name . "/";
		if(!($this->getLevelByName($name) instanceof Level)){
			return is_dir($path) and !empty(array_filter(scandir($path, SCANDIR_SORT_NONE), function($v){
				return $v !== ".." and $v !== ".";
			}));
		}

		return true;
	}

	/**
	 * Searches all levels for the entity with the specified ID.
	 * Useful for tracking entities across multiple worlds without needing strong references.
	 *
	 * @param int        $entityId
	 * @param Level|null $expectedLevel Level to look in first for the target
	 *
	 * @return Entity|null
	 */
	public function findEntity(int $entityId, Level $expectedLevel = null){
		$levels = $this->levels;
		if($expectedLevel !== null){
			array_unshift($levels, $expectedLevel);
		}

		foreach($levels as $level){
			assert(!$level->isClosed());
			if(($entity = $level->getEntity($entityId)) instanceof Entity){
				return $entity;
			}
		}

		return null;
	}

	/**
	 * @param string $variable
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getProperty(string $variable, $defaultValue = null){
		if(!array_key_exists($variable, $this->propertyCache)){
			$v = getopt("", ["$variable::"]);
			if(isset($v[$variable])){
				$this->propertyCache[$variable] = $v[$variable];
			}else{
				$this->propertyCache[$variable] = $this->config->getNested($variable);
			}
		}

		return $this->propertyCache[$variable] ?? $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param string $defaultValue
	 *
	 * @return string
	 */
	public function getConfigString(string $variable, string $defaultValue = "") : string{
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (string) $v[$variable];
		}

		return $this->properties->exists($variable) ? (string) $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param string $value
	 */
	public function setConfigString(string $variable, string $value){
		$this->properties->set($variable, $value);
	}

	/**
	 * @param string $variable
	 * @param int    $defaultValue
	 *
	 * @return int
	 */
	public function getConfigInt(string $variable, int $defaultValue = 0) : int{
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (int) $v[$variable];
		}

		return $this->properties->exists($variable) ? (int) $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param int    $value
	 */
	public function setConfigInt(string $variable, int $value){
		$this->properties->set($variable, $value);
	}

	/**
	 * @param string $variable
	 * @param bool   $defaultValue
	 *
	 * @return bool
	 */
	public function getConfigBool(string $variable, bool $defaultValue = false) : bool{
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			$value = $v[$variable];
		}else{
			$value = $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
		}

		if(is_bool($value)){
			return $value;
		}
		switch(strtolower($value)){
			case "on":
			case "true":
			case "1":
			case "yes":
				return true;
		}

		return false;
	}

	/**
	 * @param string $variable
	 * @param bool   $value
	 */
	public function setConfigBool(string $variable, bool $value){
		$this->properties->set($variable, $value ? "1" : "0");
	}

	/**
	 * @param string $name
	 *
	 * @return PluginIdentifiableCommand|null
	 */
	public function getPluginCommand(string $name){
		if(($command = $this->commandMap->getCommand($name)) instanceof PluginIdentifiableCommand){
			return $command;
		}else{
			return null;
		}
	}

	/**
	 * @return BanList
	 */
	public function getNameBans(){
		return $this->banByName;
	}

	/**
	 * @return BanList
	 */
	public function getIPBans(){
		return $this->banByIP;
	}

	/**
	 * @param string $name
	 */
	public function addOp(string $name){
		$this->operators->set(strtolower($name), true);

		if(($player = $this->getPlayerExact($name)) !== null){
			$player->recalculatePermissions();
		}
		$this->operators->save(true);
	}

	/**
	 * @param string $name
	 */
	public function removeOp(string $name){
		$this->operators->remove(strtolower($name));

		if(($player = $this->getPlayerExact($name)) !== null){
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @param string $name
	 */
	public function addWhitelist(string $name){
		$this->whitelist->set(strtolower($name), true);
		$this->whitelist->save(true);
	}

	/**
	 * @param string $name
	 */
	public function removeWhitelist(string $name){
		$this->whitelist->remove(strtolower($name));
		$this->whitelist->save();
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isWhitelisted(string $name) : bool{
		return !$this->hasWhitelist() or $this->operators->exists($name, true) or $this->whitelist->exists($name, true);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isOp(string $name) : bool{
		return $this->operators->exists($name, true);
	}

	/**
	 * @return Config
	 */
	public function getWhitelisted(){
		return $this->whitelist;
	}

	/**
	 * @return Config
	 */
	public function getOps(){
		return $this->operators;
	}

	public function reloadWhitelist(){
		$this->whitelist->reload();
	}

	/**
	 * @return string[]
	 */
	public function getCommandAliases() : array{
		$section = $this->getProperty("aliases");
		$result = [];
		if(is_array($section)){
			foreach($section as $key => $value){
				$commands = [];
				if(is_array($value)){
					$commands = $value;
				}else{
					$commands[] = (string) $value;
				}

				$result[$key] = $commands;
			}
		}

		return $result;
	}

	/**
	 * @return Server
	 */
	public static function getInstance() : Server{
		if(self::$instance === null){
			throw new \RuntimeException("Attempt to retrieve Server instance outside server thread");
		}
		return self::$instance;
	}

	public static function microSleep(int $microseconds){
		Server::$sleeper->synchronized(function(int $ms){
			Server::$sleeper->wait($ms);
		}, $microseconds);
	}

	/**
	 * @param \ClassLoader    $autoloader
	 * @param \ThreadedLogger $logger
	 * @param string          $dataPath
	 * @param string          $pluginPath
	 */
	public function __construct(\ClassLoader $autoloader, \ThreadedLogger $logger, string $dataPath, string $pluginPath){
		if(self::$instance !== null){
			throw new \InvalidStateException("Only one server instance can exist at once");
		}
		self::$instance = $this;
		self::$sleeper = new \Threaded;
		$this->autoloader = $autoloader;
		$this->logger = $logger;

		try{
			if(!file_exists($dataPath . "worlds/")){
				mkdir($dataPath . "worlds/", 0777);
			}

			if(!file_exists($dataPath . "players/")){
				mkdir($dataPath . "players/", 0777);
			}

			if(!file_exists($pluginPath)){
				mkdir($pluginPath, 0777);
			}

			$this->dataPath = realpath($dataPath) . DIRECTORY_SEPARATOR;
			$this->pluginPath = realpath($pluginPath) . DIRECTORY_SEPARATOR;

			$this->console = new CommandReader();

			$version = new VersionString($this->getPocketMineVersion());

			$this->logger->info("Loading pocketmine.yml...");
			if(!file_exists($this->dataPath . "pocketmine.yml")){
				$content = file_get_contents(\pocketmine\RESOURCE_PATH . "pocketmine.yml");
				if($version->isDev()){
					$content = str_replace("preferred-channel: stable", "preferred-channel: beta", $content);
				}
				@file_put_contents($this->dataPath . "pocketmine.yml", $content);
			}
			$this->config = new Config($this->dataPath . "pocketmine.yml", Config::YAML, []);

			define('pocketmine\DEBUG', (int) $this->getProperty("debug.level", 1));

			if(((int) ini_get('zend.assertions')) > 0 and ((bool) $this->getProperty("debug.assertions.warn-if-enabled", true)) !== false){
				$this->logger->warning("Debugging assertions are enabled, this may impact on performance. To disable them, set `zend.assertions = -1` in php.ini.");
			}

			ini_set('assert.exception', '1');

			if($this->logger instanceof MainLogger){
				$this->logger->setLogDebug(\pocketmine\DEBUG > 1);
			}

			$this->logger->info("Loading server properties...");
			$this->properties = new Config($this->dataPath . "server.properties", Config::PROPERTIES, [
				"motd" => \pocketmine\NAME . " Server",
				"server-port" => 19132,
				"white-list" => false,
				"announce-player-achievements" => true,
				"spawn-protection" => 16,
				"max-players" => 20,
				"allow-flight" => false,
				"spawn-animals" => true,
				"spawn-mobs" => true,
				"gamemode" => 0,
				"force-gamemode" => false,
				"hardcore" => false,
				"pvp" => true,
				"difficulty" => 1,
				"generator-settings" => "",
				"level-name" => "world",
				"level-seed" => "",
				"level-type" => "DEFAULT",
				"enable-query" => true,
				"enable-rcon" => false,
				"rcon.password" => substr(base64_encode(random_bytes(20)), 3, 10),
				"auto-save" => true,
				"view-distance" => 8,
				"xbox-auth" => true
			]);

			$this->forceLanguage = (bool) $this->getProperty("settings.force-language", false);
			$this->baseLang = new BaseLang($this->getProperty("settings.language", BaseLang::FALLBACK_LANGUAGE));
			$this->logger->info($this->getLanguage()->translateString("language.selected", [$this->getLanguage()->getName(), $this->getLanguage()->getLang()]));

			$this->memoryManager = new MemoryManager($this);

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.start", [TextFormat::AQUA . $this->getVersion() . TextFormat::RESET]));

			if(($poolSize = $this->getProperty("settings.async-workers", "auto")) === "auto"){
				$poolSize = ServerScheduler::$WORKERS;
				$processors = Utils::getCoreCount() - 2;

				if($processors > 0){
					$poolSize = max(1, $processors);
				}
			}else{
				$poolSize = (int) $poolSize;
			}

			ServerScheduler::$WORKERS = $poolSize;

			if($this->getProperty("network.batch-threshold", 256) >= 0){
				Network::$BATCH_THRESHOLD = (int) $this->getProperty("network.batch-threshold", 256);
			}else{
				Network::$BATCH_THRESHOLD = -1;
			}

			$this->networkCompressionLevel = $this->getProperty("network.compression-level", 7);
			if($this->networkCompressionLevel < 1 or $this->networkCompressionLevel > 9){
				$this->logger->warning("Invalid network compression level $this->networkCompressionLevel set, setting to default 7");
				$this->networkCompressionLevel = 7;
			}
			$this->networkCompressionAsync = $this->getProperty("network.async-compression", true);

			$this->autoTickRate = (bool) $this->getProperty("level-settings.auto-tick-rate", true);
			$this->autoTickRateLimit = (int) $this->getProperty("level-settings.auto-tick-rate-limit", 20);
			$this->alwaysTickPlayers = (int) $this->getProperty("level-settings.always-tick-players", false);
			$this->baseTickRate = (int) $this->getProperty("level-settings.base-tick-rate", 1);

			$this->doTitleTick = ((bool) $this->getProperty("console.title-tick", true)) && Terminal::hasFormattingCodes();

			$this->scheduler = new ServerScheduler();

			if($this->getConfigBool("enable-rcon", false)){
				try{
					$this->rcon = new RCON(
						$this,
						$this->getConfigString("rcon.password", ""),
						$this->getConfigInt("rcon.port", $this->getPort()),
						($ip = $this->getIp()) != "" ? $ip : "0.0.0.0",
						$this->getConfigInt("rcon.threads", 1),
						$this->getConfigInt("rcon.clients-per-thread", 50)
					);
				}catch(\Throwable $e){
					$this->getLogger()->critical("RCON can't be started: " . $e->getMessage());
				}
			}

			$this->entityMetadata = new EntityMetadataStore();
			$this->playerMetadata = new PlayerMetadataStore();
			$this->levelMetadata = new LevelMetadataStore();

			$this->operators = new Config($this->dataPath . "ops.txt", Config::ENUM);
			$this->whitelist = new Config($this->dataPath . "white-list.txt", Config::ENUM);
			if(file_exists($this->dataPath . "banned.txt") and !file_exists($this->dataPath . "banned-players.txt")){
				@rename($this->dataPath . "banned.txt", $this->dataPath . "banned-players.txt");
			}
			@touch($this->dataPath . "banned-players.txt");
			$this->banByName = new BanList($this->dataPath . "banned-players.txt");
			$this->banByName->load();
			@touch($this->dataPath . "banned-ips.txt");
			$this->banByIP = new BanList($this->dataPath . "banned-ips.txt");
			$this->banByIP->load();

			$this->maxPlayers = $this->getConfigInt("max-players", 20);
			$this->setAutoSave($this->getConfigBool("auto-save", true));

			$this->onlineMode = $this->getConfigBool("xbox-auth", true);
			if($this->onlineMode){
				$this->logger->notice($this->getLanguage()->translateString("pocketmine.server.auth.enabled"));
				$this->logger->notice($this->getLanguage()->translateString("pocketmine.server.authProperty.enabled"));
			}else{
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.auth.disabled"));
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.authWarning"));
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.authProperty.disabled"));
			}

			if($this->getConfigBool("hardcore", false) and $this->getDifficulty() < Level::DIFFICULTY_HARD){
				$this->setConfigInt("difficulty", Level::DIFFICULTY_HARD);
			}

			if(\pocketmine\DEBUG >= 0){
				@cli_set_process_title($this->getName() . " " . $this->getPocketMineVersion());
			}

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.networkStart", [$this->getIp() === "" ? "*" : $this->getIp(), $this->getPort()]));
			define("BOOTUP_RANDOM", random_bytes(16));
			$this->serverID = Utils::getMachineUniqueId($this->getIp() . $this->getPort());

			$this->getLogger()->debug("Server unique id: " . $this->getServerUniqueId());
			$this->getLogger()->debug("Machine unique id: " . Utils::getMachineUniqueId());

			$this->network = new Network($this);
			$this->network->setName($this->getMotd());


			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.info", [
				$this->getName(),
				($version->isDev() ? TextFormat::YELLOW : "") . $version->get(true) . TextFormat::RESET,
				$this->getCodename(),
				$this->getApiVersion()
			]));
			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.license", [$this->getName()]));


			Timings::init();
			TimingsHandler::setEnabled((bool) $this->getProperty("settings.enable-profiling", false));

			$this->consoleSender = new ConsoleCommandSender();
			$this->commandMap = new SimpleCommandMap($this);

			Entity::init();
			Tile::init();
			BlockFactory::init();
			Enchantment::init();
			ItemFactory::init();
			Item::initCreativeItems();
			Biome::init();

			$this->craftingManager = new CraftingManager();

			$this->resourceManager = new ResourcePackManager($this->getDataPath() . "resource_packs" . DIRECTORY_SEPARATOR);

			$this->pluginManager = new PluginManager($this, $this->commandMap);
			$this->pluginManager->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this->consoleSender);
			$this->profilingTickRate = (float) $this->getProperty("settings.profile-report-trigger", 20);
			$this->pluginManager->registerInterface(PharPluginLoader::class);
			$this->pluginManager->registerInterface(ScriptPluginLoader::class);

			register_shutdown_function([$this, "crashDump"]);

			$this->queryRegenerateTask = new QueryRegenerateEvent($this, 5);

			$this->pluginManager->loadPlugins($this->pluginPath);

			$this->updater = new AutoUpdater($this, $this->getProperty("auto-updater.host", "update.pmmp.io"));

			$this->enablePlugins(PluginLoadOrder::STARTUP);

			$this->network->registerInterface(new RakLibInterface($this));

			LevelProviderManager::init();
			if(extension_loaded("leveldb")){
				$this->logger->debug($this->getLanguage()->translateString("pocketmine.debug.enable"));
			}

			Generator::registerDefaultGenerators();

			foreach((array) $this->getProperty("worlds", []) as $name => $options){
				if(!is_array($options)){
					continue;
				}
				if(!$this->loadLevel($name)){
					$seed = $options["seed"] ?? time();
					if(is_string($seed) and !is_numeric($seed)){
						$seed = Utils::javaStringHash($seed);
					}elseif(!is_int($seed)){
						$seed = (int) $seed;
					}

					if(isset($options["generator"])){
						$generatorOptions = explode(":", $options["generator"]);
						$generator = Generator::getGenerator(array_shift($generatorOptions));
						if(count($options) > 0){
							$options["preset"] = implode(":", $generatorOptions);
						}
					}else{
						$generator = Generator::getGenerator("default");
					}

					$this->generateLevel($name, $seed, $generator, $options);
				}
			}

			if($this->getDefaultLevel() === null){
				$default = $this->getConfigString("level-name", "world");
				if(trim($default) == ""){
					$this->getLogger()->warning("level-name cannot be null, using default");
					$default = "world";
					$this->setConfigString("level-name", "world");
				}
				if(!$this->loadLevel($default)){
					$seed = getopt("", ["level-seed::"])["level-seed"] ?? $this->properties->get("level-seed", time());
					if(!is_numeric($seed) or bccomp($seed, "9223372036854775807") > 0){
						$seed = Utils::javaStringHash($seed);
					}elseif(PHP_INT_SIZE === 8){
						$seed = (int) $seed;
					}
					$this->generateLevel($default, $seed === 0 ? time() : $seed);
				}

				$this->setDefaultLevel($this->getLevelByName($default));
			}

			if($this->properties->hasChanged()){
				$this->properties->save(true);
			}

			if(!($this->getDefaultLevel() instanceof Level)){
				$this->getLogger()->emergency($this->getLanguage()->translateString("pocketmine.level.defaultError"));
				$this->forceShutdown();

				return;
			}

			if($this->getProperty("ticks-per.autosave", 6000) > 0){
				$this->autoSaveTicks = (int) $this->getProperty("ticks-per.autosave", 6000);
			}

			$this->enablePlugins(PluginLoadOrder::POSTWORLD);

			$this->start();
		}catch(\Throwable $e){
			$this->exceptionHandler($e);
		}
	}

	/**
	 * @param TextContainer|string $message
	 * @param Player[]             $recipients
	 *
	 * @return int
	 */
	public function broadcastMessage($message, array $recipients = null) : int{
		if(!is_array($recipients)){
			return $this->broadcast($message, self::BROADCAST_CHANNEL_USERS);
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * @param string   $tip
	 * @param Player[] $recipients
	 *
	 * @return int
	 */
	public function broadcastTip(string $tip, array $recipients = null) : int{
		if(!is_array($recipients)){
			/** @var Player[] $recipients */
			$recipients = [];
			foreach($this->pluginManager->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof Player and $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendTip($tip);
		}

		return count($recipients);
	}

	/**
	 * @param string   $popup
	 * @param Player[] $recipients
	 *
	 * @return int
	 */
	public function broadcastPopup(string $popup, array $recipients = null) : int{
		if(!is_array($recipients)){
			/** @var Player[] $recipients */
			$recipients = [];

			foreach($this->pluginManager->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof Player and $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendPopup($popup);
		}

		return count($recipients);
	}

	/**
	 * @param string        $title
	 * @param string        $subtitle
	 * @param int           $fadeIn Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
	 * @param int           $stay Duration in ticks to stay on screen for
	 * @param int           $fadeOut Duration in ticks for fade-out.
	 * @param Player[]|null $recipients
	 *
	 * @return int
	 */
	public function broadcastTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1, array $recipients = null) : int{
		if(!is_array($recipients)){
			/** @var Player[] $recipients */
			$recipients = [];

			foreach($this->pluginManager->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof Player and $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->addTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
		}

		return count($recipients);
	}

	/**
	 * @param TextContainer|string $message
	 * @param string               $permissions
	 *
	 * @return int
	 */
	public function broadcast($message, string $permissions) : int{
		/** @var CommandSender[] $recipients */
		$recipients = [];
		foreach(explode(";", $permissions) as $permission){
			foreach($this->pluginManager->getPermissionSubscriptions($permission) as $permissible){
				if($permissible instanceof CommandSender and $permissible->hasPermission($permission)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * Broadcasts a Minecraft packet to a list of players
	 *
	 * @param Player[]   $players
	 * @param DataPacket $packet
	 */
	public function broadcastPacket(array $players, DataPacket $packet){
		$packet->encode();
		$this->batchPackets($players, [$packet], false);
	}

	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param Player[]     $players
	 * @param DataPacket[] $packets
	 * @param bool         $forceSync
	 * @param bool         $immediate
	 */
	public function batchPackets(array $players, array $packets, bool $forceSync = false, bool $immediate = false){
		if(empty($packets)){
			throw new \InvalidArgumentException("Cannot send empty batch");
		}
		Timings::$playerNetworkTimer->startTiming();

		$targets = array_filter($players, function(Player $player) : bool{ return $player->isConnected(); });

		if(!empty($targets)){
			$pk = new BatchPacket();

			foreach($packets as $p){
				$pk->addPacket($p);
			}

			if(Network::$BATCH_THRESHOLD >= 0 and strlen($pk->payload) >= Network::$BATCH_THRESHOLD){
				$pk->setCompressionLevel($this->networkCompressionLevel);
			}else{
				$pk->setCompressionLevel(0); //Do not compress packets under the threshold
				$forceSync = true;
			}

			if(!$forceSync and !$immediate and $this->networkCompressionAsync){
				$task = new CompressBatchedTask($pk, $targets);
				$this->getScheduler()->scheduleAsyncTask($task);
			}else{
				$this->broadcastPacketsCallback($pk, $targets, $immediate);
			}
		}

		Timings::$playerNetworkTimer->stopTiming();
	}

	/**
	 * @param BatchPacket $pk
	 * @param Player[]    $players
	 * @param bool        $immediate
	 */
	public function broadcastPacketsCallback(BatchPacket $pk, array $players, bool $immediate = false){
		if(!$pk->isEncoded){
			$pk->encode();
		}

		foreach($players as $i){
			$i->sendDataPacket($pk, false, $immediate);
		}
	}


	/**
	 * @param int $type
	 */
	public function enablePlugins(int $type){
		foreach($this->pluginManager->getPlugins() as $plugin){
			if(!$plugin->isEnabled() and $plugin->getDescription()->getOrder() === $type){
				$this->enablePlugin($plugin);
			}
		}

		if($type === PluginLoadOrder::POSTWORLD){
			$this->commandMap->registerServerAliases();
			DefaultPermissions::registerCorePermissions();
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function enablePlugin(Plugin $plugin){
		$this->pluginManager->enablePlugin($plugin);
	}

	public function disablePlugins(){
		$this->pluginManager->disablePlugins();
	}

	public function checkConsole(){
		Timings::$serverCommandTimer->startTiming();
		if(($line = $this->console->getLine()) !== null){
			$this->pluginManager->callEvent($ev = new ServerCommandEvent($this->consoleSender, $line));
			if(!$ev->isCancelled()){
				$this->dispatchCommand($ev->getSender(), $ev->getCommand());
			}
		}
		Timings::$serverCommandTimer->stopTiming();
	}

	/**
	 * Executes a command from a CommandSender
	 *
	 * @param CommandSender $sender
	 * @param string        $commandLine
	 *
	 * @return bool
	 */
	public function dispatchCommand(CommandSender $sender, string $commandLine) : bool{
		if($this->commandMap->dispatch($sender, $commandLine)){
			return true;
		}


		$sender->sendMessage($this->getLanguage()->translateString(TextFormat::RED . "%commands.generic.notFound"));

		return false;
	}

	public function reload(){
		$this->logger->info("Saving levels...");

		foreach($this->levels as $level){
			$level->save();
		}

		$this->pluginManager->disablePlugins();
		$this->pluginManager->clearPlugins();
		$this->commandMap->clearCommands();

		$this->logger->info("Reloading properties...");
		$this->properties->reload();
		$this->maxPlayers = $this->getConfigInt("max-players", 20);

		if($this->getConfigBool("hardcore", false) and $this->getDifficulty() < Level::DIFFICULTY_HARD){
			$this->setConfigInt("difficulty", Level::DIFFICULTY_HARD);
		}

		$this->banByIP->load();
		$this->banByName->load();
		$this->reloadWhitelist();
		$this->operators->reload();

		foreach($this->getIPBans()->getEntries() as $entry){
			$this->getNetwork()->blockAddress($entry->getName(), -1);
		}

		$this->pluginManager->registerInterface(PharPluginLoader::class);
		$this->pluginManager->registerInterface(ScriptPluginLoader::class);
		$this->pluginManager->loadPlugins($this->pluginPath);
		$this->enablePlugins(PluginLoadOrder::STARTUP);
		$this->enablePlugins(PluginLoadOrder::POSTWORLD);
		TimingsHandler::reload();
	}

	/**
	 * Shutdowns the server correctly
	 */
	public function shutdown(){
		$this->isRunning = false;
	}

	public function forceShutdown(){
		if($this->hasStopped){
			return;
		}

		try{
			if(!$this->isRunning()){
				$this->sendUsage(SendUsageTask::TYPE_CLOSE);
			}

			$this->hasStopped = true;

			$this->shutdown();
			if($this->rcon instanceof RCON){
				$this->rcon->stop();
			}

			if($this->getProperty("network.upnp-forwarding", false)){
				$this->logger->info("[UPnP] Removing port forward...");
				UPnP::RemovePortForward($this->getPort());
			}

			if($this->pluginManager instanceof PluginManager){
				$this->getLogger()->debug("Disabling all plugins");
				$this->pluginManager->disablePlugins();
			}

			foreach($this->players as $player){
				$player->close($player->getLeaveMessage(), $this->getProperty("settings.shutdown-message", "Server closed"));
			}

			$this->getLogger()->debug("Unloading all levels");
			foreach($this->getLevels() as $level){
				$this->unloadLevel($level, true);
			}

			$this->getLogger()->debug("Removing event handlers");
			HandlerList::unregisterAll();

			if($this->scheduler instanceof ServerScheduler){
				$this->getLogger()->debug("Stopping all tasks");
				$this->scheduler->cancelAllTasks();
				$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);
			}

			if($this->properties !== null and $this->properties->hasChanged()){
				$this->getLogger()->debug("Saving properties");
				$this->properties->save();
			}

			$this->getLogger()->debug("Closing console");
			$this->console->shutdown();
			$this->console->notify();

			if($this->network instanceof Network){
				$this->getLogger()->debug("Stopping network interfaces");
				foreach($this->network->getInterfaces() as $interface){
					$this->getLogger()->debug("Stopping network interface " . get_class($interface));
					$interface->shutdown();
					$this->network->unregisterInterface($interface);
				}
			}

			$this->getLogger()->debug("Collecting cycles");
			gc_collect_cycles();
		}catch(\Throwable $e){
			$this->logger->logException($e);
			$this->logger->emergency("Crashed while crashing, killing process");
			@Utils::kill(getmypid());
		}

	}

	/**
	 * @return QueryRegenerateEvent
	 */
	public function getQueryInformation(){
		return $this->queryRegenerateTask;
	}

	/**
	 * Starts the PocketMine-MP server and starts processing ticks and packets
	 */
	private function start(){
		if($this->getConfigBool("enable-query", true)){
			$this->queryHandler = new QueryHandler();
		}

		foreach($this->getIPBans()->getEntries() as $entry){
			$this->network->blockAddress($entry->getName(), -1);
		}

		if($this->getProperty("settings.send-usage", true)){
			$this->sendUsageTicker = 6000;
			$this->sendUsage(SendUsageTask::TYPE_OPEN);
		}


		if($this->getProperty("network.upnp-forwarding", false)){
			$this->logger->info("[UPnP] Trying to port forward...");
			try{
				UPnP::PortForward($this->getPort());
			}catch(\Throwable $e){
				$this->logger->alert("UPnP portforward failed: " . $e->getMessage());
			}
		}

		$this->tickCounter = 0;

		if(function_exists("pcntl_signal")){
			pcntl_signal(SIGTERM, [$this, "handleSignal"]);
			pcntl_signal(SIGINT, [$this, "handleSignal"]);
			pcntl_signal(SIGHUP, [$this, "handleSignal"]);
			$this->dispatchSignals = true;
		}

		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.defaultGameMode", [self::getGamemodeString($this->getGamemode())]));

		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.startFinished", [round(microtime(true) - \pocketmine\START_TIME, 3)]));

		$this->tickProcessor();
		$this->forceShutdown();
	}

	public function handleSignal($signo){
		if($signo === SIGTERM or $signo === SIGINT or $signo === SIGHUP){
			$this->shutdown();
		}
	}

	/**
	 * @param \Throwable $e
	 * @param array|null $trace
	 */
	public function exceptionHandler(\Throwable $e, $trace = null){
		if($e === null){
			return;
		}

		global $lastError;

		if($trace === null){
			$trace = $e->getTrace();
		}

		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errline = $e->getLine();

		$errstr = preg_replace('/\s+/', ' ', trim($errstr));

		$errfile = Utils::cleanPath($errfile);

		$this->logger->logException($e, $trace);

		$lastError = [
			"type" => \get_class($e),
			"message" => $errstr,
			"fullFile" => $e->getFile(),
			"file" => $errfile,
			"line" => $errline,
			"trace" => Utils::getTrace(0, $trace)
		];

		global $lastExceptionError, $lastError;
		$lastExceptionError = $lastError;
		$this->crashDump();
	}

	public function crashDump(){
		if(!$this->isRunning){
			return;
		}
		if($this->sendUsageTicker > 0){
			$this->sendUsage(SendUsageTask::TYPE_CLOSE);
		}
		$this->hasStopped = false;

		ini_set("error_reporting", '0');
		ini_set("memory_limit", '-1'); //Fix error dump not dumped on memory problems
		try{
			$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.create"));
			$dump = new CrashDump($this);

			$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.submit", [$dump->getPath()]));

			if($this->getProperty("auto-report.enabled", true) !== false){
				$report = true;
				$plugin = $dump->getData()["plugin"];
				if(is_string($plugin)){
					$p = $this->pluginManager->getPlugin($plugin);
					if($p instanceof Plugin and !($p->getPluginLoader() instanceof PharPluginLoader)){
						$report = false;
					}
				}

				if($dump->getData()["error"]["type"] === "E_PARSE" or $dump->getData()["error"]["type"] === "E_COMPILE_ERROR"){
					$report = false;
				}

				if(strrpos(\pocketmine\GIT_COMMIT, "-dirty") !== false or \pocketmine\GIT_COMMIT === str_repeat("00", 20)){
					$this->logger->debug("Not sending crashdump due to locally modified");
					$report = false; //Don't send crashdumps for locally modified builds
				}

				if($report){
					$url = ($this->getProperty("auto-report.use-https", true) ? "https" : "http") . "://" . $this->getProperty("auto-report.host", "crash.pmmp.io") . "/submit/api";
					$reply = Utils::postURL($url, [
						"report" => "yes",
						"name" => $this->getName() . " " . $this->getPocketMineVersion(),
						"email" => "crash@pocketmine.net",
						"reportPaste" => base64_encode($dump->getEncodedData())
					]);

					if($reply !== false and ($data = json_decode($reply)) !== null and isset($data->crashId) and isset($data->crashUrl)){
						$reportId = $data->crashId;
						$reportUrl = $data->crashUrl;
						$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.archive", [$reportUrl, $reportId]));
					}
				}
			}
		}catch(\Throwable $e){
			$this->logger->logException($e);
			try{
				$this->logger->critical($this->getLanguage()->translateString("pocketmine.crash.error", [$e->getMessage()]));
			}catch(\Throwable $e){}
		}

		//$this->checkMemory();
		//$dump .= "Memory Usage Tracking: \r\n" . chunk_split(base64_encode(gzdeflate(implode(";", $this->memoryStats), 9))) . "\r\n";

		$this->forceShutdown();
		$this->isRunning = false;
		@Utils::kill(getmypid());
		exit(1);
	}

	public function __debugInfo(){
		return [];
	}

	private function tickProcessor(){
		$this->nextTick = microtime(true);
		while($this->isRunning){
			$this->tick();
			$next = $this->nextTick - 0.0001;
			if($next > microtime(true)){
				try{
					@time_sleep_until($next);
				}catch(\Throwable $e){
					//Sometimes $next is less than the current time. High load?
				}
			}
		}
	}

	public function onPlayerLogin(Player $player){
		if($this->sendUsageTicker > 0){
			$this->uniquePlayers[$player->getRawUniqueId()] = $player->getRawUniqueId();
		}

		$this->loggedInPlayers[$player->getRawUniqueId()] = $player;
	}

	public function onPlayerLogout(Player $player){
		unset($this->loggedInPlayers[$player->getRawUniqueId()]);
	}

	public function addPlayer(Player $player){
		$this->players[spl_object_hash($player)] = $player;
	}

	/**
	 * @param Player $player
	 */
	public function removePlayer(Player $player){
		unset($this->players[spl_object_hash($player)]);
	}

	public function addOnlinePlayer(Player $player){
		$this->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkin(), $player->getXuid());

		$this->playerList[$player->getRawUniqueId()] = $player;
	}

	public function removeOnlinePlayer(Player $player){
		if(isset($this->playerList[$player->getRawUniqueId()])){
			unset($this->playerList[$player->getRawUniqueId()]);

			$this->removePlayerListData($player->getUniqueId());
		}
	}

	/**
	 * @param UUID          $uuid
	 * @param int           $entityId
	 * @param string        $name
	 * @param Skin          $skin
	 * @param string        $xboxUserId
	 * @param Player[]|null $players
	 */
	public function updatePlayerListData(UUID $uuid, int $entityId, string $name, Skin $skin, string $xboxUserId = "", array $players = null){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;

		$pk->entries[] = PlayerListEntry::createAdditionEntry($uuid, $entityId, $name, "", 0, $skin, $xboxUserId);

		$this->broadcastPacket($players ?? $this->playerList, $pk);
	}

	/**
	 * @param UUID          $uuid
	 * @param Player[]|null $players
	 */
	public function removePlayerListData(UUID $uuid, array $players = null){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries[] = PlayerListEntry::createRemovalEntry($uuid);
		$this->broadcastPacket($players ?? $this->playerList, $pk);
	}

	/**
	 * @param Player $p
	 */
	public function sendFullPlayerListData(Player $p){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		foreach($this->playerList as $player){
			$pk->entries[] = PlayerListEntry::createAdditionEntry($player->getUniqueId(), $player->getId(), $player->getDisplayName(), "", 0, $player->getSkin(), $player->getXuid());
		}

		$p->dataPacket($pk);
	}

	private function checkTickUpdates($currentTick, $tickTime){
		foreach($this->players as $p){
			if(!$p->loggedIn and ($tickTime - $p->creationTime) >= 10){
				$p->close("", "Login timeout");
			}elseif($this->alwaysTickPlayers and $p->spawned){
				$p->onUpdate($currentTick);
			}
		}

		//Do level ticks
		foreach($this->getLevels() as $level){
			if($level->getTickRate() > $this->baseTickRate and --$level->tickRateCounter > 0){
				continue;
			}
			try{
				$levelTime = microtime(true);
				$level->doTick($currentTick);
				$tickMs = (microtime(true) - $levelTime) * 1000;
				$level->tickRateTime = $tickMs;

				if($this->autoTickRate){
					if($tickMs < 50 and $level->getTickRate() > $this->baseTickRate){
						$level->setTickRate($r = $level->getTickRate() - 1);
						if($r > $this->baseTickRate){
							$level->tickRateCounter = $level->getTickRate();
						}
						$this->getLogger()->debug("Raising level \"{$level->getName()}\" tick rate to {$level->getTickRate()} ticks");
					}elseif($tickMs >= 50){
						if($level->getTickRate() === $this->baseTickRate){
							$level->setTickRate(max($this->baseTickRate + 1, min($this->autoTickRateLimit, (int) floor($tickMs / 50))));
							$this->getLogger()->debug(sprintf("Level \"%s\" took %gms, setting tick rate to %d ticks", $level->getName(), (int) round($tickMs, 2), $level->getTickRate()));
						}elseif(($tickMs / $level->getTickRate()) >= 50 and $level->getTickRate() < $this->autoTickRateLimit){
							$level->setTickRate($level->getTickRate() + 1);
							$this->getLogger()->debug(sprintf("Level \"%s\" took %gms, setting tick rate to %d ticks", $level->getName(), (int) round($tickMs, 2), $level->getTickRate()));
						}
						$level->tickRateCounter = $level->getTickRate();
					}
				}
			}catch(\Throwable $e){
				if(!$level->isClosed()){
					$this->logger->critical($this->getLanguage()->translateString("pocketmine.level.tickError", [$level->getName(), $e->getMessage()]));
				}else{
					$this->logger->critical($this->getLanguage()->translateString("pocketmine.level.tickUnloadError", [$level->getName()]));
				}
				$this->logger->logException($e);
			}
		}
	}

	public function doAutoSave(){
		if($this->getAutoSave()){
			Timings::$worldSaveTimer->startTiming();
			foreach($this->players as $index => $player){
				if($player->spawned){
					$player->save(true);
				}elseif(!$player->isConnected()){
					$this->removePlayer($player);
				}
			}

			foreach($this->getLevels() as $level){
				$level->save(false);
			}
			Timings::$worldSaveTimer->stopTiming();
		}
	}

	public function sendUsage($type = SendUsageTask::TYPE_STATUS){
		if((bool) $this->getProperty("anonymous-statistics.enabled", true)){
			$this->scheduler->scheduleAsyncTask(new SendUsageTask($this, $type, $this->uniquePlayers));
		}
		$this->uniquePlayers = [];
	}


	/**
	 * @return BaseLang
	 */
	public function getLanguage(){
		return $this->baseLang;
	}

	/**
	 * @return bool
	 */
	public function isLanguageForced() : bool{
		return $this->forceLanguage;
	}

	/**
	 * @return Network
	 */
	public function getNetwork(){
		return $this->network;
	}

	/**
	 * @return MemoryManager
	 */
	public function getMemoryManager(){
		return $this->memoryManager;
	}

	private function titleTick(){
		Timings::$titleTickTimer->startTiming();
		$d = Utils::getRealMemoryUsage();

		$u = Utils::getMemoryUsage(true);
		$usage = sprintf("%g/%g/%g/%g MB @ %d threads", round(($u[0] / 1024) / 1024, 2), round(($d[0] / 1024) / 1024, 2), round(($u[1] / 1024) / 1024, 2), round(($u[2] / 1024) / 1024, 2), Utils::getThreadCount());

		echo "\x1b]0;" . $this->getName() . " " .
			$this->getPocketMineVersion() .
			" | Online " . count($this->players) . "/" . $this->getMaxPlayers() .
			" | Memory " . $usage .
			" | U " . round($this->network->getUpload() / 1024, 2) .
			" D " . round($this->network->getDownload() / 1024, 2) .
			" kB/s | TPS " . $this->getTicksPerSecondAverage() .
			" | Load " . $this->getTickUsageAverage() . "%\x07";

		Timings::$titleTickTimer->stopTiming();
	}

	/**
	 * @param AdvancedSourceInterface $interface
	 * @param string                  $address
	 * @param int                     $port
	 * @param string                  $payload
	 *
	 * TODO: move this to Network
	 */
	public function handlePacket(AdvancedSourceInterface $interface, string $address, int $port, string $payload){
		try{
			if(strlen($payload) > 2 and substr($payload, 0, 2) === "\xfe\xfd" and $this->queryHandler instanceof QueryHandler){
				$this->queryHandler->handle($interface, $address, $port, $payload);
			}
		}catch(\Throwable $e){
			if(\pocketmine\DEBUG > 1){
				$this->logger->logException($e);
			}

			$this->getNetwork()->blockAddress($address, 600);
		}
		//TODO: add raw packet events
	}


	/**
	 * Tries to execute a server tick
	 */
	private function tick() : void{
		$tickTime = microtime(true);
		if(($tickTime - $this->nextTick) < -0.025){ //Allow half a tick of diff
			return;
		}

		Timings::$serverTickTimer->startTiming();

		++$this->tickCounter;

		$this->checkConsole();

		Timings::$connectionTimer->startTiming();
		$this->network->processInterfaces();

		if($this->rcon !== null){
			$this->rcon->check();
		}

		Timings::$connectionTimer->stopTiming();

		Timings::$schedulerTimer->startTiming();
		$this->scheduler->mainThreadHeartbeat($this->tickCounter);
		Timings::$schedulerTimer->stopTiming();

		$this->checkTickUpdates($this->tickCounter, $tickTime);

		foreach($this->players as $player){
			$player->checkNetwork();
		}

		if(($this->tickCounter % 20) === 0){
			if($this->doTitleTick){
				$this->titleTick();
			}
			$this->currentTPS = 20;
			$this->currentUse = 0;

			$this->network->updateName();
			$this->network->resetStatistics();
		}

		if(($this->tickCounter & 0b111111111) === 0){
			try{
				$this->getPluginManager()->callEvent($this->queryRegenerateTask = new QueryRegenerateEvent($this, 5));
				if($this->queryHandler !== null){
					$this->queryHandler->regenerateInfo();
				}
			}catch(\Throwable $e){
				$this->logger->logException($e);
			}
		}

		if($this->autoSave and ++$this->autoSaveTicker >= $this->autoSaveTicks){
			$this->autoSaveTicker = 0;
			$this->doAutoSave();
		}

		if($this->sendUsageTicker > 0 and --$this->sendUsageTicker === 0){
			$this->sendUsageTicker = 6000;
			$this->sendUsage(SendUsageTask::TYPE_STATUS);
		}

		if(($this->tickCounter % 100) === 0){
			foreach($this->levels as $level){
				$level->clearCache();
			}

			if($this->getTicksPerSecondAverage() < 12){
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.tickOverload"));
			}
		}

		if($this->dispatchSignals and $this->tickCounter % 5 === 0){
			pcntl_signal_dispatch();
		}

		$this->getMemoryManager()->check();

		Timings::$serverTickTimer->stopTiming();

		$now = microtime(true);
		$this->currentTPS = min(20, 1 / max(0.001, $now - $tickTime));
		$this->currentUse = min(1, ($now - $tickTime) / 0.05);

		TimingsHandler::tick($this->currentTPS <= $this->profilingTickRate);

		array_shift($this->tickAverage);
		$this->tickAverage[] = $this->currentTPS;
		array_shift($this->useAverage);
		$this->useAverage[] = $this->currentUse;

		if(($this->nextTick - $tickTime) < -1){
			$this->nextTick = $tickTime;
		}else{
			$this->nextTick += 0.05;
		}
	}

	/**
	 * Called when something attempts to serialize the server instance.
	 *
	 * @throws \BadMethodCallException because Server instances cannot be serialized
	 */
	public function __sleep(){
		throw new \BadMethodCallException("Cannot serialize Server instance");
	}
}
