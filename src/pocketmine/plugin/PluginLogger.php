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

namespace pocketmine\plugin;

use LogLevel;
use pocketmine\Server;
use function spl_object_hash;

class PluginLogger implements \AttachableLogger{

	/** @var string */
	private $pluginName;

	/** @var \LoggerAttachment[] */
	private $attachments = [];

	public function addAttachment(\LoggerAttachment $attachment){
		$this->attachments[spl_object_hash($attachment)] = $attachment;
	}

	public function removeAttachment(\LoggerAttachment $attachment){
		unset($this->attachments[spl_object_hash($attachment)]);
	}

	public function removeAttachments(){
		$this->attachments = [];
	}

	public function getAttachments(){
		return $this->attachments;
	}

	public function __construct(Plugin $context){
		$prefix = $context->getDescription()->getPrefix();
		$this->pluginName = $prefix != null ? "[$prefix] " : "[" . $context->getDescription()->getName() . "] ";
	}

	public function emergency($message){
		$this->log(LogLevel::EMERGENCY, $message);
	}

	public function alert($message){
		$this->log(LogLevel::ALERT, $message);
	}

	public function critical($message){
		$this->log(LogLevel::CRITICAL, $message);
	}

	public function error($message){
		$this->log(LogLevel::ERROR, $message);
	}

	public function warning($message){
		$this->log(LogLevel::WARNING, $message);
	}

	public function notice($message){
		$this->log(LogLevel::NOTICE, $message);
	}

	public function info($message){
		$this->log(LogLevel::INFO, $message);
	}

	public function debug($message){
		$this->log(LogLevel::DEBUG, $message);
	}

	public function logException(\Throwable $e, $trace = null){
		Server::getInstance()->getLogger()->logException($e, $trace);
	}

	public function log($level, $message){
		Server::getInstance()->getLogger()->log($level, $this->pluginName . $message);
		foreach($this->attachments as $attachment){
			$attachment->log($level, $message);
		}
	}
}
