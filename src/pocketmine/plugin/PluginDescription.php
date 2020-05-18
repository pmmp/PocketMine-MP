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
use function yaml_parse;

class PluginDescription{
	/**
	 * @var mixed[]
	 * @phpstan-var array<string, mixed>
	 */
	private $map;

	/** @var string */
	private $name;
	/** @var string */
	private $main;
	/** @var string[] */
	private $api;
	/** @var int[] */
	private $compatibleMcpeProtocols = [];
	/**
	 * @var string[][]
	 * @phpstan-var array<string, list<mixed>>
	 */
	private $extensions = [];
	/** @var string[] */
	private $depend = [];
	/** @var string[] */
	private $softDepend = [];
	/** @var string[] */
	private $loadBefore = [];
	/** @var string */
	private $version;
	/**
	 * @var mixed[][]
	 * @phpstan-var array<string, array<string, mixed>>
	 */
	private $commands = [];
	/** @var string */
	private $description = "";
	/** @var string[] */
	private $authors = [];
	/** @var string */
	private $website = "";
	/** @var string */
	private $prefix = "";
	/** @var int */
	private $order = PluginLoadOrder::POSTWORLD;

	/** @var Permission[] */
	private $permissions = [];

	/**
	 * @param string|mixed[] $yamlString
	 */
	public function __construct($yamlString){
		$this->loadMap(!is_array($yamlString) ? yaml_parse($yamlString) : $yamlString);
	}

	/**
	 * @param mixed[] $plugin
	 * @throws PluginException
	 */
	private function loadMap(array $plugin) : void{
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
			$this->permissions = Permission::loadPermissions($plugin["permissions"]);
		}
	}

	public function getFullName() : string{
		return $this->name . " v" . $this->version;
	}

	/**
	 * @return string[]
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

	public function getPrefix() : string{
		return $this->prefix;
	}

	/**
	 * @return mixed[][]
	 * @phpstan-return array<string, array<string, mixed>>
	 */
	public function getCommands() : array{
		return $this->commands;
	}

	/**
	 * @return string[][]
	 * @phpstan-return array<string, list<string>>
	 */
	public function getRequiredExtensions() : array{
		return $this->extensions;
	}

	/**
	 * Checks if the current PHP runtime has the extensions required by the plugin.
	 *
	 * @return void
	 * @throws PluginException if there are required extensions missing or have incompatible version, or if the version constraint cannot be parsed
	 */
	public function checkRequiredExtensions(){
		foreach($this->extensions as $name => $versionConstrs){
			if(!extension_loaded($name)){
				throw new PluginException("Required extension $name not loaded");
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
	 * @return string[]
	 */
	public function getDepend() : array{
		return $this->depend;
	}

	public function getDescription() : string{
		return $this->description;
	}

	/**
	 * @return string[]
	 */
	public function getLoadBefore() : array{
		return $this->loadBefore;
	}

	public function getMain() : string{
		return $this->main;
	}

	public function getName() : string{
		return $this->name;
	}

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
	 * @return string[]
	 */
	public function getSoftDepend() : array{
		return $this->softDepend;
	}

	public function getVersion() : string{
		return $this->version;
	}

	public function getWebsite() : string{
		return $this->website;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<string, mixed>
	 */
	public function getMap() : array{
		return $this->map;
	}
}
