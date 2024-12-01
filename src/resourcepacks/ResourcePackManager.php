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

use pocketmine\utils\Config;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function array_keys;
use function copy;
use function count;
use function file_exists;
use function gettype;
use function is_array;
use function is_dir;
use function is_float;
use function is_int;
use function is_string;
use function mkdir;
use function rtrim;
use function strlen;
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

		foreach(Utils::promoteKeys($resourceStack) as $pos => $pack){
			if(!is_string($pack) && !is_int($pack) && !is_float($pack)){
				$logger->critical("Found invalid entry in resource pack list at offset $pos of type " . gettype($pack));
				continue;
			}
			$pack = (string) $pack;
			try{
				$newPack = $this->loadPackFromPath(Path::join($this->path, $pack));

				$this->resourcePacks[] = $newPack;
				$index = strtolower($newPack->getPackId());
				$this->uuidList[$index] = $newPack;

				$keyPath = Path::join($this->path, $pack . ".key");
				if(file_exists($keyPath)){
					try{
						$key = Filesystem::fileGetContents($keyPath);
					}catch(\RuntimeException $e){
						throw new ResourcePackException("Could not read encryption key file: " . $e->getMessage(), 0, $e);
					}
					$key = rtrim($key, "\r\n");
					if(strlen($key) !== 32){
						throw new ResourcePackException("Invalid encryption key length, must be exactly 32 bytes");
					}
					$this->encryptionKeys[$index] = $key;
				}
			}catch(ResourcePackException $e){
				$logger->critical("Could not load resource pack \"$pack\": " . $e->getMessage());
			}
		}

		$logger->debug("Successfully loaded " . count($this->resourcePacks) . " resource packs");
	}

	private function loadPackFromPath(string $packPath) : ResourcePack{
		if(!file_exists($packPath)){
			throw new ResourcePackException("File or directory not found");
		}
		if(is_dir($packPath)){
			throw new ResourcePackException("Directory resource packs are unsupported");
		}

		//Detect the type of resource pack.
		$info = new \SplFileInfo($packPath);
		switch($info->getExtension()){
			case "zip":
			case "mcpack":
				return new ZippedResourcePack($packPath);
		}

		throw new ResourcePackException("Format not recognized");
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
	 * Sets whether players must accept resource packs in order to join.
	 */
	public function setResourcePacksRequired(bool $value) : void{
		$this->serverForceResources = $value;
	}

	/**
	 * Returns an array of resource packs in use, sorted in order of priority.
	 * @return ResourcePack[]
	 */
	public function getResourceStack() : array{
		return $this->resourcePacks;
	}

	/**
	 * Sets the resource packs to use. Packs earliest in the list will appear at the top of the stack (maximum
	 * priority), and later ones will appear below (lower priority), in the same manner as the Bedrock resource packs
	 * screen in-game.
	 *
	 * @param ResourcePack[] $resourceStack
	 * @phpstan-param list<ResourcePack> $resourceStack
	 */
	public function setResourceStack(array $resourceStack) : void{
		$uuidList = [];
		$resourcePacks = [];
		foreach($resourceStack as $pack){
			$uuid = strtolower($pack->getPackId());
			if(isset($uuidList[$uuid])){
				throw new \InvalidArgumentException("Cannot load two resource pack with the same UUID ($uuid)");
			}
			$uuidList[$uuid] = $pack;
			$resourcePacks[] = $pack;
		}
		$this->resourcePacks = $resourcePacks;
		$this->uuidList = $uuidList;
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

	/**
	 * Sets the encryption key to use for decrypting the specified resource pack. The pack will **NOT** be decrypted by
	 * PocketMine-MP; the key is simply passed to the client to allow it to decrypt the pack after downloading it.
	 */
	public function setPackEncryptionKey(string $id, ?string $key) : void{
		$id = strtolower($id);
		if($key === null){
			//allow deprovisioning keys for resource packs that have been removed
			unset($this->encryptionKeys[$id]);
		}elseif(isset($this->uuidList[$id])){
			if(strlen($key) !== 32){
				throw new \InvalidArgumentException("Encryption key must be exactly 32 bytes long");
			}
			$this->encryptionKeys[$id] = $key;
		}else{
			throw new \InvalidArgumentException("Unknown pack ID $id");
		}
	}
}
