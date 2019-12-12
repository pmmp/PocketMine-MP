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
use pocketmine\crafting\CraftingManager;
use pocketmine\entity\EntityFactory;
use pocketmine\event\HandlerListManager;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\ItemFactory;
use pocketmine\lang\Language;
use pocketmine\lang\LanguageNotFoundException;
use pocketmine\lang\TextContainer;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\CompressBatchTask;
use pocketmine\network\mcpe\compression\Zlib as ZlibNetworkCompression;
use pocketmine\network\mcpe\encryption\NetworkCipher;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\PacketBatch;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\network\Network;
use pocketmine\network\query\QueryHandler;
use pocketmine\network\upnp\UPnP;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissionManager;
use pocketmine\player\GameMode;
use pocketmine\player\OfflinePlayer;
use pocketmine\player\Player;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginGraylist;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\plugin\PluginManager;
use pocketmine\plugin\ScriptPluginLoader;
use pocketmine\resourcepacks\ResourcePackManager;
use pocketmine\scheduler\AsyncPool;
use pocketmine\scheduler\SendUsageTask;
use pocketmine\snooze\SleeperHandler;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;
use pocketmine\updater\AutoUpdater;
use pocketmine\utils\Config;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Internet;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Process;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;
use pocketmine\world\biome\Biome;
use pocketmine\world\format\io\WorldProviderManager;
use pocketmine\world\format\io\WritableWorldProvider;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\generator\normal\Normal;
use pocketmine\world\World;
use pocketmine\world\WorldManager;
use function array_key_exists;
use function array_shift;
use function array_sum;
use function base64_encode;
use function cli_set_process_title;
use function copy;
use function count;
use function define;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function get_class;
use function getmypid;
use function getopt;
use function implode;
use function ini_get;
use function ini_set;
use function is_a;
use function is_array;
use function is_bool;
use function is_string;
use function json_decode;
use function max;
use function microtime;
use function min;
use function mkdir;
use function ob_end_flush;
use function preg_replace;
use function random_bytes;
use function realpath;
use function register_shutdown_function;
use function rename;
use function round;
use function sleep;
use function spl_object_id;
use function sprintf;
use function str_repeat;
use function str_replace;
use function stripos;
use function strlen;
use function strrpos;
use function strtolower;
use function time;
use function touch;
use function trim;
use function yaml_parse;
use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use const PHP_INT_MAX;
use const PTHREADS_INHERIT_NONE;

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
	/** @var float */
	private $startTime;

	/** @var bool */
	private $doTitleTick = true;

	/** @var int */
	private $sendUsageTicker = 0;

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

	/** @var WorldManager */
	private $worldManager;

	/** @var int */
	private $maxPlayers;

	/** @var bool */
	private $onlineMode = true;

	/** @var Network */
	private $network;
	/** @var bool */
	private $networkCompressionAsync = true;

	/** @var Language */
	private $language;
	/** @var bool */
	private $forceLanguage = false;

	/** @var UUID */
	private $serverID;

	/** @var \DynamicClassLoader */
	private $autoloader;
	/** @var string */
	private $dataPath;
	/** @var string */
	private $pluginPath;

	/** @var string[] */
	private $uniquePlayers = [];

	/** @var QueryRegenerateEvent */
	private $queryRegenerateTask = null;

	/** @var Config */
	private $properties;
	/** @var mixed[] */
	private $propertyCache = [];

	/** @var Config */
	private $config;

	/** @var Player[] */
	private $playerList = [];

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
	 * @return GameMode
	 */
	public function getGamemode() : GameMode{
		return GameMode::fromMagicNumber($this->getConfigInt("gamemode", 0) & 0b11);
	}

	/**
	 * @return bool
	 */
	public function getForceGamemode() : bool{
		return $this->getConfigBool("force-gamemode", false);
	}

	/**
	 * Returns Server global difficulty. Note that this may be overridden in individual worlds.
	 * @return int
	 */
	public function getDifficulty() : int{
		return $this->getConfigInt("difficulty", World::DIFFICULTY_NORMAL);
	}

	/**
	 * @return bool
	 */
	public function hasWhitelist() : bool{
		return $this->getConfigBool("white-list", false);
	}

	/**
	 * @return bool
	 */
	public function isHardcore() : bool{
		return $this->getConfigBool("hardcore", false);
	}

	/**
	 * @return string
	 */
	public function getMotd() : string{
		return $this->getConfigString("motd", \pocketmine\NAME . " Server");
	}

	/**
	 * @return \DynamicClassLoader
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
	 * @return WorldManager
	 */
	public function getWorldManager() : WorldManager{
		return $this->worldManager;
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
	 * @return float
	 */
	public function getStartTime() : float{
		return $this->startTime;
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
		$name = strtolower($name);
		return file_exists($this->getDataPath() . "players/$name.dat");
	}

	/**
	 * @param string $name
	 *
	 * @return CompoundTag|null
	 */
	public function getOfflinePlayerData(string $name) : ?CompoundTag{
		$name = strtolower($name);
		$path = $this->getDataPath() . "players/";

		if(file_exists($path . "$name.dat")){
			try{
				return (new BigEndianNbtSerializer())->readCompressed(file_get_contents($path . "$name.dat"))->mustGetCompoundTag();
			}catch(NbtDataException $e){ //zlib decode error / corrupt data
				rename($path . "$name.dat", $path . "$name.dat.bak");
				$this->logger->error($this->getLanguage()->translateString("pocketmine.data.playerCorrupted", [$name]));
			}
		}
		return null;
	}

	/**
	 * @param string      $name
	 * @param CompoundTag $nbtTag
	 */
	public function saveOfflinePlayerData(string $name, CompoundTag $nbtTag) : void{
		$ev = new PlayerDataSaveEvent($nbtTag, $name);
		$ev->setCancelled(!$this->shouldSavePlayerData());

		$ev->call();

		if(!$ev->isCancelled()){
			$nbt = new BigEndianNbtSerializer();
			try{
				file_put_contents($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed(new TreeRoot($ev->getSaveData())));
			}catch(\ErrorException $e){
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
	 * @param string $name
	 *
	 * @return Player|null
	 */
	public function getPlayer(string $name) : ?Player{
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
	 * @param string $name
	 *
	 * @return Player|null
	 */
	public function getPlayerExact(string $name) : ?Player{
		$name = strtolower($name);
		foreach($this->getOnlinePlayers() as $player){
			if(strtolower($player->getName()) === $name){
				return $player;
			}
		}

		return null;
	}

	/**
	 * Returns a list of online players whose names contain with the given string (case insensitive).
	 * If an exact match is found, only that match is returned.
	 *
	 * @param string $partialName
	 *
	 * @return Player[]
	 */
	public function matchPlayer(string $partialName) : array{
		$partialName = strtolower($partialName);
		$matchedPlayers = [];
		foreach($this->getOnlinePlayers() as $player){
			if(strtolower($player->getName()) === $partialName){
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
	public function setConfigString(string $variable, string $value) : void{
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
	public function setConfigInt(string $variable, int $value) : void{
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
	public function setConfigBool(string $variable, bool $value) : void{
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
	public function addOp(string $name) : void{
		$this->operators->set(strtolower($name), true);

		if(($player = $this->getPlayerExact($name)) !== null){
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @param string $name
	 */
	public function removeOp(string $name) : void{
		$this->operators->remove(strtolower($name));

		if(($player = $this->getPlayerExact($name)) !== null){
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @param string $name
	 */
	public function addWhitelist(string $name) : void{
		$this->whitelist->set(strtolower($name), true);
		$this->whitelist->save();
	}

	/**
	 * @param string $name
	 */
	public function removeWhitelist(string $name) : void{
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
	 * @param \DynamicClassLoader       $autoloader
	 * @param \AttachableThreadedLogger $logger
	 * @param string                    $dataPath
	 * @param string                    $pluginPath
	 */
	public function __construct(\DynamicClassLoader $autoloader, \AttachableThreadedLogger $logger, string $dataPath, string $pluginPath){
		if(self::$instance !== null){
			throw new \InvalidStateException("Only one server instance can exist at once");
		}
		self::$instance = $this;
		$this->startTime = microtime(true);

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
				"max-players" => 20,
				"gamemode" => 0,
				"force-gamemode" => false,
				"hardcore" => false,
				"pvp" => true,
				"difficulty" => World::DIFFICULTY_NORMAL,
				"generator-settings" => "",
				"level-name" => "world",
				"level-seed" => "",
				"level-type" => "DEFAULT",
				"enable-query" => true,
				"auto-save" => true,
				"view-distance" => 8,
				"xbox-auth" => true,
				"language" => "eng"
			]);

			$debugLogLevel = (int) $this->getProperty("debug.level", 1);
			if($this->logger instanceof MainLogger){
				$this->logger->setLogDebug($debugLogLevel > 1);
			}

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
				ZlibNetworkCompression::$THRESHOLD = (int) $this->getProperty("network.batch-threshold", 256);
			}else{
				ZlibNetworkCompression::$THRESHOLD = -1;
			}

			ZlibNetworkCompression::$LEVEL = $this->getProperty("network.compression-level", 7);
			if(ZlibNetworkCompression::$LEVEL < 1 or ZlibNetworkCompression::$LEVEL > 9){
				$this->logger->warning("Invalid network compression level " . ZlibNetworkCompression::$LEVEL . " set, setting to default 7");
				ZlibNetworkCompression::$LEVEL = 7;
			}
			$this->networkCompressionAsync = (bool) $this->getProperty("network.async-compression", true);

			NetworkCipher::$ENABLED = (bool) $this->getProperty("network.enable-encryption", true);

			$this->doTitleTick = ((bool) $this->getProperty("console.title-tick", true)) && Terminal::hasFormattingCodes();

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

			$this->onlineMode = $this->getConfigBool("xbox-auth", true);
			if($this->onlineMode){
				$this->logger->notice($this->getLanguage()->translateString("pocketmine.server.auth.enabled"));
				$this->logger->notice($this->getLanguage()->translateString("pocketmine.server.authProperty.enabled"));
			}else{
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.auth.disabled"));
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.authWarning"));
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.authProperty.disabled"));
			}

			if($this->getConfigBool("hardcore", false) and $this->getDifficulty() < World::DIFFICULTY_HARD){
				$this->setConfigInt("difficulty", World::DIFFICULTY_HARD);
			}

			@cli_set_process_title($this->getName() . " " . $this->getPocketMineVersion());

			define("BOOTUP_RANDOM", random_bytes(16));
			$this->serverID = Utils::getMachineUniqueId($this->getIp() . $this->getPort());

			$this->getLogger()->debug("Server unique id: " . $this->getServerUniqueId());
			$this->getLogger()->debug("Machine unique id: " . Utils::getMachineUniqueId());

			$this->network = new Network($this->logger);
			$this->network->setName($this->getMotd());

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.info", [
				$this->getName(),
				(\pocketmine\IS_DEVELOPMENT_BUILD ? TextFormat::YELLOW : "") . $this->getPocketMineVersion() . TextFormat::RESET
			]));
			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.license", [$this->getName()]));

			Timings::init();
			TimingsHandler::setEnabled((bool) $this->getProperty("settings.enable-profiling", false));
			$this->profilingTickRate = (float) $this->getProperty("settings.profile-report-trigger", 20);

			$this->commandMap = new SimpleCommandMap($this);

			EntityFactory::init();
			BlockFactory::init();
			Enchantment::init();
			ItemFactory::init();
			CreativeInventory::init();
			Biome::init();

			$this->craftingManager = new CraftingManager();

			$this->resourceManager = new ResourcePackManager($this->getDataPath() . "resource_packs" . DIRECTORY_SEPARATOR, $this->logger);

			$pluginGraylist = null;
			$graylistFile = $this->dataPath . "plugin_list.yml";
			if(!file_exists($graylistFile)){
				copy(\pocketmine\RESOURCE_PATH . 'plugin_list.yml', $graylistFile);
			}
			try{
				$pluginGraylist = PluginGraylist::fromArray(yaml_parse(file_get_contents($graylistFile)));
			}catch(\InvalidArgumentException $e){
				$this->logger->emergency("Failed to load $graylistFile: " . $e->getMessage());
				$this->forceShutdown();
				return;
			}
			$this->pluginManager = new PluginManager($this, ((bool) $this->getProperty("plugins.legacy-data-dir", true)) ? null : $this->getDataPath() . "plugin_data" . DIRECTORY_SEPARATOR, $pluginGraylist);
			$this->pluginManager->registerInterface(new PharPluginLoader($this->autoloader));
			$this->pluginManager->registerInterface(new ScriptPluginLoader());

			WorldProviderManager::init();
			if(
				($format = WorldProviderManager::getProviderByName($formatName = (string) $this->getProperty("level-settings.default-format"))) !== null and
				is_a($format, WritableWorldProvider::class, true)
			){
				WorldProviderManager::setDefault($format);
			}elseif($formatName !== ""){
				$this->logger->warning($this->language->translateString("pocketmine.level.badDefaultFormat", [$formatName]));
			}

			GeneratorManager::registerDefaultGenerators();
			$this->worldManager = new WorldManager($this);

			$this->updater = new AutoUpdater($this, $this->getProperty("auto-updater.host", "update.pmmp.io"));

			$this->queryRegenerateTask = new QueryRegenerateEvent($this);

			register_shutdown_function([$this, "crashDump"]);

			$this->pluginManager->loadPlugins($this->pluginPath);
			$this->enablePlugins(PluginLoadOrder::STARTUP());

			foreach((array) $this->getProperty("worlds", []) as $name => $options){
				if($options === null){
					$options = [];
				}elseif(!is_array($options)){
					continue;
				}
				if(!$this->worldManager->loadWorld($name, true)){
					if(isset($options["generator"])){
						$generatorOptions = explode(":", $options["generator"]);
						$generator = GeneratorManager::getGenerator(array_shift($generatorOptions));
						if(count($options) > 0){
							$options["preset"] = implode(":", $generatorOptions);
						}
					}else{
						$generator = Normal::class;
					}

					$this->worldManager->generateWorld($name, Generator::convertSeed((string) ($options["seed"] ?? "")), $generator, $options);
				}
			}

			if($this->worldManager->getDefaultWorld() === null){
				$default = $this->getConfigString("level-name", "world");
				if(trim($default) == ""){
					$this->getLogger()->warning("level-name cannot be null, using default");
					$default = "world";
					$this->setConfigString("level-name", "world");
				}
				if(!$this->worldManager->loadWorld($default, true)){
					$this->worldManager->generateWorld(
						$default,
						Generator::convertSeed($this->getConfigString("level-seed")),
						GeneratorManager::getGenerator($this->getConfigString("level-type")),
						["preset" => $this->getConfigString("generator-settings")]
					);
				}

				$world = $this->worldManager->getWorldByName($default);
				if($world === null){
					$this->getLogger()->emergency($this->getLanguage()->translateString("pocketmine.level.defaultError"));
					$this->forceShutdown();

					return;
				}
				$this->worldManager->setDefaultWorld($world);
			}

			$this->enablePlugins(PluginLoadOrder::POSTWORLD());

			$this->network->registerInterface(new RakLibInterface($this));
			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.networkStart", [$this->getIp(), $this->getPort()]));

			if($this->getConfigBool("enable-query", true)){
				$this->network->registerRawPacketHandler(new QueryHandler());
			}

			foreach($this->getIPBans()->getEntries() as $entry){
				$this->network->blockAddress($entry->getName(), -1);
			}

			if($this->getProperty("network.upnp-forwarding", false)){
				try{
					$this->network->registerInterface(new UPnP($this->logger, Internet::getInternalIP(), $this->getPort()));
				}catch(\RuntimeException $e){
					$this->logger->alert("UPnP portforward failed: " . $e->getMessage());
				}
			}

			if($this->getProperty("settings.send-usage", true)){
				$this->sendUsageTicker = 6000;
				$this->sendUsage(SendUsageTask::TYPE_OPEN);
			}

			if($this->properties->hasChanged()){
				$this->properties->save();
			}

			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.defaultGameMode", [$this->getGamemode()->getTranslationKey()]));
			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.donate", [TextFormat::AQUA . "https://patreon.com/pocketminemp" . TextFormat::RESET]));
			$this->logger->info($this->getLanguage()->translateString("pocketmine.server.startFinished", [round(microtime(true) - $this->startTime, 3)]));

			//TODO: move console parts to a separate component
			$consoleSender = new ConsoleCommandSender();
			PermissionManager::getInstance()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $consoleSender);
			PermissionManager::getInstance()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $consoleSender);

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

			$this->tickProcessor();
			$this->forceShutdown();
		}catch(\Throwable $e){
			$this->exceptionHandler($e);
		}
	}

	/**
	 * @param TextContainer|string $message
	 * @param CommandSender[]      $recipients
	 *
	 * @return int
	 */
	public function broadcastMessage($message, ?array $recipients = null) : int{
		if(!is_array($recipients)){
			return $this->broadcast($message, self::BROADCAST_CHANNEL_USERS);
		}

		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	private function selectPermittedPlayers(string $permission) : array{
		/** @var Player[] $players */
		$players = [];
		foreach(PermissionManager::getInstance()->getPermissionSubscriptions($permission) as $permissible){
			if($permissible instanceof Player and $permissible->hasPermission($permission)){
				$players[spl_object_id($permissible)] = $permissible; //prevent duplication
			}
		}
		return $players;
	}

	/**
	 * @param string   $tip
	 * @param Player[] $recipients
	 *
	 * @return int
	 */
	public function broadcastTip(string $tip, ?array $recipients = null) : int{
		$recipients = $recipients ?? $this->selectPermittedPlayers(self::BROADCAST_CHANNEL_USERS);

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
	public function broadcastPopup(string $popup, ?array $recipients = null) : int{
		$recipients = $recipients ?? $this->selectPermittedPlayers(self::BROADCAST_CHANNEL_USERS);

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
	public function broadcastTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1, ?array $recipients = null) : int{
		$recipients = $recipients ?? $this->selectPermittedPlayers(self::BROADCAST_CHANNEL_USERS);

		foreach($recipients as $recipient){
			$recipient->sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
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
					$recipients[spl_object_id($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * @param Player[]            $players
	 * @param ClientboundPacket[] $packets
	 *
	 * @return bool
	 */
	public function broadcastPackets(array $players, array $packets) : bool{
		if(empty($packets)){
			throw new \InvalidArgumentException("Cannot broadcast empty list of packets");
		}

		/** @var NetworkSession[] $recipients */
		$recipients = [];
		foreach($players as $player){
			if($player->isConnected()){
				$recipients[] = $player->getNetworkSession();
			}
		}
		if(empty($recipients)){
			return false;
		}

		$ev = new DataPacketSendEvent($recipients, $packets);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$recipients = $ev->getTargets();

		$stream = PacketBatch::fromPackets(...$ev->getPackets());

		if(ZlibNetworkCompression::$THRESHOLD < 0 or strlen($stream->getBuffer()) < ZlibNetworkCompression::$THRESHOLD){
			foreach($recipients as $target){
				foreach($ev->getPackets() as $pk){
					$target->addToSendBuffer($pk);
				}
			}
		}else{
			$promise = $this->prepareBatch($stream);
			foreach($recipients as $target){
				$target->queueCompressed($promise);
			}
		}

		return true;
	}

	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param PacketBatch $stream
	 * @param bool        $forceSync
	 *
	 * @return CompressBatchPromise
	 */
	public function prepareBatch(PacketBatch $stream, bool $forceSync = false) : CompressBatchPromise{
		try{
			Timings::$playerNetworkSendCompressTimer->startTiming();

			$compressionLevel = ZlibNetworkCompression::$LEVEL;
			$buffer = $stream->getBuffer();
			if(ZlibNetworkCompression::$THRESHOLD < 0 or strlen($buffer) < ZlibNetworkCompression::$THRESHOLD){
				$compressionLevel = 0; //Do not compress packets under the threshold
				$forceSync = true;
			}

			$promise = new CompressBatchPromise();
			if(!$forceSync and $this->networkCompressionAsync){
				$task = new CompressBatchTask($buffer, $compressionLevel, $promise);
				$this->asyncPool->submitTask($task);
			}else{
				$promise->resolve(ZlibNetworkCompression::compress($buffer, $compressionLevel));
			}

			return $promise;
		}finally{
			Timings::$playerNetworkSendCompressTimer->stopTiming();
		}
	}

	/**
	 * @param PluginLoadOrder $type
	 */
	public function enablePlugins(PluginLoadOrder $type) : void{
		foreach($this->pluginManager->getPlugins() as $plugin){
			if(!$plugin->isEnabled() and $plugin->getDescription()->getOrder()->equals($type)){
				$this->pluginManager->enablePlugin($plugin);
			}
		}

		if($type->equals(PluginLoadOrder::POSTWORLD())){
			$this->commandMap->registerServerAliases();
			DefaultPermissions::registerCorePermissions();
		}
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

	/**
	 * Shuts the server down correctly
	 */
	public function shutdown() : void{
		$this->isRunning = false;
	}

	public function forceShutdown() : void{
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

			if($this->pluginManager instanceof PluginManager){
				$this->getLogger()->debug("Disabling all plugins");
				$this->pluginManager->disablePlugins();
			}

			if($this->network instanceof Network){
				$this->network->getSessionManager()->close($this->getProperty("settings.shutdown-message", "Server closed"));
			}

			if($this->worldManager instanceof WorldManager){
				$this->getLogger()->debug("Unloading all worlds");
				foreach($this->worldManager->getWorlds() as $world){
					$this->worldManager->unloadWorld($world, true);
				}
			}

			$this->getLogger()->debug("Removing event handlers");
			HandlerListManager::global()->unregisterAll();

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
					$this->network->unregisterInterface($interface);
				}
			}
		}catch(\Throwable $e){
			$this->logger->logException($e);
			$this->logger->emergency("Crashed while crashing, killing process");
			@Process::kill(getmypid());
		}

	}

	/**
	 * @return QueryRegenerateEvent
	 */
	public function getQueryInformation(){
		return $this->queryRegenerateTask;
	}

	/**
	 * @param \Throwable $e
	 * @param array|null $trace
	 */
	public function exceptionHandler(\Throwable $e, $trace = null) : void{
		while(@ob_end_flush()){}
		global $lastError;

		if($trace === null){
			$trace = $e->getTrace();
		}

		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errline = $e->getLine();

		$errstr = preg_replace('/\s+/', ' ', trim($errstr));

		$errfile = Filesystem::cleanPath($errfile);

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

	public function crashDump() : void{
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
		$spacing = ((int) $this->startTime) - time() + 120;
		if($spacing > 0){
			echo "--- Waiting $spacing seconds to throttle automatic restart (you can kill the process safely now) ---" . PHP_EOL;
			sleep($spacing);
		}
		@Process::kill(getmypid());
		exit(1);
	}

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

	public function addOnlinePlayer(Player $player) : void{
		foreach($this->playerList as $p){
			$p->getNetworkSession()->onPlayerAdded($player);
		}
		$rawUUID = $player->getUniqueId()->toBinary();
		$this->playerList[$rawUUID] = $player;

		if($this->sendUsageTicker > 0){
			$this->uniquePlayers[$rawUUID] = $rawUUID;
		}
	}

	public function removeOnlinePlayer(Player $player) : void{
		if(isset($this->playerList[$rawUUID = $player->getUniqueId()->toBinary()])){
			unset($this->playerList[$rawUUID]);
			foreach($this->playerList as $p){
				$p->getNetworkSession()->onPlayerRemoved($player);
			}
		}
	}

	public function sendUsage(int $type = SendUsageTask::TYPE_STATUS) : void{
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

	private function titleTick() : void{
		Timings::$titleTickTimer->startTiming();
		$d = Process::getRealMemoryUsage();

		$u = Process::getMemoryUsage(true);
		$usage = sprintf("%g/%g/%g/%g MB @ %d threads", round(($u[0] / 1024) / 1024, 2), round(($d[0] / 1024) / 1024, 2), round(($u[1] / 1024) / 1024, 2), round(($u[2] / 1024) / 1024, 2), Process::getThreadCount());

		$online = count($this->playerList);
		$connecting = $this->network->getConnectionCount() - $online;

		echo "\x1b]0;" . $this->getName() . " " .
			$this->getPocketMineVersion() .
			" | Online $online/" . $this->getMaxPlayers() .
			($connecting > 0 ? " (+$connecting connecting)" : "") .
			" | Memory " . $usage .
			" | U " . round($this->network->getUpload() / 1024, 2) .
			" D " . round($this->network->getDownload() / 1024, 2) .
			" kB/s | TPS " . $this->getTicksPerSecondAverage() .
			" | Load " . $this->getTickUsageAverage() . "%\x07";

		Timings::$titleTickTimer->stopTiming();
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

		$this->worldManager->tick($this->tickCounter);

		Timings::$connectionTimer->startTiming();
		$this->network->tick();
		Timings::$connectionTimer->stopTiming();

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

		if($this->sendUsageTicker > 0 and --$this->sendUsageTicker === 0){
			$this->sendUsageTicker = 6000;
			$this->sendUsage(SendUsageTask::TYPE_STATUS);
		}

		if(($this->tickCounter % 100) === 0){
			foreach($this->worldManager->getWorlds() as $world){
				$world->clearCache();
			}

			if($this->getTicksPerSecondAverage() < 12){
				$this->logger->warning($this->getLanguage()->translateString("pocketmine.server.tickOverload"));
			}
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
