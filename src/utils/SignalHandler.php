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

use function function_exists;
use function pcntl_signal;
use function sapi_windows_set_ctrl_handler;
use const PHP_WINDOWS_EVENT_CTRL_BREAK;
use const PHP_WINDOWS_EVENT_CTRL_C;
use const SIG_DFL;
use const SIGHUP;
use const SIGINT;
use const SIGTERM;

final class SignalHandler{
	/** @phpstan-var (\Closure(int) : void)|null */
	private ?\Closure $interruptCallback;

	/**
	 * @phpstan-param \Closure() : void $interruptCallback
	 */
	public function __construct(\Closure $interruptCallback){
		$this->interruptCallback = $interruptCallback;

		if(function_exists('sapi_windows_set_ctrl_handler')){
			sapi_windows_set_ctrl_handler($this->interruptCallback = function(int $signo) use ($interruptCallback) : void{
				if($signo === PHP_WINDOWS_EVENT_CTRL_C || $signo === PHP_WINDOWS_EVENT_CTRL_BREAK){
					$interruptCallback();
				}
			});
		}elseif(function_exists('pcntl_signal')){
			foreach([
				SIGTERM,
				SIGINT,
				SIGHUP
			] as $signal){
				pcntl_signal($signal, $this->interruptCallback = fn(int $signo) => $interruptCallback());
			}
			pcntl_async_signals(true);
		}else{
			//no supported signal handlers :(
		}
	}

	public function unregister() : void{
		if(function_exists('sapi_windows_set_ctrl_handler')){
			sapi_windows_set_ctrl_handler($this->interruptCallback, false);
		}elseif(function_exists('pcntl_signal')){
			foreach([
				SIGTERM,
				SIGINT,
				SIGHUP
			] as $signal){
				pcntl_signal($signal, SIG_DFL);
			}
		}
	}
}
