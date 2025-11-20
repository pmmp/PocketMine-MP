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

namespace pocketmine\utils;

/**
 * Attribute used on registry source classes to tell the codegen script how to use the class to generate the registry
 * interface
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class RegistrySource{
	/**
	 * @param string  $targetClassName Name (without namespace) of the class to generate
	 * @param string  $getAllFunc      Name of a static method that returns iterable<name, value>, used to initialize the registry accessors
	 * @param ?string $preprocessFunc  Name of a static method that will preprocess the values before returning them from registry accessors
	 */
	public function __construct(
		public readonly string $targetClassName,
		public readonly string $getAllFunc,
		public readonly ?string $preprocessFunc = null
	){}
}
