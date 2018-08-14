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

/**
 * API for Minecraft: Bedrock custom UI (forms)
 */
namespace pocketmine\form;

/**
 * Base class for a custom form. Forms are serialized to JSON data to be sent to clients.
 */
abstract class BaseForm implements Form{

	/** @var string */
	protected $title;

	public function __construct(string $title){
		$this->title = $title;
	}

	/**
	 * Returns the text shown on the form title-bar.
	 * @return string
	 */
	public function getTitle() : string{
		return $this->title;
	}

	/**
	 * Serializes the form to JSON for sending to clients.
	 *
	 * @return array
	 */
	final public function jsonSerialize() : array{
		$ret = $this->serializeFormData();
		$ret["type"] = $this->getType();
		$ret["title"] = $this->getTitle();

		return $ret;
	}

	/**
	 * Returns the type used to show this form to clients
	 * @return string
	 */
	abstract protected function getType() : string;

	/**
	 * Serializes additional data needed to show this form to clients.
	 * @return array
	 */
	abstract protected function serializeFormData() : array;

}
