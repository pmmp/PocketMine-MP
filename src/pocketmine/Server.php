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
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketBroadcastEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\inventory\CraftingManager;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\lang\Language;
use pocketmine\lang\LanguageNotFoundException;
use pocketmine\lang\TextContainer;
use pocketmine\level\biome\Biome;
use pocketmine\level\format\io\exception\UnsupportedLevelFormatException;
use pocketmine\level\format\io\LevelProvider;
use pocketmine\level\format\io\LevelProviderManager;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GeneratorManager;
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
use pocketmine\network\AdvancedNetworkInterface;
use pocketmine\network\mcpe\CompressBatchPromise;
use pocketmine\network\mcpe\CompressBatchTask;
use pocketmine\network\mcpe\NetworkCipher;
use pocketmine\network\mcpe\NetworkCompression;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\PacketStream;
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
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\plugin\PluginManager;
use pocketmine\plugin\ScriptPluginLoader;
use pocketmine\resourcepacks\ResourcePackManager;
use pocketmine\scheduler\AsyncPool;
use pocketmine\scheduler\SendUsageTask;
use pocketmine\snooze\SleeperHandler;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\tile\Tile;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;
use pocketmine\updater\AutoUpdater;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;
use pocketmine\utils\Internet;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;

/**
 * The class that manages everything
 */
class Server{
	public const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	public const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	/** @var Server */
	private static $instance = null;

	/** @var SleeperHandler */
	private $tickSleeper;

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

	/** @var bool */
	private $hasStopped = false;

	/** @var PluginManager */
	private $pluginManager = null;

	/** @var float */
	private $profilingTickRate = 20;

	/** @var AutoUpdater */
	private $updater = null;

	/** @var AsyncPool */
	private $asyncPool;

	/**
	 * Counts the ticks since the server start
	 *
	 * @var int
	 */
	private $tickCounter = 0;
	/** @var int */
	private $nextTick = 0;
	/** @var float[] */
	private $tickAverage = [20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20];
	/** @var float[] */
	private $useAverage = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	/** @var float */
	private $currentTPS = 20;
	/** @var float */
	private $currentUse = 0;

	/** @var bool */
	private $doTitleTick = true;

	/** @var int */
	private $sendUsageTicker = 0;

	/** @var bool */
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
	/** @var bool */
	private $networkCompressionAsync = true;

	/** @var bool */
	private $autoTickRate = true;
	/** @var int */
	private $autoTickRateLimit = 20;
	/** @var bool */
	private $alwaysTickPlayers = false;
	/** @var int */
	private $baseTickRate = 1;

	/** @var int */
	private $autoSaveTicker = 0;
	/** @var int */
	private $autoSaveTicks = 6000;

	/** @var Language */
	private $language;
	/** @var bool */
	private $forceLanguage = false;

	/** @var UUID */
	private $serverID;

	/** @var \ClassLoader */
	private $autoloader;
	/** @var string */
	private $dataPath;
	/** @var string */
	private $pluginPath;

	/** @var string[] */
	private $uniquePlayers = [];

	/** @var QueryHandler */
	private $queryHandler;

	/** @var QueryRegenerateEvent */
	private $queryRegenerateTask = null;

	/** @var Config */
	private $properties;
	/** @var mixed[] */
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
	public function getVersion() : string{
		return ProtocolInfo::MINECRAFT_VERSION;
	}

	/**
	 * @return string
	 */
	public function getApiVersion() : string{
		return \pocketmine\BASE_VERSION;
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
		$str = $this->getConfigString("server-ip");
		return $str !== "" ? $str : "0.0.0.0";
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
	 * @return \AttachableThreadedLogger
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

	public function getAsyncPool() : AsyncPool{
		return $this->asyncPool;
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
	 * @return CommandReader
	 */
	public function getCommandReader() : CommandReader{
		return $this->console;
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
	 * Returns whether the server has stored any saved data for this player.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function hasOfflinePlayerData(string $name) : bool{
		return file_exists($this->getDataPath() . "players/$name.dat");
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
					return $nbt->readCompressed(file_get_contents($path . "$name.dat"));
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
	 */
	public function saveOfflinePlayerData(string $name, CompoundTag $nbtTag){
		$ev = new PlayerDataSaveEvent($nbtTag, $name);
		$ev->setCancelled(!$this->shouldSavePlayerData());

		$ev->call();

		if(!$ev->isCancelled()){
			$nbt = new BigEndianNBTStream();
			try{
				file_put_contents($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed($ev->getSaveData()));
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

		return $level->onUnload($forceUnload);
	}

	/**
	 * @internal
	 *
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

		$providers = LevelProviderManager::getMatchingProviders($path);
		if(count($providers) !== 1){
			$this->logger->error($this->language->translateString("pocketmine.level.loadError", [
				$name,
				empty($providers) ?
					$this->language->translateString("pocketmine.level.unknownFormat") :
					$this->language->translateString("pocketmine.level.ambiguousFormat", [implode(", ", array_keys($providers))])
			]));
			return false;
		}
		$providerClass = array_shift($providers);

		try{
			/** @see LevelProvider::__construct() */
			$level = new Level($this, $name, new $providerClass($path));
		}catch(UnsupportedLevelFormatException $e){
			$this->logger->error($this->language->translateString("pocketmine.level.loadError", [$name, $e->getMessage()]));
			return false;
		}

		$this->levels[$level->getId()] = $level;

		(new LevelLoadEvent($level))->call();

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
			$generator = GeneratorManager::getGenerator($this->getLevelType());
		}

		$providerClass = LevelProviderManager::getDefault();

		$path = $this->getDataPath() . "worlds/" . $name . "/";
		/** @var LevelProvider $providerClass */
		$providerClass::generate($path, $name, $seed, $generator, $options);

		/** @see LevelProvider::__construct() */
		$level = new Level($this, $name, new $providerClass($path));
		$this->levels[$level->getId()] = $level;

		$level->setTickRate($this->baseTickRate);

		(new LevelInitEvent($level))->call();

		(new LevelLoadEvent($level))->call();

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
			return !empty(LevelProviderManager::getMatchingProviders($path));
		}

		return true;
	}

	/**
	 * Searches all levels for the entity with the specified ID.
	 * Useful for tracking entities across multiple worlds without needing strong references.
	 *
	 * @param int $entityId
	 *
	 * @return Entity|null
	 */
	public function findEntity(int $entityId){
		foreach($this->levels as $level){
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
		$this->operators->save();
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
		$this->whitelist->save();
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

	/**
	 * @param \ClassLoader              $autoloader
	 * @param \AttachableThreadedLogger $logger
	 * @param string                    $dataPath
	 * @param string                    $pluginPath
	 */
	public function __construct(\ClassLoader $autoloader, \AttachableThreadedLogger $logger, string $dataPath, string $pluginPath){
		if(self::$instance !== null){
			throw new \InvalidStateException("Only one server instance can exist at once");
		}
		self::$instance = $this;

		$this->tickSleeper = new SleeperHandler();
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

			$this->logger->info("Loading pocketmine.yml...");
			if(!file_exists($this->dataPath . "pocketmine.yml")){
				$content = file_get_contents(\pocketmine\RESOURCE_PATH . "pocketmine.yml");
				if(\pocketmine\IS_DEVELOPMENT_BUILD){
					$content = str_replace("preferred-channel: stable", "preferred-channel: beta", $content);
				}
				@file_put_contents($this->dataPath . "pocketmine.yml", $content);
			}
			$this->config = new Config($this->dataPath . "pocketmine.yml", Config::YAML, []);

			$this->logger->info("Loading server properties...");
			$this->properties = new Config($this->dataPath . "server.properties", Config::PROPERTIES, [
				"motd" => \pocketmine\NAME . " Server",
				"server-port" => 19132,
				"white-list" => false,
				"announce-player-achievements" => true,
				"spawn-protection" => 16,
				"max-players" => 20,
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
				"xbox-auth" => true,
				"language" => "eng"
			]);

			define('pocketmine\DEBUG', (int) $this->getProperty("debug.level", 1));

			$this->forceLanguage = (bool) $this->getProperty("settings.force-language", false);
			$selectedLang = $this->getConfigString("language", $this->getProperty("settings.language", Language::FALLBACK_LANGUAGE));
			try{
				$this->language = new Language($selectedLang);
			}catch(LanguageNotFoundException $e){
				$this->logger->error($e->getMessage());
				try{
					$this->language = new Language(Language::FALLBACK_LANGUAGE);
				}catch(LanguageNotFoundException $e){
					$this->logger->emergency("Fallback language \"" . Language::FALLBACK_LANGUAGE . "\" not found");
					return;
				}
			}

			$this->logger->info($this->getLanguage()->translateString("language.selected", [$this->getLanguage()->getName(), $this->getLanguage()->getLang()]));

			if(\pocketmine\IS_DEVELOPMENT_BUILD){
				if(!((bool) $this->getProperty("settings.enable-dev-builds", false))){
					$this->logger->emergency($this->language->translateString("pocketmine.server.devBuild.error1", [\pocketmine\NAME]));
					$this->logger->emergency($this->language->translateString("pocketmine.server.devBuild.error2"));
					$this->logger->emergency($this->language->translateString("pocketmine.server.devBuild.error3"));
					$this->logger->emergency($this->language->translateString("pocketmine.server.devBuild.error4", ["settings.enable-dev-builds"]));
					$this->logger->emergency($this->language->translateString("pocketmine.server.devBuild.error5", ["https://github.com/pmmp/PocketMine-MP/releases"]));
					$this->forceShutdown();

					return;
				}

				$this->logger->warning(str_repeat("-", 40));
				$this->logger->warning($this->language->translateString("pocketmine.server.devBuild.warning1", [\pocketmine\NAME]));
				$this->logger->warning($this->language->translateString("pocketmine.server.devBuild.warning2"));
				$this->logger->warning($this->language->translateString("pocketmine.server.devBuild.warning3"));
				$this->logger->warning(str_repeat("-", 40));
			}

			if(((int) ini_get('zend.assertions')) > 0 and ((bool) $this->getProperty("debug.assertions.warn-if-enabled", true)) !== false){
				$this->logger->warning("Debugging assertions are enabled, this may impact on performance. To disable them, set `zend.assertions = -1` in php.ini.");
			}

			ini_set('assert.exception', '1');

			if($this->logger instanceof MainLogger){
				$this->logger->setLogDebug(\pocketmine\DEBUG > 1);
			}

			$this->memoryManager = new MemoryManager($this);

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.start", [TextFormat::AQUA . $this->getVersion() . TextFormat::RESET]));

			if(($poolSize = $this->getProperty("settings.async-workers", "auto")) === "auto"){
				$poolSize = 2;
				$processors = Utils::getCoreCount() - 2;

				if($processors > 0){
					$poolSize = max(1, $processors);
				}
			}else{
				$poolSize = max(1, (int) $poolSize);
			}

			$this->asyncPool = new AsyncPool($poolSize, (int) max(-1, (int) $this->getProperty("memory.async-worker-hard-limit", 256)), $this->autoloader, $this->logger);

			if($this->getProperty("network.batch-threshold", 256) >= 0){
				NetworkCompression::$THRESHOLD = (int) $this->getProperty("network.batch-threshold", 256);
			}else{
				NetworkCompression::$THRESHOLD = -1;
			}

			NetworkCompression::$LEVEL = $this->getProperty("network.compression-level", 7);
			if(NetworkCompression::$LEVEL < 1 or NetworkCompression::$LEVEL > 9){
				$this->logger->warning("Invalid network compression level " . NetworkCompression::$LEVEL . " set, setting to default 7");
				NetworkCompression::$LEVEL = 7;
			}
			$this->networkCompressionAsync = (bool) $this->getProperty("network.async-compression", true);

			NetworkCipher::$ENABLED = (bool) $this->getProperty("network.enable-encryption", true);

			$this->autoTickRate = (bool) $this->getProperty("level-settings.auto-tick-rate", true);
			$this->autoTickRateLimit = (int) $this->getProperty("level-settings.auto-tick-rate-limit", 20);
			$this->alwaysTickPlayers = (bool) $this->getProperty("level-settings.always-tick-players", false);
			$this->baseTickRate = (int) $this->getProperty("level-settings.base-tick-rate", 1);

			$this->doTitleTick = ((bool) $this->getProperty("console.title-tick", true)) && Terminal::hasFormattingCodes();


			$consoleSender = new ConsoleCommandSender();
			PermissionManager::getInstance()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $consoleSender);

			$consoleNotifier = new SleeperNotifier();
			$this->console = new CommandReader($consoleNotifier);
			$this->tickSleeper->addNotifier($consoleNotifier, function() use ($consoleSender) : void{
				Timings::$serverCommandTimer->startTiming();
				while(($line = $this->console->getLine()) !== null){
					$this->dispatchCommand($consoleSender, $line);
				}
				Timings::$serverCommandTimer->stopTiming();
			});
			$this->console->start(PTHREADS_INHERIT_NONE);

			if($this->getConfigBool("enable-rcon", false)){
				try{
					$this->rcon = new RCON(
						$this,
						$this->getConfigString("rcon.password", ""),
						$this->getConfigInt("rcon.port", $this->getPort()),
						$this->getIp(),
						$this->getConfigInt("rcon.max-clients", 50)
					);
				}catch(\Exception $e){
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

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.networkStart", [$this->getIp(), $this->getPort()]));
			define("BOOTUP_RANDOM", random_bytes(16));
			$this->serverID = Utils::getMachineUniqueId($this->getIp() . $this->getPort());

			$this->getLogger()->debug("Server unique id: " . $this->getServerUniqueId());
			$this->getLogger()->debug("Machine unique id: " . Utils::getMachineUniqueId());

			$this->network = new Network($this);
			$this->network->setName($this->getMotd());


			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.info", [
				$this->getName(),
				(\pocketmine\IS_DEVELOPMENT_BUILD ? TextFormat::YELLOW : "") . $this->getPocketMineVersion() . TextFormat::RESET
			]));
			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.license", [$this->getName()]));


			Timings::init();
			TimingsHandler::setEnabled((bool) $this->getProperty("settings.enable-profiling", false));

			$this->commandMap = new SimpleCommandMap($this);

			Entity::init();
			Tile::init();
			BlockFactory::init();
			BlockFactory::registerStaticRuntimeIdMappings();
			Enchantment::init();
			ItemFactory::init();
			Item::initCreativeItems();
			Biome::init();

			$this->craftingManager = new CraftingManager();

			$this->resourceManager = new ResourcePackManager($this->getDataPath() . "resource_packs" . DIRECTORY_SEPARATOR, $this->logger);

			$this->pluginManager = new PluginManager($this, ((bool) $this->getProperty("plugins.legacy-data-dir", true)) ? null : $this->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR);
			$this->profilingTickRate = (float) $this->getProperty("settings.profile-report-trigger", 20);
			$this->pluginManager->registerInterface(new PharPluginLoader($this->autoloader));
			$this->pluginManager->registerInterface(new ScriptPluginLoader());

			register_shutdown_function([$this, "crashDump"]);

			$this->queryRegenerateTask = new QueryRegenerateEvent($this, 5);

			$this->pluginManager->loadPlugins($this->pluginPath);

			$this->updater = new AutoUpdater($this, $this->getProperty("auto-updater.host", "update.pmmp.io"));

			$this->enablePlugins(PluginLoadOrder::STARTUP);

			$this->network->registerInterface(new RakLibInterface($this));

			LevelProviderManager::init();
			if(($format = LevelProviderManager::getProviderByName($formatName = (string) $this->getProperty("level-settings.default-format"))) !== null){
				LevelProviderManager::setDefault($format);
			}elseif($formatName !== ""){
				$this->logger->warning($this->language->translateString("pocketmine.level.badDefaultFormat", [$formatName]));
			}

			if(extension_loaded("leveldb")){
				$this->logger->debug($this->getLanguage()->translateString("pocketmine.debug.enable"));
			}

			GeneratorManager::registerDefaultGenerators();

			foreach((array) $this->getProperty("worlds", []) as $name => $options){
				if(!is_array($options)){
					continue;
				}
				if(!$this->loadLevel($name)){
					if(isset($options["generator"])){
						$generatorOptions = explode(":", $options["generator"]);
						$generator = GeneratorManager::getGenerator(array_shift($generatorOptions));
						if(count($options) > 0){
							$options["preset"] = implode(":", $generatorOptions);
						}
					}else{
						$generator = GeneratorManager::getGenerator("default");
					}

					$this->generateLevel($name, Generator::convertSeed((string) ($options["seed"] ?? "")), $generator, $options);
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
					$this->generateLevel($default, Generator::convertSeed($this->getConfigString("level-seed")));
				}

				$this->setDefaultLevel($this->getLevelByName($default));
			}

			if($this->properties->hasChanged()){
				$this->properties->save();
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
			foreach(PermissionManager::getInstance()->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
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

			foreach(PermissionManager::getInstance()->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
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

			foreach(PermissionManager::getInstance()->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
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
			foreach(PermissionManager::getInstance()->getPermissionSubscriptions($permission) as $permissible){
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
	 *
	 * @return bool
	 */
	public function broadcastPacket(array $players, DataPacket $packet) : bool{
		return $this->broadcastPackets($players, [$packet]);
	}

	/**
	 * @param Player[]     $players
	 * @param DataPacket[] $packets
	 *
	 * @return bool
	 */
	public function broadcastPackets(array $players, array $packets) : bool{
		if(empty($packets)){
			throw new \InvalidArgumentException("Cannot broadcast empty list of packets");
		}

		$ev = new DataPacketBroadcastEvent($players, $packets);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		/** @var NetworkSession[] $targets */
		$targets = [];
		foreach($ev->getPlayers() as $player){
			if($player->isConnected()){
				$targets[] = $player->getNetworkSession();
			}
		}
		if(empty($targets)){
			return false;
		}

		$stream = new PacketStream();
		foreach($ev->getPackets() as $packet){
			$stream->putPacket($packet);
		}

		if(NetworkCompression::$THRESHOLD < 0 or strlen($stream->buffer) < NetworkCompression::$THRESHOLD){
			foreach($targets as $target){
				foreach($ev->getPackets() as $pk){
					$target->addToSendBuffer($pk);
				}
			}
		}else{
			$promise = $this->prepareBatch($stream);
			foreach($targets as $target){
				$target->queueCompressed($promise);
			}
		}

		return true;
	}

	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param PacketStream $stream
	 * @param bool         $forceSync
	 *
	 * @return CompressBatchPromise
	 */
	public function prepareBatch(PacketStream $stream, bool $forceSync = false) : CompressBatchPromise{
		try{
			Timings::$playerNetworkSendCompressTimer->startTiming();

			$compressionLevel = NetworkCompression::$LEVEL;
			if(NetworkCompression::$THRESHOLD < 0 or strlen($stream->buffer) < NetworkCompression::$THRESHOLD){
				$compressionLevel = 0; //Do not compress packets under the threshold
				$forceSync = true;
			}

			$promise = new CompressBatchPromise();
			if(!$forceSync and $this->networkCompressionAsync){
				$task = new CompressBatchTask($stream, $compressionLevel, $promise);
				$this->asyncPool->submitTask($task);
			}else{
				$promise->resolve(NetworkCompression::compress($stream->buffer, $compressionLevel));
			}

			return $promise;
		}finally{
			Timings::$playerNetworkSendCompressTimer->stopTiming();
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

	/**
	 * Executes a command from a CommandSender
	 *
	 * @param CommandSender $sender
	 * @param string        $commandLine
	 * @param bool          $internal
	 *
	 * @return bool
	 */
	public function dispatchCommand(CommandSender $sender, string $commandLine, bool $internal = false) : bool{
		if(!$internal){
			$ev = new CommandEvent($sender, $commandLine);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}

			$commandLine = $ev->getCommand();
		}

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
		PermissionManager::getInstance()->clearPermissions();
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

		$this->pluginManager->registerInterface(new PharPluginLoader($this->autoloader));
		$this->pluginManager->registerInterface(new ScriptPluginLoader());
		$this->pluginManager->loadPlugins($this->pluginPath);
		$this->enablePlugins(PluginLoadOrder::STARTUP);
		$this->enablePlugins(PluginLoadOrder::POSTWORLD);
		TimingsHandler::reload();
	}

	/**
	 * Shuts the server down correctly
	 */
	public function shutdown(){
		$this->isRunning = false;
	}

	public function forceShutdown(){
		if($this->hasStopped){
			return;
		}

		if($this->doTitleTick){
			echo "\x1b]0;\x07";
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

			if($this->asyncPool instanceof AsyncPool){
				$this->getLogger()->debug("Shutting down async task worker pool");
				$this->asyncPool->shutdown();
			}

			if($this->properties !== null and $this->properties->hasChanged()){
				$this->getLogger()->debug("Saving properties");
				$this->properties->save();
			}

			if($this->console instanceof CommandReader){
				$this->getLogger()->debug("Closing console");
				$this->console->shutdown();
				$this->console->notify();
			}

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
			}catch(\Exception $e){
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
			"type" => get_class($e),
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

				$stamp = $this->getDataPath() . "crashdumps/.last_crash";
				$crashInterval = 120; //2 minutes
				if(file_exists($stamp) and !($report = (filemtime($stamp) + $crashInterval < time()))){
					$this->logger->debug("Not sending crashdump due to last crash less than $crashInterval seconds ago");
				}
				@touch($stamp); //update file timestamp

				$plugin = $dump->getData()["plugin"];
				if(is_string($plugin)){
					$p = $this->pluginManager->getPlugin($plugin);
					if($p instanceof Plugin and !($p->getPluginLoader() instanceof PharPluginLoader)){
						$report = false;
					}
				}

				if($dump->getData()["error"]["type"] === \ParseError::class){
					$report = false;
				}

				if(strrpos(\pocketmine\GIT_COMMIT, "-dirty") !== false or \pocketmine\GIT_COMMIT === str_repeat("00", 20)){
					$this->logger->debug("Not sending crashdump due to locally modified");
					$report = false; //Don't send crashdumps for locally modified builds
				}

				if($report){
					$url = ($this->getProperty("auto-report.use-https", true) ? "https" : "http") . "://" . $this->getProperty("auto-report.host", "crash.pmmp.io") . "/submit/api";
					$reply = Internet::postURL($url, [
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

		$this->forceShutdown();
		$this->isRunning = false;

		//Force minimum uptime to be >= 120 seconds, to reduce the impact of spammy crash loops
		$spacing = ((int) \pocketmine\START_TIME) - time() + 120;
		if($spacing > 0){
			sleep($spacing);
		}
		@Utils::kill(getmypid());
		exit(1);
	}

	public function __debugInfo(){
		return [];
	}

	public function getTickSleeper() : SleeperHandler{
		return $this->tickSleeper;
	}

	private function tickProcessor(){
		$this->nextTick = microtime(true);

		while($this->isRunning){
			$this->tick();

			//sleeps are self-correcting - if we undersleep 1ms on this tick, we'll sleep an extra ms on the next tick
			$this->tickSleeper->sleepUntil($this->nextTick);
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

		$pk->entries[] = PlayerListEntry::createAdditionEntry($uuid, $entityId, $name, $skin, $xboxUserId);

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
			$pk->entries[] = PlayerListEntry::createAdditionEntry($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkin(), $player->getXuid());
		}

		$p->sendDataPacket($pk);
	}

	private function checkTickUpdates(int $currentTick) : void{
		if($this->alwaysTickPlayers){
			foreach($this->players as $p){
				if($p->spawned){
					$p->onUpdate($currentTick);
				}
			}
		}

		//Do level ticks
		foreach($this->levels as $k => $level){
			if(!isset($this->levels[$k])){
				// Level unloaded during the tick of a level earlier in this loop, perhaps by plugin
				continue;
			}
			if($level->getTickRate() > $this->baseTickRate and --$level->tickRateCounter > 0){
				continue;
			}

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
		}
	}

	public function doAutoSave(){
		if($this->getAutoSave()){
			Timings::$worldSaveTimer->startTiming();
			foreach($this->players as $index => $player){
				if($player->spawned){
					$player->save();
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
			$this->asyncPool->submitTask(new SendUsageTask($this, $type, $this->uniquePlayers));
		}
		$this->uniquePlayers = [];
	}


	/**
	 * @return Language
	 */
	public function getLanguage(){
		return $this->language;
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
	 * @param AdvancedNetworkInterface $interface
	 * @param string                   $address
	 * @param int                      $port
	 * @param string                   $payload
	 *
	 * TODO: move this to Network
	 */
	public function handlePacket(AdvancedNetworkInterface $interface, string $address, int $port, string $payload){
		try{
			if(strlen($payload) > 2 and substr($payload, 0, 2) === "\xfe\xfd" and $this->queryHandler instanceof QueryHandler){
				$this->queryHandler->handle($interface, $address, $port, $payload);
			}else{
				$this->logger->debug("Unhandled raw packet from $address $port: " . bin2hex($payload));
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

		Timings::$schedulerTimer->startTiming();
		$this->pluginManager->tickSchedulers($this->tickCounter);
		Timings::$schedulerTimer->stopTiming();

		Timings::$schedulerAsyncTimer->startTiming();
		$this->asyncPool->collectTasks();
		Timings::$schedulerAsyncTimer->stopTiming();

		$this->checkTickUpdates($this->tickCounter);

		Timings::$connectionTimer->startTiming();
		$this->network->tick();
		Timings::$connectionTimer->stopTiming();

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
			($this->queryRegenerateTask = new QueryRegenerateEvent($this, 5))->call();
			if($this->queryHandler !== null){
				$this->queryHandler->regenerateInfo();
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
