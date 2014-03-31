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

/**
 * PocketMine-MP is the Minecraft: PE multiplayer server software
 * Homepage: http://www.pocketmine.net/
 */
namespace PocketMine;

use PocketMine\Block\Block;
use PocketMine\Command\CommandReader;
use PocketMine\Command\CommandSender;
use PocketMine\Command\ConsoleCommandSender;
use PocketMine\Command\PluginCommand;
use PocketMine\Command\SimpleCommandMap;
use PocketMine\Entity\Entity;
use PocketMine\Event\HandlerList;
use PocketMine\Event\Server\PacketReceiveEvent;
use PocketMine\Event\Server\PacketSendEvent;
use PocketMine\Event\Server\ServerCommandEvent;
use PocketMine\Item\Item;
use PocketMine\Level\Generator\Generator;
use PocketMine\Level\Level;
use PocketMine\Network\Packet;
use PocketMine\Network\RakNet\Info as RakNetInfo;
use PocketMine\Network\RakNet\Packet as RakNetPacket;
use PocketMine\Network\Query\QueryHandler;
use PocketMine\Network\Query\QueryPacket;
use PocketMine\Network\ThreadedHandler;
use PocketMine\Network\UPnP\UPnP;
use PocketMine\Permission\BanList;
use PocketMine\Permission\DefaultPermissions;
use PocketMine\Plugin\Plugin;
use PocketMine\Plugin\PluginLoadOrder;
use PocketMine\Plugin\PluginManager;
use PocketMine\Recipes\Crafting;
use PocketMine\Scheduler\ServerScheduler;
use PocketMine\Scheduler\TickScheduler;
use PocketMine\Tile\Tile;
use PocketMine\Utils\Config;
use PocketMine\Utils\TextFormat;
use PocketMine\Utils\Utils;
use PocketMine\Utils\VersionString;

class Server{
	/** @var Server */
	private static $instance = null;

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

	/** @var PluginManager */
	private $pluginManager = null;

	/** @var ServerScheduler */
	private $scheduler = null;

	/** @var TickScheduler */
	private $tickScheduler = null;

	/** @var CommandReader */
	private $console = null;

	/** @var SimpleCommandMap */
	private $commandMap = null;

	/** @var ConsoleCommandSender */
	private $consoleSender;

	/** @var int */
	private $maxPlayers;

	/**
	 * Counts the ticks since the server start
	 *
	 * @var int
	 */
	private $tickCounter;
	private $inTick = false;

	/** @var ThreadedHandler */
	private $interface;

	private $serverID;

	private $autoloader;
	private $filePath;
	private $dataPath;
	private $pluginPath;

	/** @var QueryHandler */
	private $queryHandler;

	/** @var Config */
	private $properties;

	/**
	 * @return string
	 */
	public function getName(){
		return "PocketMine-MP";
	}

	/**
	 * @return bool
	 */
	public function isRunning(){
		return $this->isRunning === true;
	}

	/**
	 * @return string
	 */
	public function getPocketMineVersion(){
		return \PocketMine\VERSION;
	}

	/**
	 * @return string
	 */
	public function getCodename(){
		return \PocketMine\CODENAME;
	}

	/**
	 * @return string
	 */
	public function getVersion(){
		return \PocketMine\MINECRAFT_VERSION;
	}

	/**
	 * @return string
	 */
	public function getApiVersion(){
		return \PocketMine\API_VERSION;
	}

	/**
	 * @return string
	 */
	public function getFilePath(){
		return $this->filePath;
	}

	/**
	 * @return string
	 */
	public function getDataPath(){
		return $this->dataPath;
	}

	/**
	 * @return string
	 */
	public function getPluginPath(){
		return $this->pluginPath;
	}

	/**
	 * @return int
	 */
	public function getMaxPlayers(){
		return $this->maxPlayers;
	}

	/**
	 * @return int
	 */
	public function getPort(){
		return $this->getConfigInt("server-port", 19132);
	}

	/**
	 * @return int
	 */
	public function getViewDistance(){
		return $this->getConfigInt("view-distance", 8);
	}

	/**
	 * @return string
	 */
	public function getIp(){
		return $this->getConfigString("server-ip", "");
	}

	/**
	 * @return string
	 */
	public function getServerName(){
		return $this->getConfigString("server-name", "Unknown server");
	}

	/**
	 * @return string
	 */
	public function getLevelType(){
		return $this->getConfigString("level-type", "DEFAULT");
	}

	/**
	 * @return bool
	 */
	public function getGenerateStructures(){
		return $this->getConfigBoolean("generate-structures", true);
	}

	/**
	 * @return int
	 */
	public function getGamemode(){
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * Returns the gamemode text name
	 *
	 * @param int $mode
	 *
	 * @return string
	 */
	public static function getGamemodeString($mode){
		switch((int) $mode){
			case Player::SURVIVAL:
				return "SURVIVAL";
			case Player::CREATIVE:
				return "CREATIVE";
			case Player::ADVENTURE:
				return "ADVENTURE";
			case Player::SPECTATOR:
				return "SPECTATOR";
		}

		return "UNKNOWN";
	}

	/**
	 * Parses a string and returns a gamemode integer, -1 if not found
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function getGamemodeFromString($str){
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
	 * @return int
	 */
	public function getDifficulty(){
		return $this->getConfigInt("difficulty", 1);
	}

	/**
	 * @return bool
	 */
	public function hasWhitelist(){
		return $this->getConfigBoolean("white-list", false);
	}

	/**
	 * @return int
	 */
	public function getSpawnRadius(){
		return $this->getConfigInt("spawn-protection", 16);
	}

	/**
	 * @return bool
	 */
	public function getAllowFlight(){
		return $this->getConfigBoolean("allow-flight", false);
	}

	/**
	 * @return bool
	 */
	public function isHardcore(){
		return $this->getConfigBoolean("hardcore", false);
	}

	/**
	 * @return int
	 */
	public function getDefaultGamemode(){
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * @return string
	 */
	public function getMotd(){
		return $this->getConfigString("motd", "Minecraft: PE Server");
	}

	/**
	 * @return \SplClassLoader
	 */
	public function getLoader(){
		return $this->autoloader;
	}

	/**
	 * @return PluginManager
	 */
	public function getPluginManager(){
		return $this->pluginManager;
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
	public function getTick(){
		return $this->tickCounter;
	}

	/**
	 * Returns the last server TPS measure
	 *
	 * @return float
	 */
	public function getTicksPerSecond(){
		return $this->tickScheduler->getTPS();
	}

	/**
	 * @return ThreadedHandler
	 */
	public function getNetwork(){
		return $this->interface;
	}

	/**
	 * @return SimpleCommandMap
	 */
	public function getCommandMap(){
		return $this->commandMap;
	}

	/**
	 * @param string $variable
	 * @param string $defaultValue
	 *
	 * @return string
	 */
	public function getConfigString($variable, $defaultValue = ""){
		$v = getopt("", array("$variable::"));
		if(isset($v[$variable])){
			return (string) $v[$variable];
		}

		return $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param string $value
	 */
	public function setConfigString($variable, $value){
		$this->properties->set($variable, $value);
	}

	/**
	 * @param string $variable
	 * @param int    $defaultValue
	 *
	 * @return int
	 */
	public function getConfigInt($variable, $defaultValue = 0){
		$v = getopt("", array("$variable::"));
		if(isset($v[$variable])){
			return (int) $v[$variable];
		}

		return $this->properties->exists($variable) ? (int) $this->properties->get($variable) : (int) $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param int    $value
	 */
	public function setConfigInt($variable, $value){
		$this->properties->set($variable, (int) $value);
	}

	/**
	 * @param string  $variable
	 * @param boolean $defaultValue
	 *
	 * @return boolean
	 */
	public function getConfigBoolean($variable, $defaultValue = false){
		$v = getopt("", array("$variable::"));
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
	public function setConfigBool($variable, $value){
		$this->properties->set($variable, $value == true ? "1" : "0");
	}

	/**
	 * @param string $name
	 *
	 * @return PluginCommand
	 */
	public function getPluginCommand($name){
		if(($command = $this->commandMap->getCommand($name)) instanceof PluginCommand){
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
	public function addOp($name){
		$this->operators->set(strtolower($name), true);

		if(($player = Player::get($name, false, false)) instanceof Player){
			$player->recalculatePermissions();
		}
	}

	/**
	 * @param string $name
	 */
	public function removeOp($name){
		$this->operators->remove(strtolower($name));

		if(($player = Player::get($name, false, false)) instanceof Player){
			$player->recalculatePermissions();
		}
	}

	/**
	 * @param string $name
	 */
	public function addWhitelist($name){
		$this->whitelist->set(strtolower($name), true);
	}

	/**
	 * @param string $name
	 */
	public function removeWhitelist($name){
		$this->whitelist->remove(strtolower($name));
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isWhitelisted($name){
		return !$this->hasWhitelist() or $this->operators->exists($name, true) or $this->whitelist->exists($name, true);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isOp($name){
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
	public function getOPs(){
		return $this->operators;
	}

	public function reloadWhitelist(){
		$this->whitelist->reload();
	}

	/**
	 * @return Server
	 */
	public static function getInstance(){
		return self::$instance;
	}

	/**
	 * @param \SplClassLoader $autoloader
	 * @param string          $filePath
	 * @param string          $dataPath
	 * @param string          $pluginPath
	 */
	public function __construct(\SplClassLoader $autoloader, $filePath, $dataPath, $pluginPath){
		self::$instance = $this;

		$this->autoloader = $autoloader;
		$this->filePath = $filePath;
		$this->dataPath = $dataPath;
		$this->pluginPath = $pluginPath;
		@mkdir($this->dataPath . "worlds/", 0777);
		@mkdir($this->dataPath . "players/", 0777);
		@mkdir($this->pluginPath, 0777);

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

		$this->tickScheduler = new TickScheduler(20);
		$this->scheduler = new ServerScheduler();
		$this->console = new CommandReader();

		$version = new VersionString($this->getPocketMineVersion());
		console("[INFO] Starting Minecraft: PE server version " . TextFormat::AQUA . $this->getVersion());

		console("[INFO] Loading properties...");
		$this->properties = new Config($this->dataPath . "server.properties", Config::PROPERTIES, array(
			"motd" => "Minecraft: PE Server",
			"server-port" => 19132,
			"memory-limit" => "128M",
			"white-list" => false,
			"announce-player-achievements" => true,
			"spawn-protection" => 16,
			"view-distance" => 8,
			"max-players" => 20,
			"allow-flight" => false,
			"spawn-animals" => true,
			"spawn-mobs" => true,
			"gamemode" => 0,
			"hardcore" => false,
			"pvp" => true,
			"difficulty" => 1,
			"generator-settings" => "",
			"level-name" => "world",
			"level-seed" => "",
			"level-type" => "DEFAULT",
			"enable-query" => true,
			"enable-rcon" => false,
			"rcon.password" => substr(base64_encode(Utils::getRandomBytes(20, false)), 3, 10),
			"auto-save" => true,
		));

		$this->maxPlayers = $this->getConfigInt("max-players", 20);

		if(($memory = str_replace("B", "", strtoupper($this->getConfigString("memory-limit", "128M")))) !== false){
			$value = array("M" => 1, "G" => 1024);
			$real = ((int) substr($memory, 0, -1)) * $value[substr($memory, -1)];
			if($real < 128){
				console("[WARNING] PocketMine-MP may not work right with less than 128MB of RAM", true, true, 0);
			}
			@ini_set("memory_limit", $memory);
		}else{
			$this->setConfigString("memory-limit", "128M");
		}

		if($this->getConfigBoolean("hardcore", false) === true and $this->getDifficulty() < 3){
			$this->setConfigInt("difficulty", 3);
		}

		define("PocketMine\\DEBUG", $this->getConfigInt("debug", 1));
		define("ADVANCED_CACHE", $this->getConfigBoolean("enable-advanced-cache", false));
		define("MAX_CHUNK_RATE", 20 / $this->getConfigInt("max-chunks-per-second", 7)); //Default rate ~448 kB/s
		if(ADVANCED_CACHE == true){
			console("[INFO] Advanced cache enabled");
		}

		if(defined("PocketMine\\DEBUG") and \PocketMine\DEBUG >= 0 and function_exists("cli_set_process_title")){
			@cli_set_process_title("PocketMine-MP " . $this->getPocketMineVersion());
		}

		console("[INFO] Starting Minecraft PE server on " . ($this->getIp() === "" ? "*" : $this->getIp()) . ":" . $this->getPort());
		define("BOOTUP_RANDOM", Utils::getRandomBytes(16));
		$this->serverID = Utils::readLong(substr(Utils::getUniqueID(true, $this->getIp() . $this->getPort()), 8));
		$this->interface = new ThreadedHandler("255.255.255.255", $this->getPort(), $this->getIp() === "" ? "0.0.0.0" : $this->getIp());

		console("[INFO] This server is running PocketMine-MP version " . ($version->isDev() ? TextFormat::YELLOW : "") . $this->getPocketMineVersion() . TextFormat::RESET . " \"" . $this->getCodename() . "\" (API " . $this->getApiVersion() . ")", true, true, 0);
		console("[INFO] PocketMine-MP is distributed under the LGPL License", true, true, 0);

		$this->consoleSender = new ConsoleCommandSender();
		$this->commandMap = new SimpleCommandMap($this);
		$this->pluginManager = new PluginManager($this, $this->commandMap);
		$this->pluginManager->subscribeToPermission(Player::BROADCAST_CHANNEL_ADMINISTRATIVE, $this->consoleSender);
		$this->pluginManager->registerInterface("PocketMine\\Plugin\\FolderPluginLoader");
		$this->pluginManager->loadPlugins($this->pluginPath);

		//TODO: update checking (async)

		$this->enablePlugins(PluginLoadOrder::STARTUP);
		Block::init();
		Item::init();
		Crafting::init();

		Generator::addGenerator("PocketMine\\Level\\Generator\\Flat", "flat");
		Generator::addGenerator("PocketMine\\Level\\Generator\\Normal", "normal");
		Generator::addGenerator("PocketMine\\Level\\Generator\\Normal", "default");
		Level::init();

		$this->properties->save();
		//TODO
		/*if($this->getProperty("send-usage", true) !== false){
			$this->server->schedule(6000, array($this, "sendUsage"), array(), true); //Send the info after 5 minutes have passed
			$this->sendUsage();
		}
		if($this->getProperty("auto-save") === true){
			$this->server->schedule(18000, array($this, "autoSave"), array(), true);
		}
		if(!defined("NO_THREADS") and $this->getProperty("enable-rcon") === true){
			$this->rcon = new RCON($this->getProperty("rcon.password", ""), $this->getProperty("rcon.port", $this->getProperty("server-port")), ($ip = $this->getProperty("server-ip")) != "" ? $ip : "0.0.0.0", $this->getProperty("rcon.threads", 1), $this->getProperty("rcon.clients-per-thread", 50));
		}*/

		//$this->schedule(2, array($this, "checkTickUpdates"), array(), true);

		$this->enablePlugins(PluginLoadOrder::POSTWORLD);
	}

	/**
	 * @param int $type
	 */
	public function enablePlugins($type){
		foreach($this->pluginManager->getPlugins() as $plugin){
			if(!$plugin->isEnabled() and $plugin->getDescription()->getOrder() === $type){
				$this->loadPlugin($plugin);
			}
		}

		if($type === PluginLoadOrder::POSTWORLD){
			$this->commandMap->registerServerAliases();
			$this->loadCustomPermissions();
		}
	}

	private function loadCustomPermissions(){
		DefaultPermissions::registerCorePermissions();
	}

	/**
	 * @param Plugin $plugin
	 */
	public function loadPlugin(Plugin $plugin){
		$this->pluginManager->enablePlugin($plugin);

		foreach($plugin->getDescription()->getPermisions() as $perm){
			$this->pluginManager->addPermission($perm);
		}
	}

	public function disablePlugins(){
		$this->pluginManager->disablePlugins();
	}

	public function checkConsole(){
		if(($line = $this->console->getLine()) !== null){
			$this->pluginManager->callEvent($ev = new ServerCommandEvent($this->consoleSender, $line));
			$this->dispatchCommand($this->consoleSender, $ev->getCommand());
		}
	}

	/**
	 * Executes a command from a CommandSender
	 *
	 * @param CommandSender $sender
	 * @param string        $commandLine
	 *
	 * @return bool
	 */
	public function dispatchCommand(CommandSender $sender, $commandLine){
		if($this->commandMap->dispatch($sender, $commandLine)){
			return true;
		}

		if($sender instanceof Player){
			$sender->sendMessage("Unknown command. Type \"/help\" for help.");
		}else{
			$sender->sendMessage("Unknown command. Type \"help\" for help.");
		}

		return false;
	}

	public function shutdown(){
		$this->isRunning = false;
	}

	/**
	 * Starts the PocketMine-MP server and starts processing ticks and packets
	 */
	public function start(){
		if($this->getConfigBoolean("enable-query", true) === true){
			$this->queryHandler = new QueryHandler();
		}

		if($this->getConfigBoolean("upnp-forwarding", false) == true){
			console("[INFO] [UPnP] Trying to port forward...");
			UPnP::PortForward($this->getPort());
		}

		$this->tickCounter = 0;
		register_tick_function(array($this, "tick"));
		/*
		register_shutdown_function(array($this, "dumpError"));
		register_shutdown_function(array($this, "close"));
		if(function_exists("pcntl_signal")){
			//pcntl_signal(SIGTERM, array($this, "close"));
			pcntl_signal(SIGINT, array($this, "close"));
			pcntl_signal(SIGHUP, array($this, "close"));
		}
		*/
		console("[INFO] Default game type: " . self::getGamemodeString($this->getGamemode())); //TODO: string name
		//$this->trigger("server.start", microtime(true));
		console('[INFO] Done (' . round(microtime(true) - \PocketMine\START_TIME, 3) . 's)! For help, type "help" or "?"');
		if(Utils::getOS() === "win"){ //Workaround less usleep() waste
			$this->tickProcessorWindows();
		}else{
			$this->tickProcessor();
		}

		$this->pluginManager->disablePlugins();

		foreach(Player::getAll() as $player){
			$player->kick("server stop");
		}

		foreach(Level::getAll() as $level){
			$level->unload(true);
		}

		HandlerList::unregisterAll();
		$this->scheduler->cancelAllTasks();
		$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);

		$this->properties->save();

		$this->tickScheduler->kill();
		$this->console->kill();

	}

	private function tickProcessorWindows(){
		$lastLoop = 0;
		while($this->isRunning){
			if(($packet = $this->interface->readPacket()) instanceof Packet){
				$this->pluginManager->callEvent($ev = new PacketReceiveEvent($packet));
				if(!$ev->isCancelled()){
					$this->handlePacket($packet);
				}
				$lastLoop = 0;
			}
			if(($ticks = $this->tick()) !== true){
				++$lastLoop;
				if($lastLoop > 128){
					usleep(1000);
				}
			}else{
				$lastLoop = 0;
			}
		}
	}

	private function tickProcessor(){
		$lastLoop = 0;
		while($this->isRunning){
			if(($packet = $this->interface->readPacket()) instanceof Packet){
				$this->pluginManager->callEvent($ev = new PacketReceiveEvent($packet));
				if(!$ev->isCancelled()){
					$this->handlePacket($packet);
				}
				$lastLoop = 0;
			}
			if(($ticks = $this->tick()) !== true){
				++$lastLoop;
				if($lastLoop > 16 and $lastLoop < 128){
					usleep(200);
				}elseif($lastLoop < 512){
					usleep(400);
				}else{
					usleep(1000);
				}
			}else{
				$lastLoop = 0;
			}
		}
	}

	public function handlePacket(Packet $packet){
		if($packet instanceof QueryPacket and isset($this->queryHandler)){
			$this->queryHandler->handle($packet);
		}elseif($packet instanceof RakNetPacket){
			$CID = $packet->ip . ":" . $packet->port;
			if(isset(Player::$list[$CID])){
				Player::$list[$CID]->handlePacket($packet);
			}else{
				switch($packet->pid()){
					case RakNetInfo::UNCONNECTED_PING:
					case RakNetInfo::UNCONNECTED_PING_OPEN_CONNECTIONS:
						$pk = new RakNetPacket(RakNetInfo::UNCONNECTED_PONG);
						$pk->pingID = $packet->pingID;
						$pk->serverID = $this->serverID;
						$pk->serverType = "MCCPP;Demo;" . $this->getMotd() . " [" . count(Player::$list) . "/" . $this->getMaxPlayers() . "]";
						$pk->ip = $packet->ip;
						$pk->port = $packet->port;
						$this->sendPacket($pk);
						break;
					case RakNetInfo::OPEN_CONNECTION_REQUEST_1:
						if($packet->structure !== RakNetInfo::STRUCTURE){
							console("[DEBUG] Incorrect structure #" . $packet->structure . " from " . $packet->ip . ":" . $packet->port, true, true, 2);
							$pk = new RakNetPacket(RakNetInfo::INCOMPATIBLE_PROTOCOL_VERSION);
							$pk->serverID = $this->serverID;
							$pk->ip = $packet->ip;
							$pk->port = $packet->port;
							$this->sendPacket($pk);
						}else{
							$pk = new RakNetPacket(RakNetInfo::OPEN_CONNECTION_REPLY_1);
							$pk->serverID = $this->serverID;
							$pk->mtuSize = strlen($packet->buffer);
							$pk->ip = $packet->ip;
							$pk->port = $packet->port;
							$this->sendPacket($pk);
						}
						break;
					case RakNetInfo::OPEN_CONNECTION_REQUEST_2:
						new Player($packet->clientID, $packet->ip, $packet->port, $packet->mtuSize); //New Session!
						$pk = new RakNetPacket(RakNetInfo::OPEN_CONNECTION_REPLY_2);
						$pk->serverID = $this->serverID;
						$pk->serverPort = $this->getPort();
						$pk->mtuSize = $packet->mtuSize;
						$pk->ip = $packet->ip;
						$pk->port = $packet->port;
						$this->sendPacket($pk);
						break;
				}
			}
		}
	}

	/**
	 * Sends a packet to the processing queue. Returns the number of bytes
	 *
	 * @param Packet $packet
	 *
	 * @return int
	 */
	public function sendPacket(Packet $packet){
		$this->pluginManager->callEvent($ev = new PacketSendEvent($packet));
		if(!$ev->isCancelled()){
			return $this->interface->writePacket($packet);
		}

		return 0;
	}

	private function checkTickUpdates(){
		//Update entities that need update
		if(count(Entity::$needUpdate) > 0){
			foreach(Entity::$needUpdate as $id => $entity){
				if($entity->onUpdate() === false){
					unset(Entity::$needUpdate[$id]);
				}
			}
		}

		//Update tiles that need update
		if(count(Tile::$needUpdate) > 0){
			foreach(Tile::$needUpdate as $id => $tile){
				if($tile->onUpdate() === false){
					unset(Tile::$needUpdate[$id]);
				}
			}
		}

		//TODO: Add level blocks

		//Do level ticks
		foreach(Level::$list as $level){
			$level->doTick();
		}
	}

	public function autoSave(){
		console("[DEBUG] Saving....", true, true, 2);
		Level::saveAll();
	}

	public function sendUsage(){
		//TODO
		/*console("[DEBUG] Sending usage data...", true, true, 2);
		$plist = "";
		foreach(Server::getInstance()->getPluginManager()->getPlugins() as $p){
			$d = $p->getDescription();
			$plist .= str_replace(array(";", ":"), "", $d->getName()) . ":" . str_replace(array(";", ":"), "", $d->getVersion()) . ";";
		}

		$this->asyncOperation(ASYNC_CURL_POST, array(
			"url" => "http://stats.pocketmine.net/usage.php",
			"data" => array(
				"serverid" => $this->server->serverID,
				"port" => $this->server->port,
				"os" => Utils::getOS(),
				"memory_total" => $this->getProperty("memory-limit"),
				"memory_usage" => memory_get_usage(true),
				"php_version" => PHP_VERSION,
				"version" => VERSION,
				"mc_version" => MINECRAFT_VERSION,
				"protocol" => Info::CURRENT_PROTOCOL,
				"online" => count(Player::$list),
				"max" => $this->server->maxClients,
				"plugins" => $plist,
			),
		), null);*/
	}

	public function titleTick(){
		if(defined("PocketMine\\DEBUG") and \PocketMine\DEBUG >= 0 and \PocketMine\ANSI === true){
			echo "\x1b]0;PocketMine-MP " . $this->getPocketMineVersion() . " | Online " . count(Player::$list) . "/" . $this->getMaxPlayers() . " | RAM " . round((memory_get_usage() / 1024) / 1024, 2) . "/" . round((memory_get_usage(true) / 1024) / 1024, 2) . " MB | U " . round($this->interface->getUploadSpeed() / 1024, 2) . " D " . round($this->interface->getDownloadSpeed() / 1024, 2) . " kB/s | TPS " . $this->getTicksPerSecond() . "\x07";
		}
	}


	/**
	 * Tries to execute a server tick
	 */
	public function tick(){
		if($this->inTick === false and $this->tickScheduler->hasTick()){
			$this->inTick = true; //Fix race conditions
			++$this->tickCounter;

			$this->checkConsole();
			$this->scheduler->mainThreadHeartbeat($this->tickCounter);
			if(($this->tickCounter & 0b1) === 0){
				$this->checkTickUpdates();
				if(($this->tickCounter & 0b1111) === 0){
					$this->titleTick();
					if(isset($this->queryHandler) and ($this->tickCounter & 0b111111111) === 0){
						$this->queryHandler->regenerateInfo();
					}
				}
			}
			$this->tickScheduler->doTick();
			$this->inTick = false;

			return true;
		}

		return false;
	}

}