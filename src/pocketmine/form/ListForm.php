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

namespace pocketmine\form;

use pocketmine\Player;

abstract class ListForm extends Form{

	/** @var string */
	protected $content;
	/** @var Button[] */
	private $buttons;

	/**
	 * @param string   $title
	 * @param string   $text
	 * @param Button[] $buttons
	 */
	public function __construct(string $title, string $text, Button ...$buttons){
		parent::__construct($title);
		$this->content = $text;
		$this->buttons = $buttons;
	}

	public function getType() : string{
		return Form::TYPE_LIST;
	}

	public function getButton(int $position) : ?Button{
		return $this->buttons[$position] ?? null;
	}

	public function handleResponse(Player $player, $data) : void{
		if($data === null){
			$this->onClose($player);
		}elseif(is_int($data)){
			if(!isset($this->buttons[$data])){
				throw new \RuntimeException($player->getName() . " selected an option that doesn't seem to exist ($data)");
			}
			$this->onSubmit($player, $data);
		}else{
			throw new \UnexpectedValueException("Expected int or NULL, got " . gettype($data));
		}
	}

	/**
	 * Called when a player clicks the close button on this form without selecting an option.
	 * @param Player $player
	 */
	public function onClose(Player $player) : void{

	}

	/**
	 * Called when a player selects an option from the menu.
	 *
	 * @param Player $player The player submitting the form
	 * @param int    $selectedOption The index of the selected button. Use {@link #getButton} with this number to get
	 *                                the clicked button object.
	 */
	abstract public function onSubmit(Player $player, int $selectedOption) : void;

	public function serializeFormData() : array{
		return [
			"content" => $this->content,
			"buttons" => $this->buttons
		];
	}
}