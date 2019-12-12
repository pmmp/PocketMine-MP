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

namespace pocketmine\permission;

use function array_shift;
use function explode;
use function implode;
use function strlen;
use function strtolower;
use function trim;

class BanEntry{
	/**
	 * @var string
	 */
	public static $format = "Y-m-d H:i:s O";

	/** @var string */
	private $name;
	/** @var \DateTime */
	private $creationDate = null;
	/** @var string */
	private $source = "(Unknown)";
	/** @var \DateTime|null */
	private $expirationDate = null;
	/** @var string */
	private $reason = "Banned by an operator.";

	public function __construct(string $name){
		$this->name = strtolower($name);
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->creationDate = new \DateTime();
	}

	public function getName() : string{
		return $this->name;
	}

	public function getCreated() : \DateTime{
		return $this->creationDate;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setCreated(\DateTime $date) : void{
		self::validateDate($date);
		$this->creationDate = $date;
	}

	public function getSource() : string{
		return $this->source;
	}

	public function setSource(string $source) : void{
		$this->source = $source;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getExpires() : ?\DateTime{
		return $this->expirationDate;
	}

	/**
	 * @param \DateTime|null $date
	 * @throws \InvalidArgumentException
	 */
	public function setExpires(?\DateTime $date) : void{
		if($date !== null){
			self::validateDate($date);
		}
		$this->expirationDate = $date;
	}

	public function hasExpired() : bool{
		/** @noinspection PhpUnhandledExceptionInspection */
		$now = new \DateTime();

		return $this->expirationDate === null ? false : $this->expirationDate < $now;
	}

	public function getReason() : string{
		return $this->reason;
	}

	public function setReason(string $reason) : void{
		$this->reason = $reason;
	}

	public function getString() : string{
		$str = "";
		$str .= $this->getName();
		$str .= "|";
		$str .= $this->getCreated()->format(self::$format);
		$str .= "|";
		$str .= $this->getSource();
		$str .= "|";
		$str .= $this->getExpires() === null ? "Forever" : $this->getExpires()->format(self::$format);
		$str .= "|";
		$str .= $this->getReason();

		return $str;
	}

	/**
	 * Hacky function to validate \DateTime objects due to a bug in PHP. format() with "Y" can emit years with more than
	 * 4 digits, but createFromFormat() with "Y" doesn't accept them if they have more than 4 digits on the year.
	 *
	 * @link https://bugs.php.net/bug.php?id=75992
	 *
	 * @param \DateTime $dateTime
	 *
	 * @throws \InvalidArgumentException if the argument can't be parsed from a formatted date string
	 */
	private static function validateDate(\DateTime $dateTime) : void{
		try{
			self::parseDate($dateTime->format(self::$format));
		}catch(\RuntimeException $e){
			throw new \InvalidArgumentException($e->getMessage(), 0, $e);
		}
	}

	/**
	 * @param string $date
	 *
	 * @return \DateTime
	 * @throws \RuntimeException
	 */
	private static function parseDate(string $date) : \DateTime{
		$datetime = \DateTime::createFromFormat(self::$format, $date);
		if(!($datetime instanceof \DateTime)){
			throw new \RuntimeException("Corrupted date/time: " . implode(", ", \DateTime::getLastErrors()["errors"]));
		}

		return $datetime;
	}

	/**
	 * @param string $str
	 *
	 * @return BanEntry|null
	 * @throws \RuntimeException
	 */
	public static function fromString(string $str) : ?BanEntry{
		if(strlen($str) < 2){
			return null;
		}

		$parts = explode("|", trim($str));
		$entry = new BanEntry(trim(array_shift($parts)));
		if(!empty($parts)){
			$entry->setCreated(self::parseDate(array_shift($parts)));
		}
		if(!empty($parts)){
			$entry->setSource(trim(array_shift($parts)));
		}
		if(!empty($parts)){
			$expire = trim(array_shift($parts));
			if($expire !== "" and strtolower($expire) !== "forever"){
				$entry->setExpires(self::parseDate($expire));
			}
		}
		if(!empty($parts)){
			$entry->setReason(trim(array_shift($parts)));
		}

		return $entry;
	}
}
