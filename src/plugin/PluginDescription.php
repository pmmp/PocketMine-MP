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
use pocketmine\utils\Utils;
use function array_map;
use function array_values;
use function get_debug_type;
use function is_array;
use function is_string;
use function preg_match;
use function str_replace;
use function stripos;
use function yaml_parse;

class PluginDescription{
	private const KEY_NAME = "name";
	private const KEY_VERSION = "version";
	private const KEY_MAIN = "main";
	private const KEY_SRC_NAMESPACE_PREFIX = "src-namespace-prefix";
	private const KEY_API = "api";
	private const KEY_MCPE_PROTOCOL = "mcpe-protocol";
	private const KEY_OS = "os";
	private const KEY_DEPEND = "depend";
	private const KEY_SOFTDEPEND = "softdepend";
	private const KEY_LOADBEFORE = "loadbefore";
	private const KEY_EXTENSIONS = "extensions";
	private const KEY_WEBSITE = "website";
	private const KEY_DESCRIPTION = "description";
	private const KEY_LOGGER_PREFIX = "prefix";
	private const KEY_LOAD = "load";
	private const KEY_AUTHOR = "author";
	private const KEY_AUTHORS = "authors";
	private const KEY_PERMISSIONS = "permissions";

	private const KEY_COMMANDS = "commands";
	private const KEY_COMMAND_PERMISSION = "permission";
	private const KEY_COMMAND_DESCRIPTION = self::KEY_DESCRIPTION;
	private const KEY_COMMAND_USAGE = "usage";
	private const KEY_COMMAND_ALIASES = "aliases";
	private const KEY_COMMAND_PERMISSION_MESSAGE = "permission-message";

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
	public function __construct(array|string $yamlString){
		if(is_string($yamlString)){
			$map = yaml_parse($yamlString);
			if($map === false){
				throw new PluginDescriptionParseException("YAML parsing error in plugin manifest");
			}
			if(!is_array($map)){
				throw new PluginDescriptionParseException("Invalid structure of plugin manifest, expected array but have " . get_debug_type($map));
			}
		}else{
			$map = $yamlString;
		}
		$this->loadMap($map);
	}

	/**
	 * @param mixed[] $plugin
	 * @throws PluginDescriptionParseException
	 */
	private function loadMap(array $plugin) : void{
		$this->map = $plugin;

		$this->name = $plugin[self::KEY_NAME];
		if(preg_match('/^[A-Za-z0-9 _.-]+$/', $this->name) === 0){
			throw new PluginDescriptionParseException("Invalid Plugin name");
		}
		$this->name = str_replace(" ", "_", $this->name);
		$this->version = (string) $plugin[self::KEY_VERSION];
		$this->main = $plugin[self::KEY_MAIN];
		if(stripos($this->main, "pocketmine\\") === 0){
			throw new PluginDescriptionParseException("Invalid Plugin main, cannot start within the PocketMine namespace");
		}

		$this->srcNamespacePrefix = $plugin[self::KEY_SRC_NAMESPACE_PREFIX] ?? "";

		$this->api = array_map("\strval", (array) ($plugin[self::KEY_API] ?? []));
		$this->compatibleMcpeProtocols = array_map("\intval", (array) ($plugin[self::KEY_MCPE_PROTOCOL] ?? []));
		$this->compatibleOperatingSystems = array_map("\strval", (array) ($plugin[self::KEY_OS] ?? []));

		if(isset($plugin[self::KEY_COMMANDS]) && is_array($plugin[self::KEY_COMMANDS])){
			foreach(Utils::promoteKeys($plugin[self::KEY_COMMANDS]) as $commandName => $commandData){
				if(!is_string($commandName)){
					throw new PluginDescriptionParseException("Invalid Plugin commands, key must be the name of the command");
				}
				if(!is_array($commandData)){
					throw new PluginDescriptionParseException("Command $commandName has invalid properties");
				}
				if(!isset($commandData[self::KEY_COMMAND_PERMISSION]) || !is_string($commandData[self::KEY_COMMAND_PERMISSION])){
					throw new PluginDescriptionParseException("Command $commandName does not have a valid permission set");
				}
				$this->commands[$commandName] = new PluginDescriptionCommandEntry(
					$commandData[self::KEY_COMMAND_DESCRIPTION] ?? null,
					$commandData[self::KEY_COMMAND_USAGE] ?? null,
					$commandData[self::KEY_COMMAND_ALIASES] ?? [],
					$commandData[self::KEY_COMMAND_PERMISSION],
					$commandData[self::KEY_COMMAND_PERMISSION_MESSAGE] ?? null
				);
			}
		}

		if(isset($plugin[self::KEY_DEPEND])){
			$this->depend = (array) $plugin[self::KEY_DEPEND];
		}
		if(isset($plugin[self::KEY_EXTENSIONS])){
			$extensions = (array) $plugin[self::KEY_EXTENSIONS];
			$isLinear = $extensions === array_values($extensions);
			foreach(Utils::promoteKeys($extensions) as $k => $v){
				if($isLinear){
					$k = $v;
					$v = "*";
				}
				$this->extensions[(string) $k] = array_map('strval', is_array($v) ? $v : [$v]);
			}
		}

		$this->softDepend = (array) ($plugin[self::KEY_SOFTDEPEND] ?? $this->softDepend);

		$this->loadBefore = (array) ($plugin[self::KEY_LOADBEFORE] ?? $this->loadBefore);

		$this->website = (string) ($plugin[self::KEY_WEBSITE] ?? $this->website);

		$this->description = (string) ($plugin[self::KEY_DESCRIPTION] ?? $this->description);

		$this->prefix = (string) ($plugin[self::KEY_LOGGER_PREFIX] ?? $this->prefix);

		if(isset($plugin[self::KEY_LOAD])){
			$order = PluginEnableOrder::fromString($plugin[self::KEY_LOAD]);
			if($order === null){
				throw new PluginDescriptionParseException("Invalid Plugin \"" . self::KEY_LOAD . "\"");
			}
			$this->order = $order;
		}else{
			$this->order = PluginEnableOrder::POSTWORLD;
		}

		$this->authors = [];
		if(isset($plugin[self::KEY_AUTHOR])){
			if(is_array($plugin[self::KEY_AUTHOR])){
				$this->authors = $plugin[self::KEY_AUTHOR];
			}else{
				$this->authors[] = $plugin[self::KEY_AUTHOR];
			}
		}
		if(isset($plugin[self::KEY_AUTHORS])){
			foreach($plugin[self::KEY_AUTHORS] as $author){
				$this->authors[] = $author;
			}
		}

		if(isset($plugin[self::KEY_PERMISSIONS])){
			try{
				$this->permissions = PermissionParser::loadPermissions($plugin[self::KEY_PERMISSIONS]);
			}catch(PermissionParserException $e){
				throw new PluginDescriptionParseException("Invalid Plugin \"" . self::KEY_PERMISSIONS . "\": " . $e->getMessage(), 0, $e);
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
