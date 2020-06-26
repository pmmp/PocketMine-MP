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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

/**
 * Drops some (or all) items from the source slot into the world as an item entity.
 */
final class DropStackRequestAction extends ItemStackRequestAction{

	/** @var int */
	private $count;
	/** @var ItemStackRequestSlotInfo */
	private $source;
	/** @var bool */
	private $randomly;

	public function __construct(int $count, ItemStackRequestSlotInfo $source, bool $randomly){
		$this->count = $count;
		$this->source = $source;
		$this->randomly = $randomly;
	}

	public function getCount() : int{ return $this->count; }

	public function getSource() : ItemStackRequestSlotInfo{ return $this->source; }

	public function isRandomly() : bool{ return $this->randomly; }

	public static function getTypeId() : int{ return ItemStackRequestActionType::DROP; }

	public static function read(PacketSerializer $in) : self{
		$count = $in->getByte();
		$source = ItemStackRequestSlotInfo::read($in);
		$random = $in->getBool();
		return new self($count, $source, $random);
	}

	public function write(PacketSerializer $out) : void{
		$out->putByte($this->count);
		$this->source->write($out);
		$out->putBool($this->randomly);
	}
}
