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

use pocketmine\utils\Utils;
use function array_shift;
use function count;
use function max;
use function min;

/**
 * Tracks adaptive scheduling, budgets and lightweight metrics for one session's
 * resource pack transfer lifecycle.
 */
final class ResourcePackTransferState{
	/**
	 * @var array<string, array{
	 *     totalChunks: int,
	 *     sizeBytes: int,
	 *     requested: bool,
	 *     sentChunks: int,
	 *     ackedChunks: int,
	 *     lastSentChunkIndex: int,
	 *     failures: int
	 * }>
	 */
	private array $packStats = [];

	/**
	 * @var array<string, array{packId: string, chunkIndex: int, bytes: int, enqueuedAt: float, sentAt: float}>
	 */
	private array $inflightChunks = [];

	/** @var array<string, float> */
	private array $chunkEnqueueTimes = [];

	/** @var list<float> */
	private array $recentAckMs = [];

	private int $windowSize;
	private int $remainingChunksBudget = 0;
	private int $remainingBytesBudget = 0;
	private ?float $startedAt = null;
	private float $lastProgressAt;
	private ?float $lastStallAt = null;
	private bool $stallRecoveryMode = false;
	private bool $paused = false;
	private int $failureCount = 0;
	private int $stallCount = 0;
	private int $requestedPackCount = 0;
	private int $requestedTotalBytes = 0;
	private int $requestedTotalChunks = 0;
	private int $ackedRequestedChunks = 0;
	private float $maxAckMs = 0.0;
	private float $totalAckMs = 0.0;
	private int $ackCount = 0;
	private float $maxDispatchDelayMs = 0.0;
	private int $maxObservedInflight = 0;
	private int $maxObservedWindow = 0;
	private int $maxObservedQueueLength = 0;
	private ?string $lastFailureReason = null;

	/**
	 * @param array<string, array{totalChunks: int, sizeBytes: int}> $packMetadata
	 */
	public function __construct(
		private readonly ResourcePackTransferConfig $config,
		array $packMetadata,
		float $createdAt
	){
		foreach(Utils::stringifyKeys($packMetadata) as $packId => $metadata){
			$this->packStats[$packId] = [
				"totalChunks" => $metadata["totalChunks"],
				"sizeBytes" => $metadata["sizeBytes"],
				"requested" => false,
				"sentChunks" => 0,
				"ackedChunks" => 0,
				"lastSentChunkIndex" => -1,
				"failures" => 0,
			];
		}

		$this->windowSize = $config->initialWindow;
		$this->lastProgressAt = $createdAt;
		$this->maxObservedWindow = $this->windowSize;
		$this->resetTickBudget();
	}

	public function markRequestedPack(string $packId, float $at) : void{
		if(isset($this->packStats[$packId]) && !$this->packStats[$packId]["requested"]){
			$this->packStats[$packId]["requested"] = true;
			$this->requestedPackCount++;
			$this->requestedTotalBytes += $this->packStats[$packId]["sizeBytes"];
			$this->requestedTotalChunks += $this->packStats[$packId]["totalChunks"];
		}
		$this->startedAt ??= $at;
		$this->lastProgressAt = $at;
	}

	public function markChunkEnqueued(string $packId, int $chunkIndex, float $at, int $queueLength) : void{
		$this->chunkEnqueueTimes[self::chunkKey($packId, $chunkIndex)] = $at;
		$this->startedAt ??= $at;
		$this->lastProgressAt = $at;
		$this->maxObservedQueueLength = max($this->maxObservedQueueLength, $queueLength);
	}

	public function markChunkSent(string $packId, int $chunkIndex, int $bytes, float $sentAt) : void{
		$key = self::chunkKey($packId, $chunkIndex);
		$enqueuedAt = $this->chunkEnqueueTimes[$key] ?? $sentAt;
		$this->inflightChunks[$key] = [
			"packId" => $packId,
			"chunkIndex" => $chunkIndex,
			"bytes" => $bytes,
			"enqueuedAt" => $enqueuedAt,
			"sentAt" => $sentAt,
		];
		if(isset($this->packStats[$packId])){
			$this->packStats[$packId]["sentChunks"]++;
			$this->packStats[$packId]["lastSentChunkIndex"] = $chunkIndex;
		}
		$this->maxDispatchDelayMs = max($this->maxDispatchDelayMs, ($sentAt - $enqueuedAt) * 1000);
		$this->maxObservedInflight = max($this->maxObservedInflight, count($this->inflightChunks));
	}

	public function markChunkAcked(string $packId, int $chunkIndex, float $ackAt) : ?float{
		$key = self::chunkKey($packId, $chunkIndex);
		if(!isset($this->inflightChunks[$key])){
			return null;
		}

		$inflight = $this->inflightChunks[$key];
		unset($this->inflightChunks[$key], $this->chunkEnqueueTimes[$key]);

		$rttMs = ($ackAt - $inflight["sentAt"]) * 1000;
		$this->recentAckMs[] = $rttMs;
		if(count($this->recentAckMs) > $this->config->ackSampleWindow){
			array_shift($this->recentAckMs);
		}
		$this->maxAckMs = max($this->maxAckMs, $rttMs);
		$this->totalAckMs += $rttMs;
		$this->ackCount++;
		$this->lastProgressAt = $ackAt;
		$this->lastStallAt = null;
		$this->stallRecoveryMode = false;
		$this->paused = false;

		if(isset($this->packStats[$packId])){
			$this->packStats[$packId]["ackedChunks"]++;
			if($this->packStats[$packId]["requested"]){
				$this->ackedRequestedChunks++;
			}
		}

		$this->adjustWindow();

		return $rttMs;
	}

	public function markChunkFailed(string $packId, int $chunkIndex, float $failedAt, string $reason) : void{
		$key = self::chunkKey($packId, $chunkIndex);
		unset($this->inflightChunks[$key]);

		$this->failureCount++;
		$this->lastFailureReason = $reason;
		$this->lastProgressAt = $failedAt;
		$this->lastStallAt = null;
		$this->stallRecoveryMode = true;
		$this->paused = false;
		$this->windowSize = max(1, $this->windowSize - 1);

		if(isset($this->packStats[$packId])){
			$this->packStats[$packId]["failures"]++;
		}
	}

	/**
	 * @return array{previousWindow: int, currentWindow: int, noProgressMs: float}|null
	 */
	public function detectStall(float $now, int $queueLength) : ?array{
		if($this->startedAt === null || ($queueLength === 0 && count($this->inflightChunks) === 0)){
			return null;
		}

		$stallTimeoutSeconds = $this->config->stallTimeoutMs / 1000;
		if($now < $this->lastProgressAt + $stallTimeoutSeconds){
			return null;
		}
		if($this->lastStallAt !== null && $now < $this->lastStallAt + $stallTimeoutSeconds){
			return null;
		}

		$previousWindow = $this->windowSize;
		$this->windowSize = 1;
		$this->stallRecoveryMode = true;
		$this->paused = count($this->inflightChunks) > 0;
		$this->lastStallAt = $now;
		$this->stallCount++;

		return [
			"previousWindow" => $previousWindow,
			"currentWindow" => $this->windowSize,
			"noProgressMs" => ($now - $this->lastProgressAt) * 1000,
		];
	}

	public function resetTickBudget() : void{
		$chunkBudget = $this->config->maxChunksPerTick;
		if($this->stallRecoveryMode){
			$chunkBudget = min($chunkBudget, 1);
		}
		$this->remainingChunksBudget = $chunkBudget > 0 ? $chunkBudget : PHP_INT_MAX;
		$this->remainingBytesBudget = $this->config->maxBytesPerTick > 0 ? $this->config->maxBytesPerTick : PHP_INT_MAX;
	}

	public function canSendMoreNow() : bool{
		return count($this->inflightChunks) < $this->windowSize && !($this->paused && count($this->inflightChunks) > 0);
	}

	public function consumeTickBudgetIfPossible(int $bytes) : bool{
		if($this->remainingChunksBudget < 1 || $this->remainingBytesBudget < $bytes){
			return false;
		}

		$this->remainingChunksBudget--;
		$this->remainingBytesBudget -= $bytes;

		return true;
	}

	public function getWindowSize() : int{
		return $this->windowSize;
	}

	public function getInflightCount() : int{
		return count($this->inflightChunks);
	}

	public function getAverageAckMs() : float{
		return count($this->recentAckMs) > 0 ? array_sum($this->recentAckMs) / count($this->recentAckMs) : 0.0;
	}

	public function getMaxAckMs() : float{
		return $this->maxAckMs;
	}

	public function getAverageAckMsOverall() : float{
		return $this->ackCount > 0 ? $this->totalAckMs / $this->ackCount : 0.0;
	}

	public function getFailureCount() : int{
		return $this->failureCount;
	}

	public function getStallCount() : int{
		return $this->stallCount;
	}

	public function getLastFailureReason() : ?string{
		return $this->lastFailureReason;
	}

	public function getStartedAt() : ?float{
		return $this->startedAt;
	}

	public function getDurationMs(float $now) : float{
		return $this->startedAt !== null ? ($now - $this->startedAt) * 1000 : 0.0;
	}

	public function getRequestedPackCount() : int{
		return $this->requestedPackCount;
	}

	public function getRequestedTotalBytes() : int{
		return $this->requestedTotalBytes;
	}

	public function getRequestedTotalChunks() : int{
		return $this->requestedTotalChunks;
	}

	public function getAckedRequestedChunks() : int{
		return $this->ackedRequestedChunks;
	}

	public function getMaxDispatchDelayMs() : float{
		return $this->maxDispatchDelayMs;
	}

	public function getMaxObservedInflight() : int{
		return $this->maxObservedInflight;
	}

	public function getMaxObservedWindow() : int{
		return $this->maxObservedWindow;
	}

	public function getMaxObservedQueueLength() : int{
		return $this->maxObservedQueueLength;
	}

	private function adjustWindow() : void{
		$averageAckMs = $this->getAverageAckMs();
		if($averageAckMs <= 0){
			return;
		}

		if($averageAckMs <= $this->config->ackFastThresholdMs){
			$this->windowSize = min($this->config->maxWindow, $this->windowSize + 1);
		}elseif($averageAckMs >= $this->config->ackSlowThresholdMs){
			$this->windowSize = max(1, $this->windowSize - 1);
		}

		$this->maxObservedWindow = max($this->maxObservedWindow, $this->windowSize);
	}

	private static function chunkKey(string $packId, int $chunkIndex) : string{
		return $packId . ":" . $chunkIndex;
	}
}
