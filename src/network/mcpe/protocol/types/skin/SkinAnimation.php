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

namespace pocketmine\network\mcpe\protocol\types\skin;

class SkinAnimation{

	public const TYPE_HEAD = 1;
	public const TYPE_BODY_32 = 2;
	public const TYPE_BODY_64 = 3;

	public const EXPRESSION_LINEAR = 0; //???
	public const EXPRESSION_BLINKING = 1;

	/** @var SkinImage */
	private $image;
	/** @var int */
	private $type;
	/** @var float */
	private $frames;
	/** @var int */
	private $expressionType;

	public function __construct(SkinImage $image, int $type, float $frames, int $expressionType){
		$this->image = $image;
		$this->type = $type;
		$this->frames = $frames;
		$this->expressionType = $expressionType;
	}

	/**
	 * Image of the animation.
	 */
	public function getImage() : SkinImage{
		return $this->image;
	}

	/**
	 * The type of animation you are applying.
	 */
	public function getType() : int{
		return $this->type;
	}

	/**
	 * The total amount of frames in an animation.
	 */
	public function getFrames() : float{
		return $this->frames;
	}

	public function getExpressionType() : int{ return $this->expressionType; }
}
