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

namespace pocketmine\plugin;

use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\event\Listener;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\event\RegisteredListener;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\permission\PermissionManager;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use SOFe\Pathetique\Path;
use function array_intersect;
use function array_merge;
use function class_exists;
use function count;
use function dirname;
use function file_exists;
use function get_class;
use function implode;
use function in_array;
use function is_a;
use function is_array;
use function is_dir;
use function is_string;
use function is_subclass_of;
use function iterator_to_array;
use function mkdir;
use function shuffle;
use function stripos;
use function strpos;
use function strtolower;
use const DIRECTORY_SEPARATOR;

/**
 * Manages all the plugins
 */
class PluginManager{

	/** @var Server */
	private $server;

	/** @var Plugin[] */
	protected $plugins = [];

	/** @var Plugin[] */
	protected $enabledPlugins = [];

	/**
	 * @var PluginLoader[]
	 * @phpstan-var array<class-string<PluginLoader>, PluginLoader>
	 */
	protected $fileAssociations = [];

	/** @var string|null */
	private $pluginDataDirectory;
	/** @var PluginGraylist|null */
	private $graylist;

	public function __construct(Server $server, ?Path $pluginDataDirectory, ?PluginGraylist $graylist = null){
		$this->server = $server;
		$this->pluginDataDirectory = $pluginDataDirectory;
		if($this->pluginDataDirectory !== null){
			$this->pluginDataDirectory->mkdir(true);
		}

		$this->graylist = $graylist;
	}

	public function getPlugin(string $name) : ?Plugin{
		if(isset($this->plugins[$name])){
			return $this->plugins[$name];
		}

		return null;
	}

	public function registerInterface(PluginLoader $loader) : void{
		$this->fileAssociations[get_class($loader)] = $loader;
	}

	/**
	 * @return Plugin[]
	 */
	public function getPlugins() : array{
		return $this->plugins;
	}

	private function getDataDirectory(Path $pluginPath, string $pluginName) : Path{
		if($this->pluginDataDirectory !== null){
			return $this->pluginDataDirectory->join($pluginName);
		}
		return $pluginPath->join("..")->join($pluginName);
	}

	/**
	 * @param PluginLoader[] $loaders
	 */
	public function loadPlugin(Path $path, ?array $loaders = null) : ?Plugin{
		foreach($loaders ?? $this->fileAssociations as $loader){
			if($loader->canLoadPlugin($path)){
				$description = $loader->getPluginDescription($path);
				if($description instanceof PluginDescription){
					$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.load", [$description->getFullName()]));
					try{
						$description->checkRequiredExtensions();
					}catch(PluginException $ex){
						$this->server->getLogger()->error($ex->getMessage());
						return null;
					}

					$dataFolder = $this->getDataDirectory($path, $description->getName());
					if(file_exists($dataFolder) and !is_dir($dataFolder)){
						$this->server->getLogger()->error("Projected dataFolder '" . $dataFolder . "' for " . $description->getName() . " exists and is not a directory");
						return null;
					}
					if(!file_exists($dataFolder)){
						mkdir($dataFolder, 0777, true);
					}

					$prefixed = $path->setProtocol($loader->getAccessProtocol());
					$loader->loadPlugin($prefixed);

					$mainClass = $description->getMain();
					if(!class_exists($mainClass, true)){
						$this->server->getLogger()->error("Main class for plugin " . $description->getName() . " not found");
						return null;
					}
					if(!is_a($mainClass, Plugin::class, true)){
						$this->server->getLogger()->error("Main class for plugin " . $description->getName() . " is not an instance of " . Plugin::class);
						return null;
					}

					$permManager = PermissionManager::getInstance();
					foreach($description->getPermissions() as $perm){
						$permManager->addPermission($perm);
					}

					/**
					 * @var Plugin $plugin
					 * @see Plugin::__construct()
					 */
					$plugin = new $mainClass($loader, $this->server, $description, $dataFolder, $prefixed, new DiskResourceProvider($prefixed->join("resources")));
					$this->plugins[$plugin->getDescription()->getName()] = $plugin;

					return $plugin;
				}
			}
		}

		return null;
	}

	/**
	 * @param string[]|null $newLoaders
	 * @phpstan-param list<class-string<PluginLoader>> $newLoaders
	 *
	 * @return Plugin[]
	 */
	public function loadPlugins(Path $directory, ?array $newLoaders = null) : array{
		if(!$directory->isDir()){
			return [];
		}

		$plugins = [];
		$loadedPlugins = [];
		$dependencies = [];
		$softDependencies = [];
		if(is_array($newLoaders)){
			$loaders = [];
			foreach($newLoaders as $key){
				if(isset($this->fileAssociations[$key])){
					$loaders[$key] = $this->fileAssociations[$key];
				}
			}
		}else{
			$loaders = $this->fileAssociations;
		}

		$files = [];
		foreach($directory->scan() as $file) {
			$files[] = $file;
		}
		shuffle($files); //this prevents plugins implicitly relying on the filesystem name order when they should be using dependency properties
		foreach($loaders as $loader){
			foreach($files as $file){
				if(!$loader->canLoadPlugin($file)){
					continue;
				}
				try{
					$description = $loader->getPluginDescription($file);
				}catch(\RuntimeException $e){ //TODO: more specific exception handling
					$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.fileError", [$file->displayUtf8(), $directory->displayUtf8(), $e->getMessage()]));
					$this->server->getLogger()->logException($e);
					continue;
				}
				if($description === null){
					continue;
				}

				$name = $description->getName();
				if(stripos($name, "pocketmine") !== false or stripos($name, "minecraft") !== false or stripos($name, "mojang") !== false){
					$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.restrictedName"]));
					continue;
				}
				if(strpos($name, " ") !== false){
					$this->server->getLogger()->warning($this->server->getLanguage()->translateString("pocketmine.plugin.spacesDiscouraged", [$name]));
				}

				if(isset($plugins[$name]) or $this->getPlugin($name) instanceof Plugin){
					$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.duplicateError", [$name]));
					continue;
				}

				if(!ApiVersion::isCompatible($this->server->getApiVersion(), $description->getCompatibleApis())){
					$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
						$name,
						$this->server->getLanguage()->translateString("%pocketmine.plugin.incompatibleAPI", [implode(", ", $description->getCompatibleApis())])
					]));
					continue;
				}
				$ambiguousVersions = ApiVersion::checkAmbiguousVersions($description->getCompatibleApis());
				if(count($ambiguousVersions) > 0){
					$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
						$name,
						$this->server->getLanguage()->translateString("pocketmine.plugin.ambiguousMinAPI", [implode(", ", $ambiguousVersions)])
					]));
					continue;
				}

				if(count($description->getCompatibleOperatingSystems()) > 0 and !in_array(Utils::getOS(), $description->getCompatibleOperatingSystems(), true)) {
					$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
						$name,
						$this->server->getLanguage()->translateString("%pocketmine.plugin.incompatibleOS", [implode(", ", $description->getCompatibleOperatingSystems())])
					]));
					continue;
				}

				if(count($pluginMcpeProtocols = $description->getCompatibleMcpeProtocols()) > 0){
					$serverMcpeProtocols = [ProtocolInfo::CURRENT_PROTOCOL];
					if(count(array_intersect($pluginMcpeProtocols, $serverMcpeProtocols)) === 0){
						$this->server->getLogger()->error($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
							$name,
							$this->server->getLanguage()->translateString("%pocketmine.plugin.incompatibleProtocol", [implode(", ", $pluginMcpeProtocols)])
						]));
						continue;
					}
				}

				if($this->graylist !== null and !$this->graylist->isAllowed($name)){
					$this->server->getLogger()->notice($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
						$name,
						"Disallowed by graylist"
					]));
					continue;
				}
				$plugins[$name] = $file;

				$softDependencies[$name] = array_merge($softDependencies[$name] ?? [], $description->getSoftDepend());
				$dependencies[$name] = $description->getDepend();

				foreach($description->getLoadBefore() as $before){
					if(isset($softDependencies[$before])){
						$softDependencies[$before][] = $name;
					}else{
						$softDependencies[$before] = [$name];
					}
				}
			}
		}

		while(count($plugins) > 0){
			$loadedThisLoop = 0;
			foreach($plugins as $name => $file){
				if(isset($dependencies[$name])){
					foreach($dependencies[$name] as $key => $dependency){
						if(isset($loadedPlugins[$dependency]) or $this->getPlugin($dependency) instanceof Plugin){
							unset($dependencies[$name][$key]);
						}elseif(!isset($plugins[$dependency])){
							$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [
								$name,
								$this->server->getLanguage()->translateString("%pocketmine.plugin.unknownDependency", [$dependency])
							]));
							unset($plugins[$name]);
							continue 2;
						}
					}

					if(count($dependencies[$name]) === 0){
						unset($dependencies[$name]);
					}
				}

				if(isset($softDependencies[$name])){
					foreach($softDependencies[$name] as $key => $dependency){
						if(isset($loadedPlugins[$dependency]) or $this->getPlugin($dependency) instanceof Plugin){
							$this->server->getLogger()->debug("Successfully resolved soft dependency \"$dependency\" for plugin \"$name\"");
							unset($softDependencies[$name][$key]);
						}elseif(!isset($plugins[$dependency])){
							//this dependency is never going to be resolved, so don't bother trying
							$this->server->getLogger()->debug("Skipping resolution of missing soft dependency \"$dependency\" for plugin \"$name\"");
							unset($softDependencies[$name][$key]);
						}else{
							$this->server->getLogger()->debug("Deferring resolution of soft dependency \"$dependency\" for plugin \"$name\" (found but not loaded yet)");
						}
					}

					if(count($softDependencies[$name]) === 0){
						unset($softDependencies[$name]);
					}
				}

				if(!isset($dependencies[$name]) and !isset($softDependencies[$name])){
					unset($plugins[$name]);
					$loadedThisLoop++;
					if(($plugin = $this->loadPlugin($file, $loaders)) instanceof Plugin){
						$loadedPlugins[$name] = $plugin;
					}else{
						$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.genericLoadError", [$name]));
					}
				}
			}

			if($loadedThisLoop === 0){
				//No plugins loaded :(
				foreach($plugins as $name => $file){
					$this->server->getLogger()->critical($this->server->getLanguage()->translateString("pocketmine.plugin.loadError", [$name, "%pocketmine.plugin.circularDependency"]));
				}
				$plugins = [];
			}
		}

		return $loadedPlugins;
	}

	public function isPluginEnabled(Plugin $plugin) : bool{
		return isset($this->plugins[$plugin->getDescription()->getName()]) and $plugin->isEnabled();
	}

	public function enablePlugin(Plugin $plugin) : void{
		if(!$plugin->isEnabled()){
			$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.enable", [$plugin->getDescription()->getFullName()]));

			$plugin->getScheduler()->setEnabled(true);
			$plugin->onEnableStateChange(true);

			$this->enabledPlugins[$plugin->getDescription()->getName()] = $plugin;

			(new PluginEnableEvent($plugin))->call();
		}
	}

	public function disablePlugins() : void{
		foreach($this->getPlugins() as $plugin){
			$this->disablePlugin($plugin);
		}
	}

	public function disablePlugin(Plugin $plugin) : void{
		if($plugin->isEnabled()){
			$this->server->getLogger()->info($this->server->getLanguage()->translateString("pocketmine.plugin.disable", [$plugin->getDescription()->getFullName()]));
			(new PluginDisableEvent($plugin))->call();

			unset($this->enabledPlugins[$plugin->getDescription()->getName()]);

			$plugin->onEnableStateChange(false);
			$plugin->getScheduler()->shutdown();
			HandlerListManager::global()->unregisterAll($plugin);
		}
	}

	public function tickSchedulers(int $currentTick) : void{
		foreach($this->enabledPlugins as $p){
			$p->getScheduler()->mainThreadHeartbeat($currentTick);
		}
	}

	public function clearPlugins() : void{
		$this->disablePlugins();
		$this->plugins = [];
		$this->enabledPlugins = [];
		$this->fileAssociations = [];
	}

	/**
	 * Registers all the events in the given Listener class
	 *
	 * @throws PluginException
	 */
	public function registerEvents(Listener $listener, Plugin $plugin) : void{
		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin attempted to register " . get_class($listener) . " while not enabled");
		}

		$reflection = new \ReflectionClass(get_class($listener));
		foreach($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method){
			if(!$method->isStatic() and $method->getDeclaringClass()->implementsInterface(Listener::class)){
				$tags = Utils::parseDocComment((string) $method->getDocComment());
				if(isset($tags["notHandler"])){
					continue;
				}

				$parameters = $method->getParameters();
				if(count($parameters) !== 1){
					continue;
				}

				$handlerClosure = $method->getClosure($listener);

				try{
					$eventClass = $parameters[0]->getClass();
				}catch(\ReflectionException $e){ //class doesn't exist
					if(isset($tags["softDepend"]) && !isset($this->plugins[$tags["softDepend"]])){
						$this->server->getLogger()->debug("Not registering @softDepend listener " . Utils::getNiceClosureName($handlerClosure) . "() because plugin \"" . $tags["softDepend"] . "\" not found");
						continue;
					}

					throw $e;
				}
				if($eventClass === null or !$eventClass->isSubclassOf(Event::class)){
					continue;
				}

				try{
					$priority = isset($tags["priority"]) ? EventPriority::fromString($tags["priority"]) : EventPriority::NORMAL;
				}catch(\InvalidArgumentException $e){
					throw new PluginException("Event handler " . Utils::getNiceClosureName($handlerClosure) . "() declares invalid/unknown priority \"" . $tags["priority"] . "\"");
				}

				$handleCancelled = false;
				if(isset($tags["handleCancelled"])){
					switch(strtolower($tags["handleCancelled"])){
						case "true":
						case "":
							$handleCancelled = true;
							break;
						case "false":
							break;
						default:
							throw new PluginException("Event handler " . Utils::getNiceClosureName($handlerClosure) . "() declares invalid @handleCancelled value \"" . $tags["handleCancelled"] . "\"");
					}
				}

				/** @phpstan-var \ReflectionClass<Event> $eventClass */
				$this->registerEvent($eventClass->getName(), $handlerClosure, $priority, $plugin, $handleCancelled);
			}
		}
	}

	/**
	 * @param string $event Class name that extends Event
	 *
	 * @phpstan-template TEvent of Event
	 * @phpstan-param class-string<TEvent> $event
	 * @phpstan-param \Closure(TEvent) : void $handler
	 *
	 * @throws \ReflectionException
	 */
	public function registerEvent(string $event, \Closure $handler, int $priority, Plugin $plugin, bool $handleCancelled = false) : void{
		if(!is_subclass_of($event, Event::class)){
			throw new PluginException($event . " is not an Event");
		}

		$handlerName = Utils::getNiceClosureName($handler);

		if(!$plugin->isEnabled()){
			throw new PluginException("Plugin attempted to register event handler " . $handlerName . "() to event " . $event . " while not enabled");
		}

		$timings = new TimingsHandler("Plugin: " . $plugin->getDescription()->getFullName() . " Event: " . $handlerName . "(" . (new \ReflectionClass($event))->getShortName() . ")");

		HandlerListManager::global()->getListFor($event)->register(new RegisteredListener($handler, $priority, $plugin, $handleCancelled, $timings));
	}
}
