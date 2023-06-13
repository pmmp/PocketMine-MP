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

namespace pocketmine\player\camera;

use pocketmine\entity\Location;
use pocketmine\utils\SingletonTrait;

/**
 * This class manage the creation of camera presets.
 */
final class CameraPresetFactory{
	use SingletonTrait;

	/**
	 * @var CameraPreset[]
	 * @phpstan-var array<string, CameraPreset>
	 */
	private array $presets = [];

	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $runtimeIds = [];

	private int $nextRuntimeId = 0;
	
	public function __construct(){
		$this->register(new CameraPreset("minecraft:first_person"));
		$this->register(new CameraPreset("minecraft:third_person"));
		$this->register(new CameraPreset("minecraft:third_person_front"));
		$this->register(new CameraPreset("minecraft:free", "", new Location(0, 0, 0, null, 0, 0)));
	}

	/**
	 * Registers a new camera preset type into the index.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function register(CameraPreset $preset) : void{
		$id = $preset->getIdentifier();
		if ($this->isRegistered($id)) {
			throw new \InvalidArgumentException("A presset with id \"$id\" is already registered");
		}

		$inheritFrom = $preset->getInheritFrom();
		if ($inheritFrom !== "" && !$this->isRegistered($inheritFrom)) {
			throw new \InvalidArgumentException("Parent \"$inheritFrom\" preset is not registered");
		}

		$this->presets[$id] = $preset;
		$this->presets[$id] = $nextRuntimeId++;
	}

	public function fromId(string $identifier) : CameraPreset{
		if (!$this->isRegistered()) {
			throw new \InvalidArgumentException("\"$identifier\" is not registered");
		}

		return $this->presets[$identifier];
	}

	public function getRuntimeId(string $identifier) : int{
		if (!$this->isRegistered()) {
			throw new \InvalidArgumentException("\"$identifier\" is not registered");
		}

		return $this->runtimeIds[$identifier];
	}

	/**
	 * @return CameraPreset[]
	 * @phpstan-return array<string, CameraPreset>
	 */
	public function getAll() : array{
		return $this->presets;
	}

	public function isRegistered(string $identifier) : bool{
		return isset($this->presets[$identifier]);
	}
}
