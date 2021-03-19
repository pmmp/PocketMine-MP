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

namespace pocketmine\scheduler;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;
use function igbinary_serialize;
use function igbinary_unserialize;

/**
 * Executes a consecutive list of cURL operations.
 *
 * The result of this AsyncTask is an array of arrays (returned from {@link Internet::simpleCurl}) or InternetException objects.
 */
class BulkCurlTask extends AsyncTask{
	private const TLS_KEY_COMPLETION_CALLBACK = "completionCallback";

	/** @var string */
	private $operations;

	/**
	 * BulkCurlTask constructor.
	 *
	 * $operations accepts an array of arrays. Each member array must contain a string mapped to "page", and optionally,
	 * "timeout", "extraHeaders" and "extraOpts". Documentation of these options are same as those in
	 * {@link Internet::simpleCurl}.
	 *
	 * @param BulkCurlTaskOperation[] $operations
	 * @phpstan-param \Closure(list<InternetRequestResult|InternetException> $results) : void $onCompletion
	 */
	public function __construct(array $operations, \Closure $onCompletion){
		$this->operations = igbinary_serialize($operations);
		$this->storeLocal(self::TLS_KEY_COMPLETION_CALLBACK, $onCompletion);
	}

	public function onRun() : void{
		/**
		 * @var BulkCurlTaskOperation[] $operations
		 * @phpstan-var list<BulkCurlTaskOperation> $operations
		 */
		$operations = igbinary_unserialize($this->operations);
		$results = [];
		foreach($operations as $op){
			try{
				$results[] = Internet::simpleCurl($op->getPage(), $op->getTimeout(), $op->getExtraHeaders(), $op->getExtraOpts());
			}catch(InternetException $e){
				$results[] = $e;
			}
		}
		$this->setResult($results);
	}

	public function onCompletion() : void{
		/**
		 * @var \Closure
		 * @phpstan-var \Closure(list<InternetRequestResult|InternetException>) : void
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_COMPLETION_CALLBACK);
		/** @var InternetRequestResult[]|InternetException[] $results */
		$results = $this->getResult();
		$callback($results);
	}
}
