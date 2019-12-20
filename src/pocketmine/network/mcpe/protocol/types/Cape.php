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

namespace pocketmine\network\mcpe\protocol\types;

class Cape{
	/** @var string */
	private $id;
	/** @var bool */
	private $onClassicSkin;
	/** @var SkinImage */
	private $image;

	public function __construct(string $id, SkinImage $image, bool $onClassicSkin = false){
		$this->id = $id;
		$this->image = $image;
		$this->onClassicSkin = $onClassicSkin;
	}

	/**
	 * @return SkinImage
	 */
	public function getImage() : SkinImage{
		return $this->image;
	}

	/**
	 * @return string
	 */
	public function getId() : string{
		return $this->id;
	}

	/**
	 * @return bool
	 */
	public function isOnClassicSkin() : bool{
		return $this->onClassicSkin;
	}
}
