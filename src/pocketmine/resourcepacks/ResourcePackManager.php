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
use pocketmine\utils\MainLogger;

class ResourcePackManager{

	/** @var string */
	private $path;

	/** @var bool */
	private $serverForceResources = false;

	/** @var ResourcePack[] */
	private $resourcePacks = [];

	/** @var ResourcePack[] */
	private $uuidList = [];

	/**
	 * @param string $path Path to resource-packs directory.
	 */
	public function __construct(string $path){
		$this->path = $path;

		$logger = MainLogger::getLogger();

		if(!file_exists($this->path)){
			$logger->debug("Resource packs path $path does not exist, creating directory");
			mkdir($this->path);
		}elseif(!is_dir($this->path)){
			throw new \InvalidArgumentException("Resource packs path $path exists and is not a directory");
		}

		if(!file_exists($this->path . "resource_packs.yml")){
			copy(\pocketmine\RESOURCE_PATH . "resource_packs.yml", $this->path . "resource_packs.yml");
		}

		$resourcePacksConfig = new Config($this->path . "resource_packs.yml", Config::YAML, []);

		$this->serverForceResources = (bool) $resourcePacksConfig->get("force_resources", false);

		$logger->info("Loading resource packs...");

		foreach($resourcePacksConfig->get("resource_stack", []) as $pos => $pack){
			try{
				$packPath = $this->path . DIRECTORY_SEPARATOR . $pack;
				if(file_exists($packPath)){
					$newPack = null;
					//Detect the type of resource pack.
					if(is_dir($packPath)){
						$logger->warning("Skipped resource entry $pack due to directory resource packs currently unsupported");
					}else{
						$info = new \SplFileInfo($packPath);
						switch($info->getExtension()){
							case "zip":
							case "mcpack":
								$newPack = new ZippedResourcePack($packPath);
								break;
							default:
								$logger->warning("Skipped resource entry $pack due to format not recognized");
								break;
						}
					}

					if($newPack instanceof ResourcePack){
						$this->resourcePacks[] = $newPack;
						$this->uuidList[strtolower($newPack->getPackId())] = $newPack;
					}
				}else{
					$logger->warning("Skipped resource entry $pack due to file or directory not found");
				}
			}catch(\Throwable $e){
				$logger->logException($e);
			}
		}

		$logger->debug("Successfully loaded " . count($this->resourcePacks) . " resource packs");
	}

	/**
	 * Returns the directory which resource packs are loaded from.
	 * @return string
	 */
	public function getPath() : string{
		return $this->path;
	}

	/**
	 * Returns whether players must accept resource packs in order to join.
	 * @return bool
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
	 *
	 * @param string $id
	 * @return ResourcePack|null
	 */
	public function getPackById(string $id){
		return $this->uuidList[strtolower($id)] ?? null;
	}

	/**
	 * Returns an array of pack IDs for packs currently in use.
	 * @return string[]
	 */
	public function getPackIdList() : array{
		return array_keys($this->uuidList);
	}
}
