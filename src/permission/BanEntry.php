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

use function strtotime;
use function time;

class BanEntry{
	protected const DATE_FORMAT_STRING = "m/d/Y H:i:s";

	private string $name;
	private ?string $source;
	private ?string $reason;
	private int $creationTimestamp;
	private ?int $expirationTimestamp;

	public function __construct(string $name, ?string $source = null, ?string $reason = null, ?int $creationTimestamp = null, ?int $expirationTimestamp = null){
		$this->name = $name;
		$this->source = $source;
		$this->reason = $reason;
		$this->creationTimestamp = $creationTimestamp ?? time();
		$this->expirationTimestamp = $expirationTimestamp;
	}

	public function getSource() : ?string{
		return $this->source;
	}

	public function isExpired(): bool{
		$now = time();
		return !$this->isPermanent() && $this->getExpirationTimestamp() < $now;
	}

	public function isPermanent(): bool{
		return $this->getExpirationTimestamp() === null;
	}

	public function getCreationTimestamp() : ?int{
		return $this->creationTimestamp;
	}

	public function getExpirationTimestamp() : ?int{
		return $this->expirationTimestamp;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getReason() : ?string{
		return $this->reason;
	}

	public static function isValidDateString(string $dateString, ?int $baseTimestamp = null): bool{
		return strtotime($dateString, $baseTimestamp) !== false;
	}

	public function setCreationTimestamp(int $creationTimestamp) : void{
		$this->creationTimestamp = $creationTimestamp;
	}

	public function setExpirationTimestamp(?int $expirationTimestamp) : void{
		$this->expirationTimestamp = $expirationTimestamp;
	}

	public function setName(string $name) : void{
		$this->name = $name;
	}

	public function setReason(?string $reason) : void{
		$this->reason = $reason;
	}

	public function setSource(?string $source) : void{
		$this->source = $source;
	}

	public function toString(): string{
		$str = $this->getName();
		$str .= "|";
		$str .= $this->getSource();
		$str .= "|";
		$str .= $this->getReason();
		$str .= "|";
		$str .= date(self::DATE_FORMAT_STRING, $this->getCreationTimestamp());
		$str .= "|";
		$str .= $this->isPermanent() ? "Permanent" : date(self::DATE_FORMAT_STRING, $this->getExpirationTimestamp());

		return $str;
	}
}
