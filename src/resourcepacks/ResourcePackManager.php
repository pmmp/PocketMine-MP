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

namespace pocketmine\resourcepacks;

use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\utils\Config;
use Symfony\Component\Filesystem\Path;
use function array_keys;
use function copy;
use function count;
use function file_exists;
use function file_get_contents;
use function gettype;
use function is_array;
use function is_dir;
use function is_float;
use function is_int;
use function is_string;
use function mkdir;
use function strtolower;
use const DIRECTORY_SEPARATOR;

class ResourcePackManager{
	private string $path;
	private bool $serverForceResources = false;

	/** @var ResourcePack[] */
	private array $resourcePacks = [];

	/** @var ResourcePack[] */
	private array $uuidList = [];

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private array $encryptionKeys = [];

	/**
	 * @param string $path Path to resource-packs directory.
	 */
	public function __construct(string $path, \Logger $logger){
		$this->path = $path;

		if(!file_exists($this->path)){
			$logger->debug("Resource packs path $path does not exist, creating directory");
			mkdir($this->path);
		}elseif(!is_dir($this->path)){
			throw new \InvalidArgumentException("Resource packs path $path exists and is not a directory");
		}

		$resourcePacksYml = Path::join($this->path, "resource_packs.yml");
		if(!file_exists($resourcePacksYml)){
			copy(Path::join(\pocketmine\RESOURCE_PATH, "resource_packs.yml"), $resourcePacksYml);
		}

		$resourcePacksConfig = new Config($resourcePacksYml, Config::YAML, []);

		$this->serverForceResources = (bool) $resourcePacksConfig->get("force_resources", false);

		$logger->info("Loading resource packs...");

		$resourceStack = $resourcePacksConfig->get("resource_stack", []);
		if(!is_array($resourceStack)){
			throw new \InvalidArgumentException("\"resource_stack\" key should contain a list of pack names");
		}

		foreach($resourceStack as $pos => $pack){
			if(!is_string($pack) && !is_int($pack) && !is_float($pack)){
				$logger->critical("Found invalid entry in resource pack list at offset $pos of type " . gettype($pack));
				continue;
			}
			$pack = (string) $pack;
			try{
				$packPath = Path::join($this->path, $pack);
				if(!file_exists($packPath)){
					throw new ResourcePackException("File or directory not found");
				}
				if(is_dir($packPath)){
					throw new ResourcePackException("Directory resource packs are unsupported");
				}

				$newPack = null;
				//Detect the type of resource pack.
				$info = new \SplFileInfo($packPath);
				switch($info->getExtension()){
					case "zip":
					case "mcpack":
						$newPack = new ZippedResourcePack($packPath);
						break;
				}

				if($newPack instanceof ResourcePack){
					$this->resourcePacks[] = $newPack;
					$index = strtolower($newPack->getPackId());
					$this->uuidList[$index] = $newPack;

					$keyPath = Path::join($this->path, $pack . ".key");
					if(file_exists($keyPath)){
						try{
							$this->encryptionKeys[$index] = ErrorToExceptionHandler::trapAndRemoveFalse(
								fn() => file_get_contents($keyPath)
							);
						}catch(\ErrorException $e){
							throw new ResourcePackException("Could not read encryption key file: " . $e->getMessage(), 0, $e);
						}
					}
				}else{
					throw new ResourcePackException("Format not recognized");
				}
			}catch(ResourcePackException $e){
				$logger->critical("Could not load resource pack \"$pack\": " . $e->getMessage());
			}
		}

		$logger->debug("Successfully loaded " . count($this->resourcePacks) . " resource packs");
	}

	/**
	 * Returns the directory which resource packs are loaded from.
	 */
	public function getPath() : string{
		return $this->path . DIRECTORY_SEPARATOR;
	}

	/**
	 * Returns whether players must accept resource packs in order to join.
	 */
	public function resourcePacksRequired() : bool{
		return $this->serverForceResources;
	}

	/**
	 * Returns an array of resource packs in use, sorted in order of priority.
	 * @return ResourcePack[]
	 */
	public function getResourceStack() : array{
		return $this->resourcePacks;
	}

	/**
	 * Returns the resource pack matching the specified UUID string, or null if the ID was not recognized.
	 */
	public function getPackById(string $id) : ?ResourcePack{
		return $this->uuidList[strtolower($id)] ?? null;
	}

	/**
	 * Returns an array of pack IDs for packs currently in use.
	 * @return string[]
	 */
	public function getPackIdList() : array{
		return array_keys($this->uuidList);
	}

	/**
	 * Returns the key with which the pack was encrypted, or null if the pack has no key.
	 */
	public function getPackEncryptionKey(string $id) : ?string{
		return $this->encryptionKeys[strtolower($id)] ?? null;
	}
}
