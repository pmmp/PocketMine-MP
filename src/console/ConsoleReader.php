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

namespace pocketmine\console;

use pocketmine\utils\AssumptionFailedError;
use function fclose;
use function fgets;
use function fopen;
use function stream_select;
use function trim;

final class ConsoleReader{
	/** @var resource */
	private $stdin;

	public function __construct(){
		$stdin = fopen("php://stdin", "r");
		if($stdin === false) throw new AssumptionFailedError("Opening stdin should never fail");
		$this->stdin = $stdin;
	}

	/**
	 * Reads a line from the console and adds it to the buffer. This method may block the thread.
	 * @throws ConsoleReaderException
	 */
	public function readLine() : ?string{
		$r = [$this->stdin];
		$w = $e = null;
		if(($count = stream_select($r, $w, $e, 0, 200000)) === 0){ //nothing changed in 200000 microseconds
			return null;
		}elseif($count === false){ //stream error
			throw new ConsoleReaderException("Unexpected EOF on select()");
		}

		if(($raw = fgets($this->stdin)) === false){ //broken pipe or EOF
			throw new ConsoleReaderException("Unexpected EOF on fgets()");
		}

		$line = trim($raw);

		return $line !== "" ? $line : null;
	}

	public function __destruct(){
		fclose($this->stdin);
	}
}
