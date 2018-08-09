<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\event\server;

use pocketmine\network\NetworkInterface;

class NetworkInterfaceEvent extends ServerEvent{
	/** @var NetworkInterface */
	protected $interface;

	/**
	 * @param NetworkInterface $interface
	 */
	public function __construct(NetworkInterface $interface){
		$this->interface = $interface;
	}

	/**
	 * @return NetworkInterface
	 */
	public function getInterface() : NetworkInterface{
		return $this->interface;
	}
}