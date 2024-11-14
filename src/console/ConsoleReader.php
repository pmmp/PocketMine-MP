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

use pocketmine\utils\Utils;
use function fclose;
use function fgets;
use function fopen;
use function is_resource;
use function stream_select;
use function trim;
use function usleep;

final class ConsoleReader{
	/** @var resource */
	private $stdin;

	public function __construct(){
		$this->initStdin();
	}

	private function initStdin() : void{
		if(is_resource($this->stdin)){
			fclose($this->stdin);
		}

		$this->stdin = Utils::assumeNotFalse(fopen("php://stdin", "r"), "Opening stdin should never fail");
	}

	/**
	 * Reads a line from the console and adds it to the buffer. This method may block the thread.
	 */
	public function readLine() : ?string{
		if(!is_resource($this->stdin)){
			$this->initStdin();
		}

		$r = [$this->stdin];
		$w = $e = null;
		if(($count = stream_select($r, $w, $e, 0, 200000)) === 0){ //nothing changed in 200000 microseconds
			return null;
		}elseif($count === false){ //stream error
			return null;
		}

		if(($raw = fgets($this->stdin)) === false){ //broken pipe or EOF
			usleep(200000); //prevent CPU waste if it's end of pipe
			return null; //loop back round
		}

		$line = trim($raw);

		return $line !== "" ? $line : null;
	}

	public function __destruct(){
		fclose($this->stdin);
	}
}
