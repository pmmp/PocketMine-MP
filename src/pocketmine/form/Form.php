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

namespace pocketmine\form;

use pocketmine\Player;

abstract class Form implements \JsonSerializable{

	const TYPE_MODAL = "modal";
	const TYPE_LIST = "form";
	const TYPE_CUSTOM_FORM = "custom_form";

	/**
	 * Returns the type used to show this form to clients
	 * @return string
	 */
	abstract public function getType() : string;

	/**
	 * Handles a modal form response from a player
	 *
	 * @param Player $player
	 * @param mixed  $data
	 */
	abstract public function handleResponse(Player $player, $data) : void;

	/**
	 * Serializes the form to JSON for sending to clients.
	 *
	 * @return array
	 */
	final public function jsonSerialize() : array{
		$jsonBase = [
			"type" => $this->getType()
		];

		return array_merge($jsonBase, $this->serializeFormData());
	}

	/**
	 * Serializes additional data needed to show this form to clients.
	 * @return array
	 */
	abstract protected function serializeFormData() : array;

}