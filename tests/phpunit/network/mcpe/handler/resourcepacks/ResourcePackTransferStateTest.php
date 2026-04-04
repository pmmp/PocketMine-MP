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

use PHPUnit\Framework\TestCase;

final class ResourcePackTransferStateTest extends TestCase{
	private const PACK_ID = "00000000-0000-0000-0000-000000000000";

	public function testWindowExpandsAndShrinksFromAckFeedback() : void{
		$state = $this->createState();

		for($i = 0; $i < 3; ++$i){
			$this->sendAndAck($state, $i, $i * 1.0, 0.10);
		}
		self::assertSame(4, $state->getWindowSize());

		for($i = 3; $i < 11; ++$i){
			$this->sendAndAck($state, $i, $i * 1.0, 1.20);
		}
		self::assertSame(1, $state->getWindowSize());
		self::assertGreaterThan(800.0, $state->getAverageAckMs());
	}

	public function testTickBudgetHonoursConfiguredLimits() : void{
		$config = new ResourcePackTransferConfig(
			enabled: true,
			initialWindow: 1,
			maxWindow: 4,
			maxChunksPerTick: 2,
			maxBytesPerTick: 300,
			stallTimeoutMs: 10_000,
			ackFastThresholdMs: 150,
			ackSlowThresholdMs: 800,
			debugLog: false
		);
		$state = $this->createState($config);

		self::assertTrue($state->consumeTickBudgetIfPossible(100));
		self::assertTrue($state->consumeTickBudgetIfPossible(100));
		self::assertFalse($state->consumeTickBudgetIfPossible(100));

		$state->resetTickBudget();
		self::assertTrue($state->consumeTickBudgetIfPossible(200));
		self::assertFalse($state->consumeTickBudgetIfPossible(150));
	}

	public function testStallRecoveryShrinksWindowAndTemporarilyForcesSingleChunkBudget() : void{
		$state = $this->createState();
		$this->sendAndAck($state, 0, 0.0, 0.10);
		$this->sendAndAck($state, 1, 1.0, 0.10);
		self::assertSame(3, $state->getWindowSize());

		$state->markChunkEnqueued(self::PACK_ID, 2, 2.0, 1);
		$state->markChunkSent(self::PACK_ID, 2, 100, 2.0);
		$stall = $state->detectStall(12.1, 0);
		self::assertNotNull($stall);
		self::assertSame(1, $state->getWindowSize());

		$state->resetTickBudget();
		self::assertTrue($state->consumeTickBudgetIfPossible(100));
		self::assertFalse($state->consumeTickBudgetIfPossible(100));

		$state->markChunkAcked(self::PACK_ID, 2, 12.2);
		$state->resetTickBudget();
		self::assertTrue($state->consumeTickBudgetIfPossible(100));
		self::assertTrue($state->consumeTickBudgetIfPossible(100));
	}

	public function testFailuresReleaseInflightAndRecordReason() : void{
		$state = $this->createState();
		$state->markChunkEnqueued(self::PACK_ID, 0, 0.0, 1);
		$state->markChunkSent(self::PACK_ID, 0, 100, 0.0);
		$state->markChunkFailed(self::PACK_ID, 0, 0.5, "send rejected before ACK");

		self::assertSame(0, $state->getInflightCount());
		self::assertSame(1, $state->getFailureCount());
		self::assertSame("send rejected before ACK", $state->getLastFailureReason());
		self::assertTrue($state->canSendMoreNow());
	}

	private function createState(?ResourcePackTransferConfig $config = null) : ResourcePackTransferState{
		$config ??= new ResourcePackTransferConfig(
			enabled: true,
			initialWindow: 1,
			maxWindow: 4,
			maxChunksPerTick: 3,
			maxBytesPerTick: 1024 * 1024,
			stallTimeoutMs: 10_000,
			ackFastThresholdMs: 150,
			ackSlowThresholdMs: 800,
			debugLog: false
		);

		$state = new ResourcePackTransferState($config, [
			self::PACK_ID => [
				"totalChunks" => 16,
				"sizeBytes" => 16 * 256 * 1024,
			],
		], 0.0);
		$state->markRequestedPack(self::PACK_ID, 0.0);

		return $state;
	}

	private function sendAndAck(ResourcePackTransferState $state, int $chunkIndex, float $sentAt, float $rttSeconds) : void{
		$state->markChunkEnqueued(self::PACK_ID, $chunkIndex, $sentAt, 1);
		$state->markChunkSent(self::PACK_ID, $chunkIndex, 100, $sentAt);
		$state->markChunkAcked(self::PACK_ID, $chunkIndex, $sentAt + $rttSeconds);
	}
}
