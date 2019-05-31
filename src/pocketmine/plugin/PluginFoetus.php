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

class PluginFoetus{
	/** @var PluginDescription */
	private $description;
	/** @var string */
	private $dataFolder;
	/** @var callable returns a Plugin instance */
	private $createInstance;

	public function __construct(PluginDescription $description, string $dataFolder, callable $createInstance){
		$this->description = $description;
		$this->dataFolder = $dataFolder;
		$this->createInstance = $createInstance;
	}


	/**
	 * @return PluginDescription
	 */
	public function getDescription() : PluginDescription{
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getDataFolder() : string{
		return $this->dataFolder;
	}

	/**
	 * @return callable
	 */
	public function callCreateInstance() : Plugin{
		$createInstance = $this->createInstance;
		return $createInstance($this->description, $this->dataFolder);
	}
}
