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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

class BookEditPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::BOOK_EDIT_PACKET;

	public const TYPE_REPLACE_PAGE = 0;
	public const TYPE_ADD_PAGE = 1;
	public const TYPE_DELETE_PAGE = 2;
	public const TYPE_SWAP_PAGES = 3;
	public const TYPE_SIGN_BOOK = 4;

	/** @var int */
	public $type;
	/** @var int */
	public $inventorySlot;
	/** @var int */
	public $pageNumber;
	/** @var int */
	public $secondaryPageNumber;

	/** @var string */
	public $text;
	/** @var string */
	public $photoName;

	/** @var string */
	public $title;
	/** @var string */
	public $author;
	/** @var string */
	public $xuid;

	protected function decodePayload(PacketSerializer $in) : void{
		$this->type = $in->getByte();
		$this->inventorySlot = $in->getByte();

		switch($this->type){
			case self::TYPE_REPLACE_PAGE:
			case self::TYPE_ADD_PAGE:
				$this->pageNumber = $in->getByte();
				$this->text = $in->getString();
				$this->photoName = $in->getString();
				break;
			case self::TYPE_DELETE_PAGE:
				$this->pageNumber = $in->getByte();
				break;
			case self::TYPE_SWAP_PAGES:
				$this->pageNumber = $in->getByte();
				$this->secondaryPageNumber = $in->getByte();
				break;
			case self::TYPE_SIGN_BOOK:
				$this->title = $in->getString();
				$this->author = $in->getString();
				$this->xuid = $in->getString();
				break;
			default:
				throw new PacketDecodeException("Unknown book edit type $this->type!");
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putByte($this->type);
		$out->putByte($this->inventorySlot);

		switch($this->type){
			case self::TYPE_REPLACE_PAGE:
			case self::TYPE_ADD_PAGE:
				$out->putByte($this->pageNumber);
				$out->putString($this->text);
				$out->putString($this->photoName);
				break;
			case self::TYPE_DELETE_PAGE:
				$out->putByte($this->pageNumber);
				break;
			case self::TYPE_SWAP_PAGES:
				$out->putByte($this->pageNumber);
				$out->putByte($this->secondaryPageNumber);
				break;
			case self::TYPE_SIGN_BOOK:
				$out->putString($this->title);
				$out->putString($this->author);
				$out->putString($this->xuid);
				break;
			default:
				throw new \InvalidArgumentException("Unknown book edit type $this->type!");
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleBookEdit($this);
	}
}
