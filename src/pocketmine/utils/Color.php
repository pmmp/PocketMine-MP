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


namespace pocketmine\utils;


class Color{

	/** @var int */
	protected $alpha, $red, $green, $blue;

	/**
	 * @param int $r Red balance, 0-255
	 * @param int $g Green balance, 0-255
	 * @param int $b Blue balance, 0-255
	 * @param int $a Alpha value (opacity) 0-255, defaults to 255 (fully opaque)
	 */
	public function __construct(int $r, int $g, int $b, int $a = 0xff){
		$this->red = $r & 0xff;
		$this->green = $g & 0xff;
		$this->blue = $b & 0xff;
		$this->alpha = $a & 0xff;
	}

	/**
	 * Returns the alpha (transparency) value of this colour.
	 * @return int
	 */
	public function getAlpha() : int{
		return $this->alpha;
	}

	/**
	 * Sets the alpha (opacity) value of this colour, lower = more transparent
	 * @param int $a
	 */
	public function setAlpha(int $a){
		$this->alpha = $a & 0xff;
	}

	/**
	 * Retuns the red value of this colour.
	 * @return int
	 */
	public function getRed() : int{
		return $this->red;
	}

	/**
	 * Sets the red value of this colour.
	 * @param int $r
	 */
	public function setRed(int $r){
		$this->red = $r & 0xff;
	}

	/**
	 * Returns the green value of this colour.
	 * @return int
	 */
	public function getGreen() : int{
		return $this->green;
	}

	/**
	 * Sets the green value of this colour.
	 * @param int $g
	 */
	public function setGreen(int $g){
		$this->green = $g & 0xff;
	}

	/**
	 * Returns the blue value of this colour.
	 * @return int
	 */
	public function getBlue() : int{
		return $this->blue;
	}

	/**
	 * Sets the blue value of this colour.
	 * @param int $b
	 */
	public function setBlue(int $b){
		$this->blue = $b & 0xff;
	}

	/**
	 * Mixes the supplied list of colours together to produce a result colour. Used for calculating potion effect bubble colours.
	 *
	 * @param Color[] ...$colors
	 *
	 * @return Color
	 */
	public static function mix(Color ...$colors) : Color{
		if(count($colors) < 1){
			throw new \InvalidArgumentException("No colours given!");
		}
		$alpha = 0;
		$red = 0;
		$green = 0;
		$blue = 0;

		$count = 0;

		foreach($colors as $color){
			$alpha += $color->getAlpha();
			$red += $color->getRed();
			$green += $color->getGreen();
			$blue += $color->getBlue();
			$count++;
		}

		$alpha /= $count;
		$red /= $count;
		$green /= $count;
		$blue /= $count;

		return new Color($red, $green, $blue, $alpha);
	}

	/**
	 * Returns a Color from the supplied RGB colour code (24-bit)
	 * @param int $code
	 *
	 * @return Color
	 */
	public static function fromRGB(int $code){
		return new Color(($code >> 16) & 0xff, ($code >> 8) & 0xff, $code & 0xff);
	}

	public static function fromHtmlRGB(string $code){
		if(strlen($code) < 6){
			throw new \InvalidArgumentException("Expected a HTML RGB color code representation, for example #FF8844");
		}

		return Color::fromRGB(hexdec(substr($code, -6))); //Drop # and leading alpha values
	}

	public function toRGB() : int{
		return 0xff000000 | ($this->red << 16) | ($this->green << 8) | ($this->blue);
	}

	/**
	 * Returns a Color from the supplied ARGB colour code (32-bit)
	 *
	 * @param int $code
	 *
	 * @return Color
	 */
	public static function fromARGB(int $code){
		return new Color(($code >> 16) & 0xff, ($code >> 8) & 0xff, $code & 0xff, ($code >> 24) & 0xff);
	}

	/**
	 * Returns an ARGB big-endian 32-bit colour value.
	 * @return int
	 */
	public function toARGB() : int{
		return ($this->alpha << 24) | ($this->red << 16) | ($this->green << 8) | $this->blue;
	}

	/**
	 * Returns a Color from the supplied RGBA colour code (32-bit)
	 *
	 * @param int $code
	 *
	 * @return Color
	 */
	public static function fromRGBA(int $code){
		return new Color(
			($code >> 24) & 0xff,
			($code >> 16) & 0xff,
			($code >>  8) & 0xff,
			$code & 0xff
		);
	}

	/**
	 * Returns an RGBA big-endian 32-bit colour value.
	 * @return int
	 */
	public function toRGBA() : int{
		return ($this->red << 24) | ($this->green << 16) | ($this->blue << 8) | $this->alpha;
	}

	/**
	 * Returns a Color from the supplied little-endian RGBA colour code.
	 * @param int $code
	 *
	 * @return Color
	 */
	public static function fromLittleEndianRGBA(int $code){
		return new Color($code & 0xff, ($code >> 8) & 0xff, ($code >> 16) & 0xff, ($code >> 24) & 0xff);
	}

	/**
	 * Returns a little-endian RGBA colour value.
	 * @return int
	 */
	public function toLittleEndianRGBA() : int{
		return ($this->alpha << 24) | ($this->blue << 16) | ($this->green << 8) | $this->red;
	}
}