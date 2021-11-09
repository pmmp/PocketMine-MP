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
use pocketmine\permission\PermissionParserException;
use function array_map;
use function array_values;
use function is_array;
use function is_string;
use function preg_match;
use function str_replace;
use function stripos;
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
	 */
	public function __construct($yamlString){
		$this->loadMap(!is_array($yamlString) ? yaml_parse($yamlString) : $yamlString);
	}

	/**
	 * @param mixed[] $plugin
	 * @throws PluginDescriptionParseException
	 */
	private function loadMap(array $plugin) : void{
		$this->map = $plugin;

		$this->name = $plugin["name"];
		if(preg_match('/^[A-Za-z0-9 _.-]+$/', $this->name) === 0){
			throw new PluginDescriptionParseException("Invalid Plugin name");
		}
		$this->name = str_replace(" ", "_", $this->name);
		$this->version = (string) $plugin["version"];
		$this->main = $plugin["main"];
		if(stripos($this->main, "pocketmine\\") === 0){
			throw new PluginDescriptionParseException("Invalid Plugin main, cannot start within the PocketMine namespace");
		}

		$this->srcNamespacePrefix = $plugin["src-namespace-prefix"] ?? "";

		$this->api = array_map("\strval", (array) ($plugin["api"] ?? []));
		$this->compatibleMcpeProtocols = array_map("\intval", (array) ($plugin["mcpe-protocol"] ?? []));
		$this->compatibleOperatingSystems = array_map("\strval", (array) ($plugin["os"] ?? []));

		if(isset($plugin["commands"]) and is_array($plugin["commands"])){
			foreach($plugin["commands"] as $commandName => $commandData){
				if(!is_string($commandName)){
					throw new PluginDescriptionParseException("Invalid Plugin commands, key must be the name of the command");
				}
				if(!is_array($commandData)){
					throw new PluginDescriptionParseException("Command $commandName has invalid properties");
				}
				if(!isset($commandData["permission"]) || !is_string($commandData["permission"])){
					throw new PluginDescriptionParseException("Command $commandName does not have a valid permission set");
				}
				$this->commands[$commandName] = new PluginDescriptionCommandEntry(
					$commandData["description"] ?? null,
					$commandData["usage"] ?? null,
					$commandData["aliases"] ?? [],
					$commandData["permission"],
					$commandData["permission-message"] ?? null
				);
			}
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
				$this->extensions[$k] = array_map('strval', is_array($v) ? $v : [$v]);
			}
		}

		$this->softDepend = (array) ($plugin["softdepend"] ?? $this->softDepend);

		$this->loadBefore = (array) ($plugin["loadbefore"] ?? $this->loadBefore);

		$this->website = (string) ($plugin["website"] ?? $this->website);

		$this->description = (string) ($plugin["description"] ?? $this->description);

		$this->prefix = (string) ($plugin["prefix"] ?? $this->prefix);

		if(isset($plugin["load"])){
			$order = PluginEnableOrder::fromString($plugin["load"]);
			if($order === null){
				throw new PluginDescriptionParseException("Invalid Plugin \"load\"");
			}
			$this->order = $order;
		}else{
			$this->order = PluginEnableOrder::POSTWORLD();
		}

		$this->authors = [];
		if(isset($plugin["author"])){
			if(is_array($plugin["author"])){
				$this->authors = $plugin["author"];
			}else{
				$this->authors[] = $plugin["author"];
			}
		}
		if(isset($plugin["authors"])){
			foreach($plugin["authors"] as $author){
				$this->authors[] = $author;
			}
		}

		if(isset($plugin["permissions"])){
			try{
				$this->permissions = PermissionParser::loadPermissions($plugin["permissions"]);
			}catch(PermissionParserException $e){
				throw new PluginDescriptionParseException("Invalid Plugin \"permissions\": " . $e->getMessage(), 0, $e);
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
