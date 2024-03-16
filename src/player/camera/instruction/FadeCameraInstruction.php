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

namespace pocketmine\player\camera\instruction;

use pocketmine\color\Color;
use pocketmine\nbt\tag\CompoundTag;

class FadeCameraInstruction implements CameraInstruction{

	private const TAG_FADE = "fade"; //TAG_Compound

	private const TAG_COLOR = "color"; //TAG_Compound
	private const TAG_COLOR_R = "r"; //TAG_Float

	/** TODO: Blue and green are swapped... */
	private const TAG_COLOR_G = "b"; //TAG_Float
	private const TAG_COLOR_B = "g"; //TAG_Float

	private const TAG_TIME = "time"; //TAG_Compound
	private const TAG_FADE_IN = "fadeIn"; //TAG_Float
	private const TAG_HOLD = "hold"; //TAG_Float
	private const TAG_FADE_OUT = "fadeOut"; //TAG_Float

	public function __construct(
		private ?Color $color = null,
		private float $fadeInSeconds = 1,
		private float $holdSeconds = 0.5,
		private float $fadeOutSeconds = 0.5
	) {
	}

	public function writeInstructionData(CompoundTag $tag) : void{
		$fadeTag = CompoundTag::create();
		if ($this->color !== null) {
			$fadeTag->setTag(self::TAG_COLOR, CompoundTag::create()
				->setFloat(self::TAG_COLOR_R, $this->color->getR() / 255)
				->setFloat(self::TAG_COLOR_G, $this->color->getG() / 255)
				->setFloat(self::TAG_COLOR_B, $this->color->getB() / 255)
				//doesn't support alpha (opacity) :(
			);
		}

		$fadeTag->setTag(self::TAG_TIME, CompoundTag::create()
			->setFloat(self::TAG_FADE_IN, $this->fadeInSeconds)
			->setFloat(self::TAG_HOLD, $this->holdSeconds)
			->setFloat(self::TAG_FADE_OUT, $this->fadeOutSeconds)
		);

		$tag->setTag(self::TAG_FADE, $fadeTag);
	}
}
