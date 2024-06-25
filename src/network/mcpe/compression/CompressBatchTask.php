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

namespace pocketmine\network\mcpe\compression;

use pocketmine\scheduler\AsyncTask;
use pocketmine\thread\NonThreadSafeValue;
use function chr;

class CompressBatchTask extends AsyncTask{

	private const TLS_KEY_PROMISE = "promise";

	/** @phpstan-var NonThreadSafeValue<Compressor> */
	private NonThreadSafeValue $compressor;

	public function __construct(
		private string $data,
		CompressBatchPromise $promise,
		Compressor $compressor
	){
		$this->compressor = new NonThreadSafeValue($compressor);
		$this->storeLocal(self::TLS_KEY_PROMISE, $promise);
	}

	public function onRun() : void{
		$compressor = $this->compressor->deserialize();
		$this->setResult(chr($compressor->getNetworkId()) . $compressor->compress($this->data));
	}

	public function onCompletion() : void{
		/** @var CompressBatchPromise $promise */
		$promise = $this->fetchLocal(self::TLS_KEY_PROMISE);
		$promise->resolve($this->getResult());
	}
}
