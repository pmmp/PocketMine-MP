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

namespace pocketmine\plugin\manifest;

use pocketmine\plugin\loader\PluginLoader;

abstract class AbstractPluginManifest implements PluginManifest{

	/** @var PluginLoader */
	protected $loader;

	/** @var string */
	protected $path;

	public function __construct(PluginLoader $loader, string $path){
		$this->loader = $loader;
		$this->path = $path;
	}

	/**
	 * @inheritdoc
	 */
	public function getLoader() : PluginLoader{
		return $this->loader;
	}

	/**
	 * @inheritdoc
	 */
	public function getPath() : string{
		return $this->path;
	}

}