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

namespace pocketmine\crash;

final class CrashDumpData implements \JsonSerializable{

	public int $format_version;

	public float $time;

	public float $uptime;

	/** @var mixed[] */
	public array $lastError = [];

	/** @var mixed[] */
	public array $error;

	public string $plugin_involvement;

	public string $plugin = "";

	/** @var string[] */
	public array $code = [];

	/** @var string[] */
	public array $trace;

	/**
	 * @var CrashDumpDataPluginEntry[]
	 * @phpstan-var array<string, CrashDumpDataPluginEntry>
	 */
	public array $plugins = [];

	/** @var string[] */
	public array $parameters = [];

	public string $serverDotProperties = "";

	public string $pocketmineDotYml = "";

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	public array $extensions = [];

	public string $phpinfo = "";

	public CrashDumpDataGeneral $general;

	/**
	 * @return mixed[]
	 */
	public function jsonSerialize() : array{
		$result = (array) $this;
		unset($result["serverDotProperties"]);
		unset($result["pocketmineDotYml"]);
		$result["pocketmine.yml"] = $this->pocketmineDotYml;
		$result["server.properties"] = $this->serverDotProperties;
		return $result;
	}
}