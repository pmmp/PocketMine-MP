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


namespace pocketmine\config;

use Iterator;

final class ConfigSourceSingletonGroup implements ConfigSource{
	/** @var string */
	private $name;
	/** @var MutableConfigSource $source */
	private $source;

	public function __construct(string $name, ConfigSource $source){
		$this->name = $name;
		$this->source = $source;
	}

	public function string() : ?string{
		return null;
	}

	public function int() : ?int{
		return null;
	}

	public function float() : ?float{
		return null;
	}

	public function bool() : ?bool{
		return null;
	}

	public function mapEntry(string $key) : ?ConfigSource{
		return $key === $this->name ? $this->source : null;
	}

	public function mapEntries() : ?Iterator{
		yield $this->name => $this->source;
	}

	public function listElement(int $index) : ?ConfigSource{
		return null;
	}

	public function listElements() : ?Iterator{
		return null;
	}
}
