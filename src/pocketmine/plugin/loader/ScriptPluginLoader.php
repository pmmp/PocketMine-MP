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

namespace pocketmine\plugin\loader;

use pocketmine\plugin\manifest\PhpDocManifestLoader;

/**
 * Simple script loader, not for plugin development
 * For an example see https://gist.github.com/shoghicp/516105d470cf7d140757
 */
class ScriptPluginLoader extends AbstractPluginLoader implements FilePluginLoader{
	use FilePluginLoaderTrait;

	public function __construct(){
		$this->addManifestLoader(PhpDocManifestLoader::class);
	}

	/**
	 * @inheritdoc
	 */
	public function getFileExtension() : string{
		return ".php";
	}

}