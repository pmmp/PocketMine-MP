<?php

/*
 * PocketMine Standard PHP Library
 * Copyright (C) 2014-2017 PocketMine Team <https://github.com/PocketMine/PocketMine-SPL>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

interface Logger{

	/**
	 * System is unusable
	 *
	 * @param string $message
	 */
	public function emergency($message);

	/**
	 * Action must me taken immediately
	 *
	 * @param string $message
	 */
	public function alert($message);

	/**
	 * Critical conditions
	 *
	 * @param string $message
	 */
	public function critical($message);

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 */
	public function error($message);

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 */
	public function warning($message);

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 */
	public function notice($message);

	/**
	 * Inersting events.
	 *
	 * @param string $message
	 */
	public function info($message);

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 */
	public function debug($message);

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 */
	public function log($level, $message);

	/**
	 * Logs a Throwable object
	 *
	 * @param Throwable $e
	 * @param $trace
	 */
	public function logException(\Throwable $e, $trace = null);
}