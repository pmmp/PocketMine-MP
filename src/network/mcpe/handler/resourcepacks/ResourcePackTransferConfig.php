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

namespace pocketmine\network\mcpe\handler\resourcepacks;

use pocketmine\ServerConfigGroup;
use function max;
use function min;

/**
 * Immutable configuration for the adaptive resource pack transfer scheduler.
 */
final class ResourcePackTransferConfig{
	private const CONFIG_ROOT = "resource-pack-transfer";
	private const DEFAULT_ACK_SAMPLE_WINDOW = 8;
	private const MIN_BYTES_PER_TICK = 256 * 1024;

	public function __construct(
		public readonly bool $enabled,
		public readonly int $initialWindow,
		public readonly int $maxWindow,
		public readonly int $maxChunksPerTick,
		public readonly int $maxBytesPerTick,
		public readonly int $stallTimeoutMs,
		public readonly int $ackFastThresholdMs,
		public readonly int $ackSlowThresholdMs,
		public readonly bool $debugLog,
		public readonly int $ackSampleWindow = self::DEFAULT_ACK_SAMPLE_WINDOW
	){}

	public static function fromConfig(ServerConfigGroup $configGroup) : self{
		$initialWindow = max(1, $configGroup->getPropertyInt(self::CONFIG_ROOT . ".initial-window", 1));
		$maxWindow = max($initialWindow, $configGroup->getPropertyInt(self::CONFIG_ROOT . ".max-window", 4));
		$ackFastThresholdMs = max(1, $configGroup->getPropertyInt(self::CONFIG_ROOT . ".ack-fast-threshold-ms", 150));
		$ackSlowThresholdMs = max($ackFastThresholdMs, $configGroup->getPropertyInt(self::CONFIG_ROOT . ".ack-slow-threshold-ms", 800));

		return new self(
			enabled: $configGroup->getPropertyBool(self::CONFIG_ROOT . ".enabled", true),
			initialWindow: $initialWindow,
			maxWindow: $maxWindow,
			maxChunksPerTick: max(1, $configGroup->getPropertyInt(self::CONFIG_ROOT . ".max-chunks-per-tick", 2)),
			maxBytesPerTick: max(self::MIN_BYTES_PER_TICK, $configGroup->getPropertyInt(self::CONFIG_ROOT . ".max-bytes-per-tick", 1024 * 1024)),
			stallTimeoutMs: max(1000, $configGroup->getPropertyInt(self::CONFIG_ROOT . ".stall-timeout-ms", 10_000)),
			ackFastThresholdMs: $ackFastThresholdMs,
			ackSlowThresholdMs: $ackSlowThresholdMs,
			debugLog: $configGroup->getPropertyBool(self::CONFIG_ROOT . ".debug-log", false),
			ackSampleWindow: min(32, max(1, self::DEFAULT_ACK_SAMPLE_WINDOW))
		);
	}

	public static function legacy() : self{
		return new self(
			enabled: false,
			initialWindow: 1,
			maxWindow: 1,
			maxChunksPerTick: 1,
			maxBytesPerTick: 1024 * 1024,
			stallTimeoutMs: 10_000,
			ackFastThresholdMs: 150,
			ackSlowThresholdMs: 800,
			debugLog: false
		);
	}
}
