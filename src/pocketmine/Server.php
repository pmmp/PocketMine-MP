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
use pocketmine\network\AdvancedSourceInterface;
use pocketmine\network\CompressBatchedTask;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
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
use pocketmine\utils\Config;
use pocketmine\utils\Internet;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Process;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;
use function array_filter;
use function array_key_exists;
use function array_shift;
use function array_sum;
use function asort;
use function assert;
use function base64_encode;
use function class_exists;
use function cli_set_process_title;
use function count;
use function define;
use function explode;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function function_exists;
use function get_class;
use function getopt;
use function gettype;
use function implode;
use function ini_set;
use function is_array;
use function is_bool;
use function is_dir;
use function is_object;
use function is_string;
use function is_subclass_of;
use function json_decode;
use function max;
use function microtime;
use function min;
use function mkdir;
use function ob_end_flush;
use function pcntl_signal;
use function pcntl_signal_dispatch;
use function preg_replace;
use function random_bytes;
use function random_int;
use function realpath;
use function register_shutdown_function;
use function rename;
use function round;
use function scandir;
use function sleep;
use function spl_object_hash;
use function sprintf;
use function str_repeat;
use function str_replace;
use function stripos;
use function strlen;
use function strrpos;
use function strtolower;
use function substr;
use function time;
use function touch;
use function trim;
use const DIRECTORY_SEPARATOR;
use const INT32_MAX;
use const INT32_MIN;
use const PHP_EOL;
use const PHP_INT_MAX;
use const PTHREADS_INHERIT_NONE;
use const SCANDIR_SORT_NONE;
use const SIGHUP;
use const SIGINT;
use const SIGTERM;

/**
 * The class that manages everything
 */
class Server{
	public const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	public const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	/** @var Server|null */
	private static $instance = null;

	/** @var \Threaded|null */
	private static $sleeper = null;

	/** @var SleeperHandler */
	private $tickSleeper;

	/** @var BanList */
	private $banByName;

	/** @var BanList */
	private $banByIP;

	/** @var Config */
	private $operators;

	/** @var Config */
	private $whitelist;

	/** @var bool */
	private $isRunning = true;

	/** @var bool */
	private $hasStopped = false;

	/** @var PluginManager */
	private $pluginManager;

	/** @var float */
	private $profilingTickRate = 20;

	/** @var AutoUpdater */
	private $updater;

	/** @var AsyncPool */
	private $asyncPool;

	/**
	 * Counts the ticks since the server start
	 *
	 * @var int
	 */
	private $tickCounter = 0;
	/** @var float */
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
	private $console;

	/** @var SimpleCommandMap */
	private $commandMap;

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

	/** @var RCON|null */
	private $rcon = null;

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
	/** @var int */
	public $networkCompressionLevel = 7;

	/** @var int */
	private $autoSaveTicker = 0;
	/** @var int */
	private $autoSaveTicks = 6000;

	/** @var BaseLang */
	private $baseLang;
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

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private $uniquePlayers = [];

	/** @var QueryHandler|null */
	private $queryHandler = null;

	/** @var QueryRegenerateEvent */
	private $queryRegenerateTask;

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

	/** @var Level|null */
	private $levelDefault = null;

	public function getName() : string{
		return \pocketmine\NAME;
	}

	public function isRunning() : bool{
		return $this->isRunning;
	}

	public function getPocketMineVersion() : string{
		return \pocketmine\VERSION;
	}

	public function getVersion() : string{
		return ProtocolInfo::MINECRAFT_VERSION;
	}

	public function getApiVersion() : string{
		return \pocketmine\BASE_VERSION;
	}

	public function getFilePath() : string{
		return \pocketmine\PATH;
	}

	public function getResourcePath() : string{
		return \pocketmine\RESOURCE_PATH;
	}

	public function getDataPath() : string{
		return $this->dataPath;
	}

	public function getPluginPath() : string{
		return $this->pluginPath;
	}

	public function getMaxPlayers() : int{
		return $this->maxPlayers;
	}

	/**
	 * Returns whether the server requires that players be authenticated to Xbox Live. If true, connecting players who
	 * are not logged into Xbox Live will be disconnected.
	 */
	public function getOnlineMode() : bool{
		return $this->onlineMode;
	}

	/**
	 * Alias of {@link #getOnlineMode()}.
	 */
	public function requiresAuthentication() : bool{
		return $this->getOnlineMode();
	}

	public function getPort() : int{
		return $this->getConfigInt("server-port", 19132);
	}

	public function getViewDistance() : int{
		return max(2, $this->getConfigInt("view-distance", 8));
	}

	/**
	 * Returns a view distance up to the currently-allowed limit.
	 */
	public function getAllowedViewDistance(int $distance) : int{
		return max(2, min($distance, $this->memoryManager->getViewDistance($this->getViewDistance())));
	}

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

	public function getAutoSave() : bool{
		return $this->autoSave;
	}

	/**
	 * @return void
	 */
	public function setAutoSave(bool $value){
		$this->autoSave = $value;
		foreach($this->getLevels() as $level){
			$level->setAutoSave($this->autoSave);
		}
	}

	public function getLevelType() : string{
		return $this->getConfigString("level-type", "DEFAULT");
	}

	public function getGenerateStructures() : bool{
		return $this->getConfigBool("generate-structures", true);
	}

	public function getGamemode() : int{
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	public function getForceGamemode() : bool{
		return $this->getConfigBool("force-gamemode", false);
	}

	/**
	 * Returns the gamemode text name
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
	 */
	public function getDifficulty() : int{
		return $this->getConfigInt("difficulty", Level::DIFFICULTY_NORMAL);
	}

	public function hasWhitelist() : bool{
		return $this->getConfigBool("white-list", false);
	}

	public function getSpawnRadius() : int{
		return $this->getConfigInt("spawn-protection", 16);
	}

	/**
	 * @deprecated
	 */
	public function getAllowFlight() : bool{
		return true;
	}

	public function isHardcore() : bool{
		return $this->getConfigBool("hardcore", false);
	}

	public function getDefaultGamemode() : int{
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

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

	public function getResourcePackManager() : ResourcePackManager{
		return $this->resourceManager;
	}

	public function getAsyncPool() : AsyncPool{
		return $this->asyncPool;
	}

	public function getTick() : int{
		return $this->tickCounter;
	}

	/**
	 * Returns the last server TPS measure
	 */
	public function getTicksPerSecond() : float{
		return round($this->currentTPS, 2);
	}

	/**
	 * Returns the last server TPS average measure
	 */
	public function getTicksPerSecondAverage() : float{
		return round(array_sum($this->tickAverage) / count($this->tickAverage), 2);
	}

	/**
	 * Returns the TPS usage/load in %
	 */
	public function getTickUsage() : float{
		return round($this->currentUse * 100, 2);
	}

	/**
	 * Returns the TPS usage/load average in %
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

	private function getPlayerDataPath(string $username) : string{
		return $this->getDataPath() . '/players/' . strtolower($username) . '.dat';
	}

	/**
	 * Returns whether the server has stored any saved data for this player.
	 */
	public function hasOfflinePlayerData(string $name) : bool{
		return file_exists($this->getPlayerDataPath($name));
	}

	public function getOfflinePlayerData(string $name) : CompoundTag{
		$name = strtolower($name);
		$path = $this->getPlayerDataPath($name);
		if($this->shouldSavePlayerData()){
			if(file_exists($path)){
				try{
					$nbt = new BigEndianNBTStream();
					$compound = $nbt->readCompressed(file_get_contents($path));
					if(!($compound instanceof CompoundTag)){
						throw new \RuntimeException("Invalid data found in \"$name.dat\", expected " . CompoundTag::class . ", got " . (is_object($compound) ? get_class($compound) : gettype($compound)));
					}

					return $compound;
				}catch(\Throwable $e){ //zlib decode error / corrupt data
					rename($path, $path . '.bak');
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
	 * @return void
	 */
	public function saveOfflinePlayerData(string $name, CompoundTag $nbtTag){
		$ev = new PlayerDataSaveEvent($nbtTag, $name);
		$ev->setCancelled(!$this->shouldSavePlayerData());

		$ev->call();

		if(!$ev->isCancelled()){
			$nbt = new BigEndianNBTStream();
			try{
				file_put_contents($this->getPlayerDataPath($name), $nbt->writeCompressed($ev->getSaveData()));
			}catch(\Throwable $e){
				$this->logger->critical($this->getLanguage()->translateString("pocketmine.data.saveError", [$name, $e->getMessage()]));
				$this->logger->logException($e);
			}
		}
	}

	/**
	 * Returns an online player whose name begins with or equals the given string (case insensitive).
	 * The closest match will be returned, or null if there are no online matches.
	 *
	 * @see Server::getPlayerExact()
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
	 * Returns an online player with the given name (case insensitive), or null if not found.
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
	 * Returns a list of online players whose names contain with the given string (case insensitive).
	 * If an exact match is found, only that match is returned.
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
	 */
	public function getPlayerByRawUUID(string $rawUUID) : ?Player{
		return $this->playerList[$rawUUID] ?? null;
	}

	/**
	 * Returns the player online with a UUID equivalent to the specified UUID object, or null if not found
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

	public function getDefaultLevel() : ?Level{
		return $this->levelDefault;
	}

	/**
	 * Sets the default level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 */
	public function setDefaultLevel(?Level $level) : void{
		if($level === null or ($this->isLevelLoaded($level->getFolderName()) and $level !== $this->levelDefault)){
			$this->levelDefault = $level;
		}
	}

	public function isLevelLoaded(string $name) : bool{
		return $this->getLevelByName($name) instanceof Level;
	}

	public function getLevel(int $levelId) : ?Level{
		return $this->levels[$levelId] ?? null;
	}

	/**
	 * NOTE: This matches levels based on the FOLDER name, NOT the display name.
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
	 * @throws \InvalidStateException
	 */
	public function unloadLevel(Level $level, bool $forceUnload = false) : bool{
		if($level === $this->getDefaultLevel() and !$forceUnload){
			throw new \InvalidStateException("The default world cannot be unloaded while running, please switch worlds.");
		}

		return $level->unload($forceUnload);
	}

	/**
	 * @internal
	 */
	public function removeLevel(Level $level) : void{
		unset($this->levels[$level->getId()]);
	}

	/**
	 * Loads a level from the data directory
	 *
	 * @throws LevelException
	 */
	public function loadLevel(string $name) : bool{
		if(trim($name) === ""){
			throw new LevelException("Invalid empty world name");
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
			/**
			 * @var LevelProvider $provider
			 * @see LevelProvider::__construct()
			 */
			$provider = new $providerClass($path);
		}catch(LevelException $e){
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, $e->getMessage()]));
			return false;
		}
		try{
			GeneratorManager::getGenerator($provider->getGenerator(), true);
		}catch(\InvalidArgumentException $e){
			$this->logger->error($this->getLanguage()->translateString("pocketmine.level.loadError", [$name, "Unknown generator \"" . $provider->getGenerator() . "\""]));
			return false;
		}

		$level = new Level($this, $name, $provider);

		$this->levels[$level->getId()] = $level;

		(new LevelLoadEvent($level))->call();

		return true;
	}

	/**
	 * Generates a new level if it does not exist
	 *
	 * @param string|null $generator Class name that extends pocketmine\level\generator\Generator
	 * @phpstan-param class-string<Generator> $generator
	 * @phpstan-param array<string, mixed>    $options
	 */
	public function generateLevel(string $name, int $seed = null, $generator = null, array $options = []) : bool{
		if(trim($name) === "" or $this->isLevelGenerated($name)){
			return false;
		}

		$seed = $seed ?? random_int(INT32_MIN, INT32_MAX);

		if(!isset($options["preset"])){
			$options["preset"] = $this->getConfigString("generator-settings", "");
		}

		if(!($generator !== null and class_exists($generator, true) and is_subclass_of($generator, Generator::class))){
			$generator = GeneratorManager::getGenerator($this->getLevelType());
		}

		if(($providerClass = LevelProviderManager::getProviderByName($this->getProperty("level-settings.default-format", "pmanvil"))) === null){
			$providerClass = LevelProviderManager::getProviderByName("pmanvil");
			if($providerClass === null){
				throw new \InvalidStateException("Default world provider has not been registered");
			}
		}

		$path = $this->getDataPath() . "worlds/" . $name . "/";
		/** @var LevelProvider $providerClass */
		$providerClass::generate($path, $name, $seed, $generator, $options);

		/** @see LevelProvider::__construct() */
		$level = new Level($this, $name, new $providerClass($path));
		$this->levels[$level->getId()] = $level;

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

	public function isLevelGenerated(string $name) : bool{
		if(trim($name) === ""){
			return false;
		}
		$path = $this->getDataPath() . "worlds/" . $name . "/";
		if(!($this->getLevelByName($name) instanceof Level)){
			return is_dir($path) and count(array_filter(scandir($path, SCANDIR_SORT_NONE), function(string $v) : bool{
				return $v !== ".." and $v !== ".";
			})) > 0;
		}

		return true;
	}

	/**
	 * Searches all levels for the entity with the specified ID.
	 * Useful for tracking entities across multiple worlds without needing strong references.
	 *
	 * @param Level|null $expectedLevel @deprecated Level to look in first for the target
	 *
	 * @return Entity|null
	 */
	public function findEntity(int $entityId, Level $expectedLevel = null){
		foreach($this->levels as $level){
			assert(!$level->isClosed());
			if(($entity = $level->getEntity($entityId)) instanceof Entity){
				return $entity;
			}
		}

		return null;
	}

	/**
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

	public function getConfigString(string $variable, string $defaultValue = "") : string{
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (string) $v[$variable];
		}

		return $this->properties->exists($variable) ? (string) $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @return void
	 */
	public function setConfigString(string $variable, string $value){
		$this->properties->set($variable, $value);
	}

	public function getConfigInt(string $variable, int $defaultValue = 0) : int{
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (int) $v[$variable];
		}

		return $this->properties->exists($variable) ? (int) $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @return void
	 */
	public function setConfigInt(string $variable, int $value){
		$this->properties->set($variable, $value);
	}

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
	 * @return void
	 */
	public function setConfigBool(string $variable, bool $value){
		$this->properties->set($variable, $value ? "1" : "0");
	}

	/**
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
	 * @return void
	 */
	public function addOp(string $name){
		$this->operators->set(strtolower($name), true);

		if(($player = $this->getPlayerExact($name)) !== null){
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @return void
	 */
	public function removeOp(string $name){
		$this->operators->remove(strtolower($name));

		if(($player = $this->getPlayerExact($name)) !== null){
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @return void
	 */
	public function addWhitelist(string $name){
		$this->whitelist->set(strtolower($name), true);
		$this->whitelist->save();
	}

	/**
	 * @return void
	 */
	public function removeWhitelist(string $name){
		$this->whitelist->remove(strtolower($name));
		$this->whitelist->save();
	}

	public function isWhitelisted(string $name) : bool{
		return !$this->hasWhitelist() or $this->operators->exists($name, true) or $this->whitelist->exists($name, true);
	}

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

	/**
	 * @return void
	 */
	public function reloadWhitelist(){
		$this->whitelist->reload();
	}

	/**
	 * @return string[][]
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

	public static function getInstance() : Server{
		if(self::$instance === null){
			throw new \RuntimeException("Attempt to retrieve Server instance outside server thread");
		}
		return self::$instance;
	}

	/**
	 * @return void
	 */
	public static function microSleep(int $microseconds){
		if(self::$sleeper === null){
			self::$sleeper = new \Threaded();
		}
		self::$sleeper->synchronized(function(int $ms) : void{
			Server::$sleeper->wait($ms);
		}, $microseconds);
	}

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
				"gamemode" => 0,
				"force-gamemode" => false,
				"hardcore" => false,
				"pvp" => true,
				"difficulty" => Level::DIFFICULTY_NORMAL,
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
			$this->baseLang = new BaseLang($this->getConfigString("language", $this->getProperty("settings.language", BaseLang::FALLBACK_LANGUAGE)));
			$this->logger->info($this->getLanguage()->translateString("language.selected", [$this->getLanguage()->getName(), $this->getLanguage()->getLang()]));

			if(\pocketmine\IS_DEVELOPMENT_BUILD and !((bool) $this->getProperty("settings.enable-dev-builds", false))){
				$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error1", [\pocketmine\NAME]));
				$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error2"));
				$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error3"));
				$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error4", ["settings.enable-dev-builds"]));
				$this->logger->emergency($this->baseLang->translateString("pocketmine.server.devBuild.error5", ["https://github.com/pmmp/PocketMine-MP/releases"]));
				$this->forceShutdown();
				return;
			}

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

			$this->asyncPool = new AsyncPool($this, $poolSize, max(-1, (int) $this->getProperty("memory.async-worker-hard-limit", 256)), $this->autoloader, $this->logger);

			if($this->getProperty("network.batch-threshold", 256) >= 0){
				Network::$BATCH_THRESHOLD = (int) $this->getProperty("network.batch-threshold", 256);
			}else{
				Network::$BATCH_THRESHOLD = -1;
			}

			$this->networkCompressionLevel = (int) $this->getProperty("network.compression-level", 6);
			if($this->networkCompressionLevel < 1 or $this->networkCompressionLevel > 9){
				$this->logger->warning("Invalid network compression level $this->networkCompressionLevel set, setting to default 6");
				$this->networkCompressionLevel = 6;
			}
			$this->networkCompressionAsync = (bool) $this->getProperty("network.async-compression", true);

			$this->doTitleTick = ((bool) $this->getProperty("console.title-tick", true)) && Terminal::hasFormattingCodes();

			$consoleSender = new ConsoleCommandSender();
			PermissionManager::getInstance()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $consoleSender);

			$consoleNotifier = new SleeperNotifier();
			$this->console = new CommandReader($consoleNotifier);
			$this->tickSleeper->addNotifier($consoleNotifier, function() use ($consoleSender) : void{
				Timings::$serverCommandTimer->startTiming();
				while(($line = $this->console->getLine()) !== null){
					$ev = new ServerCommandEvent($consoleSender, $line);
					$ev->call();
					if(!$ev->isCancelled()){
						$this->dispatchCommand($ev->getSender(), $ev->getCommand());
					}
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
			Enchantment::init();
			ItemFactory::init();
			Item::initCreativeItems();
			Biome::init();

			LevelProviderManager::init();
			if(extension_loaded("leveldb")){
				$this->logger->debug($this->getLanguage()->translateString("pocketmine.debug.enable"));
			}
			GeneratorManager::registerDefaultGenerators();

			$this->craftingManager = new CraftingManager();

			$this->resourceManager = new ResourcePackManager($this->getDataPath() . "resource_packs" . DIRECTORY_SEPARATOR, $this->logger);

			$this->pluginManager = new PluginManager($this, $this->commandMap, ((bool) $this->getProperty("plugins.legacy-data-dir", true)) ? null : $this->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR);
			$this->profilingTickRate = (float) $this->getProperty("settings.profile-report-trigger", 20);
			$this->pluginManager->registerInterface(new PharPluginLoader($this->autoloader));
			$this->pluginManager->registerInterface(new ScriptPluginLoader());

			register_shutdown_function([$this, "crashDump"]);

			$this->queryRegenerateTask = new QueryRegenerateEvent($this);

			$this->updater = new AutoUpdater($this, $this->getProperty("auto-updater.host", "update.pmmp.io"));

			$this->pluginManager->loadPlugins($this->pluginPath);
			$this->enablePlugins(PluginLoadOrder::STARTUP);

			$this->network->registerInterface(new RakLibInterface($this));

			foreach((array) $this->getProperty("worlds", []) as $name => $options){
				if($options === null){
					$options = [];
				}elseif(!is_array($options)){
					continue;
				}
				if(!$this->loadLevel($name)){
					if(isset($options["generator"])){
						$generatorOptions = explode(":", $options["generator"]);
						$generator = GeneratorManager::getGenerator(array_shift($generatorOptions));
						if(count($generatorOptions) > 0){
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
	 * @param CommandSender[]|null $recipients
	 */
	public function broadcastMessage($message, array $recipients = null) : int{
		if(!is_array($recipients)){
			return $this->broadcast($message, self::BROADCAST_CHANNEL_USERS);
		}

		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * @param Player[]|null $recipients
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

		foreach($recipients as $recipient){
			$recipient->sendTip($tip);
		}

		return count($recipients);
	}

	/**
	 * @param Player[]|null $recipients
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

		foreach($recipients as $recipient){
			$recipient->sendPopup($popup);
		}

		return count($recipients);
	}

	/**
	 * @param int           $fadeIn Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
	 * @param int           $stay Duration in ticks to stay on screen for
	 * @param int           $fadeOut Duration in ticks for fade-out.
	 * @param Player[]|null $recipients
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

		foreach($recipients as $recipient){
			$recipient->sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
		}

		return count($recipients);
	}

	/**
	 * @param TextContainer|string $message
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
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	public function batchPackets(array $players, array $packets, bool $forceSync = false, bool $immediate = false){
		if(count($packets) === 0){
			throw new \InvalidArgumentException("Cannot send empty batch");
		}
		Timings::$playerNetworkTimer->startTiming();

		$targets = array_filter($players, function(Player $player) : bool{ return $player->isConnected(); });

		if(count($targets) > 0){
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
				$this->asyncPool->submitTask($task);
			}else{
				$this->broadcastPacketsCallback($pk, $targets, $immediate);
			}
		}

		Timings::$playerNetworkTimer->stopTiming();
	}

	/**
	 * @param Player[]    $players
	 *
	 * @return void
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
	 * @return void
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
	 * @return void
	 */
	public function enablePlugin(Plugin $plugin){
		$this->pluginManager->enablePlugin($plugin);
	}

	/**
	 * @return void
	 */
	public function disablePlugins(){
		$this->pluginManager->disablePlugins();
	}

	/**
	 * Executes a command from a CommandSender
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

	/**
	 * @return void
	 */
	public function reload(){
		$this->logger->info("Saving worlds...");

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
	 *
	 * @return void
	 */
	public function shutdown(){
		$this->isRunning = false;
	}

	/**
	 * @return void
	 */
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

			if((bool) $this->getProperty("network.upnp-forwarding", false)){
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

			$this->getLogger()->debug("Unloading all worlds");
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
		}catch(\Throwable $e){
			$this->logger->logException($e);
			$this->logger->emergency("Crashed while crashing, killing process");
			@Process::kill(Process::pid());
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
	private function start() : void{
		if($this->getConfigBool("enable-query", true)){
			$this->queryHandler = new QueryHandler();
		}

		foreach($this->getIPBans()->getEntries() as $entry){
			$this->network->blockAddress($entry->getName(), -1);
		}

		if((bool) $this->getProperty("settings.send-usage", true)){
			$this->sendUsageTicker = 6000;
			$this->sendUsage(SendUsageTask::TYPE_OPEN);
		}

		if((bool) $this->getProperty("network.upnp-forwarding", false)){
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

		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.donate", [TextFormat::AQUA . "https://patreon.com/pocketminemp" . TextFormat::RESET]));
		$this->logger->info($this->getLanguage()->translateString("pocketmine.server.startFinished", [round(microtime(true) - \pocketmine\START_TIME, 3)]));

		$this->tickProcessor();
		$this->forceShutdown();
	}

	/**
	 * @param int $signo
	 *
	 * @return void
	 */
	public function handleSignal($signo){
		if($signo === SIGTERM or $signo === SIGINT or $signo === SIGHUP){
			$this->shutdown();
		}
	}

	/**
	 * @param mixed[][]|null $trace
	 * @phpstan-param list<array<string, mixed>>|null $trace
	 *
	 * @return void
	 */
	public function exceptionHandler(\Throwable $e, $trace = null){
		while(@ob_end_flush()){}
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
			"trace" => $trace
		];

		global $lastExceptionError, $lastError;
		$lastExceptionError = $lastError;
		$this->crashDump();
	}

	/**
	 * @return void
	 */
	public function crashDump(){
		while(@ob_end_flush()){}
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
						$this->logger->debug("Not sending crashdump due to caused by non-phar plugin");
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
					$url = ((bool) $this->getProperty("auto-report.use-https", true) ? "https" : "http") . "://" . $this->getProperty("auto-report.host", "crash.pmmp.io") . "/submit/api";
					$postUrlError = "Unknown error";
					$reply = Internet::postURL($url, [
						"report" => "yes",
						"name" => $this->getName() . " " . $this->getPocketMineVersion(),
						"email" => "crash@pocketmine.net",
						"reportPaste" => base64_encode($dump->getEncodedData())
					], 10, [], $postUrlError);

					if($reply !== false and ($data = json_decode($reply)) !== null){
						if(isset($data->crashId) and isset($data->crashUrl)){
							$reportId = $data->crashId;
							$reportUrl = $data->crashUrl;
							$this->logger->emergency($this->getLanguage()->translateString("pocketmine.crash.archive", [$reportUrl, $reportId]));
						}elseif(isset($data->error)){
							$this->logger->emergency("Automatic crash report submission failed: $data->error");
						}
					}else{
						$this->logger->emergency("Failed to communicate with crash archive: $postUrlError");
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
			echo "--- Waiting $spacing seconds to throttle automatic restart (you can kill the process safely now) ---" . PHP_EOL;
			sleep($spacing);
		}
		@Process::kill(Process::pid());
		exit(1);
	}

	/**
	 * @return mixed[]
	 */
	public function __debugInfo(){
		return [];
	}

	public function getTickSleeper() : SleeperHandler{
		return $this->tickSleeper;
	}

	private function tickProcessor() : void{
		$this->nextTick = microtime(true);

		while($this->isRunning){
			$this->tick();

			//sleeps are self-correcting - if we undersleep 1ms on this tick, we'll sleep an extra ms on the next tick
			$this->tickSleeper->sleepUntil($this->nextTick);
		}
	}

	/**
	 * @return void
	 */
	public function onPlayerLogin(Player $player){
		if($this->sendUsageTicker > 0){
			$this->uniquePlayers[$player->getRawUniqueId()] = $player->getRawUniqueId();
		}

		$this->loggedInPlayers[$player->getRawUniqueId()] = $player;
	}

	/**
	 * @return void
	 */
	public function onPlayerLogout(Player $player){
		unset($this->loggedInPlayers[$player->getRawUniqueId()]);
	}

	/**
	 * @return void
	 */
	public function addPlayer(Player $player){
		$this->players[spl_object_hash($player)] = $player;
	}

	/**
	 * @return void
	 */
	public function removePlayer(Player $player){
		unset($this->players[spl_object_hash($player)]);
	}

	/**
	 * @return void
	 */
	public function addOnlinePlayer(Player $player){
		$this->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getDisplayName(), $player->getSkin(), $player->getXuid());

		$this->playerList[$player->getRawUniqueId()] = $player;
	}

	/**
	 * @return void
	 */
	public function removeOnlinePlayer(Player $player){
		if(isset($this->playerList[$player->getRawUniqueId()])){
			unset($this->playerList[$player->getRawUniqueId()]);

			$this->removePlayerListData($player->getUniqueId());
		}
	}

	/**
	 * @param Player[]|null $players
	 *
	 * @return void
	 */
	public function updatePlayerListData(UUID $uuid, int $entityId, string $name, Skin $skin, string $xboxUserId = "", array $players = null){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;

		$pk->entries[] = PlayerListEntry::createAdditionEntry($uuid, $entityId, $name, SkinAdapterSingleton::get()->toSkinData($skin), $xboxUserId);

		$this->broadcastPacket($players ?? $this->playerList, $pk);
	}

	/**
	 * @param Player[]|null $players
	 *
	 * @return void
	 */
	public function removePlayerListData(UUID $uuid, array $players = null){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries[] = PlayerListEntry::createRemovalEntry($uuid);
		$this->broadcastPacket($players ?? $this->playerList, $pk);
	}

	/**
	 * @return void
	 */
	public function sendFullPlayerListData(Player $p){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		foreach($this->playerList as $player){
			$pk->entries[] = PlayerListEntry::createAdditionEntry($player->getUniqueId(), $player->getId(), $player->getDisplayName(), SkinAdapterSingleton::get()->toSkinData($player->getSkin()), $player->getXuid());
		}

		$p->dataPacket($pk);
	}

	private function checkTickUpdates(int $currentTick, float $tickTime) : void{
		foreach($this->players as $p){
			if(!$p->loggedIn and ($tickTime - $p->creationTime) >= 10){
				$p->close("", "Login timeout");
			}
		}

		//Do level ticks
		foreach($this->levels as $k => $level){
			if(!isset($this->levels[$k])){
				// Level unloaded during the tick of a level earlier in this loop, perhaps by plugin
				continue;
			}

			$levelTime = microtime(true);
			$level->doTick($currentTick);
			$tickMs = (microtime(true) - $levelTime) * 1000;
			$level->tickRateTime = $tickMs;
			if($tickMs >= 50){
				$this->getLogger()->debug(sprintf("World \"%s\" took too long to tick: %gms (%g ticks)", $level->getName(), $tickMs, round($tickMs / 50, 2)));
			}
		}
	}

	/**
	 * @return void
	 */
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

	/**
	 * @param int $type
	 *
	 * @return void
	 */
	public function sendUsage($type = SendUsageTask::TYPE_STATUS){
		if((bool) $this->getProperty("anonymous-statistics.enabled", true)){
			$this->asyncPool->submitTask(new SendUsageTask($this, $type, $this->uniquePlayers));
		}
		$this->uniquePlayers = [];
	}

	/**
	 * @return BaseLang
	 */
	public function getLanguage(){
		return $this->baseLang;
	}

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

	private function titleTick() : void{
		Timings::$titleTickTimer->startTiming();
		$d = Process::getRealMemoryUsage();

		$u = Process::getAdvancedMemoryUsage();
		$usage = sprintf("%g/%g/%g/%g MB @ %d threads", round(($u[0] / 1024) / 1024, 2), round(($d[0] / 1024) / 1024, 2), round(($u[1] / 1024) / 1024, 2), round(($u[2] / 1024) / 1024, 2), Process::getThreadCount());

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
	 * @return void
	 *
	 * TODO: move this to Network
	 */
	public function handlePacket(AdvancedSourceInterface $interface, string $address, int $port, string $payload){
		try{
			if(strlen($payload) > 2 and substr($payload, 0, 2) === "\xfe\xfd" and $this->queryHandler instanceof QueryHandler){
				$this->queryHandler->handle($interface, $address, $port, $payload);
			}else{
				$this->logger->debug("Unhandled raw packet from $address $port: " . base64_encode($payload));
			}
		}catch(\Throwable $e){
			$this->logger->logException($e);

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

		Timings::$connectionTimer->startTiming();
		$this->network->processInterfaces();
		Timings::$connectionTimer->stopTiming();

		Timings::$schedulerTimer->startTiming();
		$this->pluginManager->tickSchedulers($this->tickCounter);
		Timings::$schedulerTimer->stopTiming();

		Timings::$schedulerAsyncTimer->startTiming();
		$this->asyncPool->collectTasks();
		Timings::$schedulerAsyncTimer->stopTiming();

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

			($this->queryRegenerateTask = new QueryRegenerateEvent($this))->call();

			$this->network->updateName();
			$this->network->resetStatistics();
		}

		if($this->autoSave and ++$this->autoSaveTicker >= $this->autoSaveTicks){
			$this->autoSaveTicker = 0;
			$this->getLogger()->debug("[Auto Save] Saving worlds...");
			$start = microtime(true);
			$this->doAutoSave();
			$time = (microtime(true) - $start);
			$this->getLogger()->debug("[Auto Save] Save completed in " . ($time >= 1 ? round($time, 3) . "s" : round($time * 1000) . "ms"));
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

		$idx = $this->tickCounter % 20;
		$this->tickAverage[$idx] = $this->currentTPS;
		$this->useAverage[$idx] = $this->currentUse;

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
