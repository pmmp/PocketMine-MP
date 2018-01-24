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

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

class FloatingText extends Entity{

	protected $title;
	protected $text;

	protected function initEntity(){
		parent::initEntity();

		$this->setTitle($this->namedtag["Title"] ?? "");
		$this->setText($this->namedtag["Text"] ?? "");

		$this->setNameTagVisible();
		$this->setNameTagAlwaysVisible();
		$this->setScale(0.01);
	}

	public function getTitle() : string{
		return $this->title;
	}

	public function setTitle(string $title) : void{
		$this->title = $title;

		$this->updateNameTag();
	}

	public function getText() : string{
		return $this->text;
	}

	public function setText(string $text) : void{
		$this->text = $text;

		$this->updateNameTag();
	}

	private function updateNameTag() : void{
		$this->setNameTag($this->title . ($this->text !== "" ? "\n$this->text" : ""));
	}

	public function saveNBT(){
		parent::saveNBT();

		$this->namedtag->Title = new StringTag("Title", $this->title);
		$this->namedtag->Text = new StringTag("Text", $this->text);
	}

	public function onUpdate(int $currentTick) : bool{
		return false; //Don't ever tick FloatingText
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function spawnTo(Player $player){
		$pk = new AddPlayerPacket();
		$pk->uuid = UUID::fromRandom();
		$pk->username = "";
		$pk->entityRuntimeId = $this->id;
		$pk->position = $this->asVector3(); //TODO: check offset
		$pk->item = ItemFactory::get(ItemItem::AIR, 0, 0);
		$pk->metadata = $this->dataProperties;

		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}