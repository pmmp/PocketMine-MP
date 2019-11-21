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

class SkinData{

	/** @var string */
	public $skinId;
	/** @var string */
	public $resourcePatch;
	/** @var SkinImage */
	public $skinImage;
	/** @var SkinAnimation[] */
	public $animations;
	/** @var SkinImage */
	public $capeImage;
	/** @var string */
	public $geometryData;
	/** @var string */
	public $animationData;
	/** @var bool */
	public $persona;
	/** @var bool */
	public $premium;
	/** @var bool */
	public $capeOnClassic;
	/** @var string */
	public $capeId;

	public function __construct(string $skinId, string $resourcePatch, SkinImage $skinImage, array $animations = [], SkinImage $capeImage = null, string $geometryData = "", string $animationData = "",  bool $premium = false, bool $persona = false, bool $capeOnClassic = false, string $capeId = ""){
		$this->skinId = $skinId;
		$this->resourcePatch = $resourcePatch;
		$this->skinImage = $skinImage;
		$this->animations = $animations;
		$this->capeImage = $capeImage;
		$this->geometryData = $geometryData;
		$this->animationData = $animationData;
		$this->premium = $premium;
		$this->persona = $persona;
		$this->capeOnClassic = $capeOnClassic;
		$this->capeId = $capeId;
	}
}