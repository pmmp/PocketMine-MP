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

abstract class ThreadedLoggerAttachment extends \Threaded{

	/** @var \ThreadedLoggerAttachment */
	protected $attachment = null;

	/**
	 * @param string $message
	 */
	protected abstract function log($message);

	/**
	 * @param string $message
	 */
	public final function call($message){
		$this->log($message);
		if($this->attachment instanceof \ThreadedLoggerAttachment){
			$this->attachment->call($message);
		}
	}

	/**
	 * @param ThreadedLoggerAttachment $attachment
	 */
	public function addAttachment(\ThreadedLoggerAttachment $attachment){
		if($this->attachment instanceof \ThreadedLoggerAttachment){
			$this->attachment->addAttachment($attachment);
		}else{
			$this->attachment = $attachment;
		}
	}

	/**
	 * @param ThreadedLoggerAttachment $attachment
	 */
	public function removeAttachment(\ThreadedLoggerAttachment $attachment){
		if($this->attachment instanceof \ThreadedLoggerAttachment){
			if($this->attachment === $attachment){
				$this->attachment = null;
				foreach($attachment->getAttachments() as $attachment){
					$this->addAttachment($attachment);
				}
			}
		}
	}

	public function removeAttachments(){
		if($this->attachment instanceof \ThreadedLoggerAttachment){
			$this->attachment->removeAttachments();
			$this->attachment = null;
		}
	}

	/**
	 * @return \ThreadedLoggerAttachment[]
	 */
	public function getAttachments(){
		$attachments = [];
		if($this->attachment instanceof \ThreadedLoggerAttachment){
			$attachments[] = $this->attachment;
			$attachments += $this->attachment->getAttachments();
		}
		return $attachments;
	}
}