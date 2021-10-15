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

use PHPModelGenerator\Exception\ValidationException;
use pocketmine\datamodels\immutable\PluginManifest;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionParser;
use function is_array;
use function is_string;
use function phpversion;
use function preg_match;
use function str_replace;
use function stripos;
use function strlen;
use function substr;
use function version_compare;
use function yaml_parse;

class PluginDescription{
	/**
	 * @var mixed[]
	 * @phpstan-var array<string, mixed>
	 */
	private array $map;

	private string $name;
	private string $main;
	private string $srcNamespacePrefix = "";
	/** @var string[] */
	private array $api;
	/** @var int[] */
	private array $compatibleMcpeProtocols = [];
	/** @var string[] */
	private array $compatibleOperatingSystems = [];
	/**
	 * @var string[][]
	 * @phpstan-var array<string, list<string>>
	 */
	private array $extensions = [];
	/** @var string[] */
	private array $depend = [];
	/** @var string[] */
	private array $softDepend = [];
	/** @var string[] */
	private array $loadBefore = [];
	private string $version;
	/**
	 * @var PluginDescriptionCommandEntry[]
	 * @phpstan-var array<string, PluginDescriptionCommandEntry>
	 */
	private array $commands = [];
	private string $description = "";
	/** @var string[] */
	private array $authors = [];
	private string $website = "";
	private string $prefix = "";
	private PluginEnableOrder $order;

	/**
	 * @var Permission[][]
	 * @phpstan-var array<string, list<Permission>>
	 */
	private array $permissions = [];

	/**
	 * @param string|mixed[] $yamlString
	 *
	 * @throws PluginDescriptionParseException
	 */
	public function __construct($yamlString){
		if(!is_array($yamlString)){
			$yamlString = yaml_parse($yamlString);
			if(!is_array($yamlString)){
				throw new PluginDescriptionParseException("Manifest must be a set of properties");
			}
		}
		$this->map = $yamlString;

		try{
			$this->loadMap(new PluginManifest($this->map));
		}catch(ValidationException $e){
			throw new PluginDescriptionParseException($e->getMessage(), 0, $e);
		}
	}

	/**
	 * @throws PluginDescriptionParseException
	 */
	private function loadMap(PluginManifest $model) : void{
		$this->name = $model->getName();
		if(preg_match('/^[A-Za-z0-9 _.-]+$/', $this->name) === 0){
			throw new PluginDescriptionParseException("Invalid Plugin name");
		}
		$this->name = str_replace(" ", "_", $this->name);
		$this->version = (string) $model->getVersion();
		$this->main = $model->getMain();
		if(stripos($this->main, "pocketmine\\") === 0){
			throw new PluginDescriptionParseException("Invalid Plugin main, cannot start within the PocketMine namespace");
		}

		$this->srcNamespacePrefix = $model->getSrcNamespacePrefix() ?? "";

		$this->api = (array) ($model->getApi() ?? []);
		$this->compatibleMcpeProtocols = (array) ($model->getMcpeProtocol() ?? []);
		$this->compatibleOperatingSystems = (array) ($model->getOs() ?? []);

		if(($commands = $model->getCommands()) !== null){
			foreach(($commands->getAdditionalProperties()) as $commandName => $commandData){
				$this->commands[(string) $commandName] = new PluginDescriptionCommandEntry(
					$commandData->getDescription(),
					$commandData->getUsage(),
					$commandData->getAliases() ?? [],
					$commandData->getPermission(),
					$commandData->getPermissionMessage()
				);
			}
		}

		$this->depend = (array) ($model->getDepend() ?? []);

		$parsedExtensions = $model->getExtensions();
		if(is_string($parsedExtensions)){
			$this->extensions[$parsedExtensions] = ["*"];
		}elseif(is_array($parsedExtensions)){
			foreach($parsedExtensions as $v){
				$this->extensions[$v] = ["*"];
			}
		}elseif($parsedExtensions !== null){
			foreach($parsedExtensions->getAdditionalProperties() as $extension => $constraints){
				$this->extensions[(string) $extension] = is_array($constraints) ? $constraints : [$constraints];
			}
		}

		$this->softDepend = (array) ($model->getSoftdepend() ?? []);
		$this->loadBefore = (array) ($model->getLoadbefore() ?? []);
		$this->website = $model->getWebsite() ?? "";
		$this->description = $model->getDescription() ?? "";
		$this->prefix = $model->getPrefix() ?? "";

		$load = $model->getLoad();
		if($load !== null){
			$order = PluginEnableOrder::fromString($load);
			if($order === null){
				throw new PluginDescriptionParseException("Invalid Plugin \"load\"");
			}
			$this->order = $order;
		}else{
			$this->order = PluginEnableOrder::POSTWORLD();
		}

		$this->authors = (array) ($model->getAuthor() ?? []);
		if(($additionalAuthors = $model->getAuthors()) !== null){
			foreach($additionalAuthors as $author){
				$this->authors[] = $author;
			}
		}

		if(($permissions = $model->getPermissions()) !== null){
			foreach($permissions->getAdditionalProperties() as $permissionName => $properties){
				$default = PermissionParser::defaultFromString($properties->getDefault());
				if($default === null){
					throw new PluginDescriptionParseException("Failed to parse plugin permission \"$permissionName\": Invalid default \"" . $properties->getDefault() . "\"");
				}

				$this->permissions[$default][] = new Permission($permissionName, $properties->getDescription());
			}
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
	public function getCompatibleOperatingSystems() : array{
		return $this->compatibleOperatingSystems;
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
	 * @return PluginDescriptionCommandEntry[]
	 * @phpstan-return array<string, PluginDescriptionCommandEntry>
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
	 * @throws PluginException if there are required extensions missing or have incompatible version, or if the version constraint cannot be parsed
	 */
	public function checkRequiredExtensions() : void{
		foreach($this->extensions as $name => $versionConstrs){
			$gotVersion = phpversion($name);
			if($gotVersion === false){
				throw new PluginException("Required extension $name not loaded");
			}

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

	public function getSrcNamespacePrefix() : string{ return $this->srcNamespacePrefix; }

	public function getName() : string{
		return $this->name;
	}

	public function getOrder() : PluginEnableOrder{
		return $this->order;
	}

	/**
	 * @return Permission[][]
	 * @phpstan-return array<string, list<Permission>>
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
