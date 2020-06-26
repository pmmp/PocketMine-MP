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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\NetworkBinaryStream;

trait TakeOrPlaceStackRequestActionTrait{

	/** @var int */
	private $count;
	/** @var ItemStackRequestSlotInfo */
	private $source;
	/** @var ItemStackRequestSlotInfo */
	private $destination;

	final public function __construct(int $count, ItemStackRequestSlotInfo $source, ItemStackRequestSlotInfo $destination){
		$this->count = $count;
		$this->source = $source;
		$this->destination = $destination;
	}

	final public function getCount() : int{ return $this->count; }

	final public function getSource() : ItemStackRequestSlotInfo{ return $this->source; }

	final public function getDestination() : ItemStackRequestSlotInfo{ return $this->destination; }

	public static function read(NetworkBinaryStream $in) : self{
		$count = $in->getByte();
		$src = ItemStackRequestSlotInfo::read($in);
		$dst = ItemStackRequestSlotInfo::read($in);
		return new self($count, $src, $dst);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->count);
		$this->source->write($out);
		$this->destination->write($out);
	}
}
