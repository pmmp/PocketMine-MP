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

/**
 * This form type presents a menu to the user with a list of options on it. The user may select an option or close the
 * form by clicking the X in the top left corner.
 */
abstract class ListForm extends Form{

	/** @var string */
	protected $content;
	/** @var Button[] */
	private $buttons;

	/** @var int|null */
	private $selectedOption;

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

	/**
	 * Returns the index of the option selected by the user. Pass this to {@link getButton} to get the button object
	 * which was clicked.
	 *
	 * @return int|null
	 */
	public function getSelectedOptionIndex() : ?int{
		return $this->selectedOption;
	}

	/**
	 * Sets the selected option to the specified index or null. null = no selection.
	 * @param int $option
	 */
	public function setSelectedOptionIndex(int $option) : void{
		$this->selectedOption = $option;
	}

	/**
	 * Returns the menu option selected by the user.
	 *
	 * @return Button
	 * @throws \InvalidStateException if no option is selected or if the selected option doesn't exist
	 */
	public function getSelectedOption() : Button{
		$index = $this->getSelectedOptionIndex();
		if($index === null){
			throw new \InvalidStateException("No option selected, maybe the form hasn't been submitted yet");
		}

		$option = $this->getButton($index);

		if($option !== null){
			return $option;
		}

		throw new \InvalidStateException("No option found at index $index");
	}

	/**
	 * {@inheritdoc}
	 *
	 * {@link getSelectedOption} can be used to get the option selected by the user.
	 */
	public function onSubmit(Player $player) : void{

	}

	/**
	 * Called when a player clicks the close button on this form without selecting an option.
	 * @param Player $player
	 */
	public function onClose(Player $player) : void{

	}

	public function clearResponseData() : void{
		$this->selectedOption = null;
	}


	final public function handleResponse(Player $player, $data) : void{
		if($data === null){
			$this->onClose($player);
		}elseif(is_int($data)){
			if(!isset($this->buttons[$data])){
				throw new \RuntimeException($player->getName() . " selected an option that doesn't seem to exist ($data)");
			}
			$this->setSelectedOptionIndex($data);
			$this->onSubmit($player);
		}else{
			throw new \UnexpectedValueException("Expected int or NULL, got " . gettype($data));
		}
	}

	public function serializeFormData() : array{
		return [
			"content" => $this->content,
			"buttons" => $this->buttons
		];
	}
}