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

use function count;
use function preg_match;

/**
 * Manages PocketMine-MP version strings, and compares them
 */
class VersionString{
	private int $major;
	private int $minor;
	private int $patch;
	private string $suffix;

	public function __construct(
		private string $baseVersion,
		private bool $isDevBuild = false,
		private int $buildNumber = 0
	){
		preg_match('/^(\d+)\.(\d+)\.(\d+)(?:-(.*))?$/', $this->baseVersion, $matches);
		if(count($matches) < 4){
			throw new \InvalidArgumentException("Invalid base version \"$baseVersion\", should contain at least 3 version digits");
		}

		$this->major = (int) $matches[1];
		$this->minor = (int) $matches[2];
		$this->patch = (int) $matches[3];
		$this->suffix = $matches[4] ?? "";
	}

	public static function isValidBaseVersion(string $baseVersion) : bool{
		return preg_match('/^\d+\.\d+\.\d+(?:-(.*))?$/', $baseVersion, $matches) === 1;
	}

	public function getNumber() : int{
		return (($this->major * 1_000_000) + ($this->minor * 1_000) + $this->patch);
	}

	public function getBaseVersion() : string{
		return $this->baseVersion;
	}

	public function getFullVersion(bool $build = false) : string{
		$retval = $this->baseVersion;
		if($this->isDevBuild){
			$retval .= "+dev";
			if($build && $this->buildNumber > 0){
				$retval .= "." . $this->buildNumber;
			}
		}

		return $retval;
	}

	public function getMajor() : int{
		return $this->major;
	}

	public function getMinor() : int{
		return $this->minor;
	}

	public function getPatch() : int{
		return $this->patch;
	}

	public function getSuffix() : string{
		return $this->suffix;
	}

	public function getBuild() : int{
		return $this->buildNumber;
	}

	public function isDev() : bool{
		return $this->isDevBuild;
	}

	public function __toString() : string{
		return $this->getFullVersion();
	}

	public function compare(VersionString $target, bool $diff = false) : int{
		$number = $this->getNumber();
		$tNumber = $target->getNumber();
		if($diff){
			return $tNumber - $number;
		}

		if(($result = $tNumber <=> $number) !== 0){
			return $result;
		}
		if($target->isDev() !== $this->isDev()){
			return $this->isDev() ? 1 : -1; //Dev builds of the same version are always considered older than a release
		}
		if(($target->getSuffix() === "") !== ($this->suffix === "")){
			return $this->suffix !== "" ? 1 : -1; //alpha/beta/whatever releases are always considered older than a non-suffixed version
		}
		return $target->getBuild() <=> $this->getBuild();
	}
}
