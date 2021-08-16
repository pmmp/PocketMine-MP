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

namespace pocketmine\world\format\io;

use pocketmine\world\WorldCreationOptions;

/**
 * @phpstan-type FromPath \Closure(string $path) : WritableWorldProvider
 * @phpstan-type Generate \Closure(string $path, string $name, WorldCreationOptions $options) : void
 */
final class WritableWorldProviderManagerEntry extends WorldProviderManagerEntry{
	/** @phpstan-var FromPath */
	private \Closure $fromPath;
	/** @phpstan-var Generate */
	private \Closure $generate;

	/**
	 * @phpstan-param FromPath $fromPath
	 * @phpstan-param Generate $generate
	 */
	public function __construct(\Closure $isValid, \Closure $fromPath, \Closure $generate){
		parent::__construct($isValid);
		$this->fromPath = $fromPath;
		$this->generate = $generate;
	}

	public function fromPath(string $path) : WritableWorldProvider{
		return ($this->fromPath)($path);
	}

	/**
	 * Generates world manifest files and any other things needed to initialize a new world on disk
	 */
	public function generate(string $path, string $name, WorldCreationOptions $options) : void{
		($this->generate)($path, $name, $options);
	}
}
