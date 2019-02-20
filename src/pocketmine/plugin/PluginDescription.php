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

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionParser;
use function array_map;
use function array_values;
use function constant;
use function defined;
use function extension_loaded;
use function is_array;
use function phpversion;
use function preg_match;
use function str_replace;
use function stripos;
use function strlen;
use function strtoupper;
use function substr;
use function version_compare;

class PluginDescription{
	private $map;

	private $name;
	private $main;
	private $api;
	/** @var int[] */
	private $compatibleMcpeProtocols = [];
	private $extensions = [];
	private $depend = [];
	private $softDepend = [];
	private $loadBefore = [];
	/** @var string */
	private $version;
	private $commands = [];
	/** @var string */
	private $description = "";
	/** @var string[] */
	private $authors = [];
	/** @var string */
	private $website = "";
	/** @var string */
	private $prefix = "";
	private $order = PluginLoadOrder::POSTWORLD;

	/**
	 * @var Permission[]
	 */
	private $permissions = [];

	/**
	 * @param string|array $yamlString
	 */
	public function __construct($yamlString){
		$this->loadMap(!is_array($yamlString) ? yaml_parse($yamlString) : $yamlString);
	}

	/**
	 * @param array $plugin
	 *
	 * @throws PluginException
	 */
	private function loadMap(array $plugin){
		$this->map = $plugin;

		$this->name = $plugin["name"];
		if(preg_match('/^[A-Za-z0-9 _.-]+$/', $this->name) === 0){
			throw new PluginException("Invalid PluginDescription name");
		}
		$this->name = str_replace(" ", "_", $this->name);
		$this->version = (string) $plugin["version"];
		$this->main = $plugin["main"];
		if(stripos($this->main, "pocketmine\\") === 0){
			throw new PluginException("Invalid PluginDescription main, cannot start within the PocketMine namespace");
		}

		$this->api = array_map("\strval", (array) ($plugin["api"] ?? []));
		$this->compatibleMcpeProtocols = array_map("\intval", (array) ($plugin["mcpe-protocol"] ?? []));

		if(isset($plugin["commands"]) and is_array($plugin["commands"])){
			$this->commands = $plugin["commands"];
		}

		if(isset($plugin["depend"])){
			$this->depend = (array) $plugin["depend"];
		}
		if(isset($plugin["extensions"])){
			$extensions = (array) $plugin["extensions"];
			$isLinear = $extensions === array_values($extensions);
			foreach($extensions as $k => $v){
				if($isLinear){
					$k = $v;
					$v = "*";
				}
				$this->extensions[$k] = is_array($v) ? $v : [$v];
			}
		}

		$this->softDepend = (array) ($plugin["softdepend"] ?? $this->softDepend);

		$this->loadBefore = (array) ($plugin["loadbefore"] ?? $this->loadBefore);

		$this->website = (string) ($plugin["website"] ?? $this->website);

		$this->description = (string) ($plugin["description"] ?? $this->description);

		$this->prefix = (string) ($plugin["prefix"] ?? $this->prefix);

		if(isset($plugin["load"])){
			$order = strtoupper($plugin["load"]);
			if(!defined(PluginLoadOrder::class . "::" . $order)){
				throw new PluginException("Invalid PluginDescription load");
			}else{
				$this->order = constant(PluginLoadOrder::class . "::" . $order);
			}
		}
		$this->authors = [];
		if(isset($plugin["author"])){
			$this->authors[] = $plugin["author"];
		}
		if(isset($plugin["authors"])){
			foreach($plugin["authors"] as $author){
				$this->authors[] = $author;
			}
		}

		if(isset($plugin["permissions"])){
			$this->permissions = PermissionParser::loadPermissions($plugin["permissions"]);
		}
	}

	/**
	 * @return string
	 */
	public function getFullName() : string{
		return $this->name . " v" . $this->version;
	}

	/**
	 * @return array
	 */
	public function getCompatibleApis() : array{
		return $this->api;
	}

	/**
	 * @return int[]
	 */
	public function getCompatibleMcpeProtocols() : array{
		return $this->compatibleMcpeProtocols;
	}

	/**
	 * @return string[]
	 */
	public function getAuthors() : array{
		return $this->authors;
	}

	/**
	 * @return string
	 */
	public function getPrefix() : string{
		return $this->prefix;
	}

	/**
	 * @return array
	 */
	public function getCommands() : array{
		return $this->commands;
	}

	/**
	 * @return array
	 */
	public function getRequiredExtensions() : array{
		return $this->extensions;
	}

	/**
	 * Checks if the current PHP runtime has the extensions required by the plugin.
	 *
	 * @throws PluginException if there are required extensions missing or have incompatible version, or if the version constraint cannot be parsed
	 */
	public function checkRequiredExtensions(){
		foreach($this->extensions as $name => $versionConstrs){
			if(!extension_loaded($name)){
				throw new PluginException("Required extension $name not loaded");
			}

			if(!is_array($versionConstrs)){
				$versionConstrs = [$versionConstrs];
			}
			$gotVersion = phpversion($name);
			foreach($versionConstrs as $constr){ // versionConstrs_loop
				if($constr === "*"){
					continue;
				}
				if($constr === ""){
					throw new PluginException("One of the extension version constraints of $name is empty. Consider quoting the version string in plugin.yml");
				}
				foreach(["<=", "le", "<>", "!=", "ne", "<", "lt", "==", "=", "eq", ">=", "ge", ">", "gt"] as $comparator){
					// warning: the > character should be quoted in YAML
					if(substr($constr, 0, strlen($comparator)) === $comparator){
						$version = substr($constr, strlen($comparator));
						if(!version_compare($gotVersion, $version, $comparator)){
							throw new PluginException("Required extension $name has an incompatible version ($gotVersion not $constr)");
						}
						continue 2; // versionConstrs_loop
					}
				}
				throw new PluginException("Error parsing version constraint: $constr");
			}
		}
	}

	/**
	 * @return array
	 */
	public function getDepend() : array{
		return $this->depend;
	}

	/**
	 * @return string
	 */
	public function getDescription() : string{
		return $this->description;
	}

	/**
	 * @return array
	 */
	public function getLoadBefore() : array{
		return $this->loadBefore;
	}

	/**
	 * @return string
	 */
	public function getMain() : string{
		return $this->main;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getOrder() : int{
		return $this->order;
	}

	/**
	 * @return Permission[]
	 */
	public function getPermissions() : array{
		return $this->permissions;
	}

	/**
	 * @return array
	 */
	public function getSoftDepend() : array{
		return $this->softDepend;
	}

	/**
	 * @return string
	 */
	public function getVersion() : string{
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getWebsite() : string{
		return $this->website;
	}

	public function getMap() : array{
		return $this->map;
	}
}
