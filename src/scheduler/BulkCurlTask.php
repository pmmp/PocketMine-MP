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
use function igbinary_serialize;
use function igbinary_unserialize;

/**
 * Executes a consecutive list of cURL operations.
 *
 * The result of this AsyncTask is an array of arrays (returned from {@link Internet::simpleCurl}) or InternetException objects.
 */
class BulkCurlTask extends AsyncTask{
	/** @var string */
	private $operations;

	/**
	 * BulkCurlTask constructor.
	 *
	 * $operations accepts an array of arrays. Each member array must contain a string mapped to "page", and optionally,
	 * "timeout", "extraHeaders" and "extraOpts". Documentation of these options are same as those in
	 * {@link Utils::simpleCurl}.
	 *
	 * @param BulkCurlTaskOperation[]  $operations
	 * @phpstan-param list<BulkCurlTaskOperation> $operations
	 */
	public function __construct(array $operations){
		$this->operations = igbinary_serialize($operations);
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
}
