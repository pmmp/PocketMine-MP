<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\form;

use pocketmine\Player;
use pocketmine\utils\Utils;

/**
 * This form type presents a menu to the user with a list of options on it. The user may select an option or close the
 * form by clicking the X in the top left corner.
 */
abstract class MenuForm extends Form{

	/** @var string */
	protected $content;
	/** @var MenuOption[] */
	private $options;

	/** @var int|null */
	private $selectedOption;

	/**
	 * @param string       $title
	 * @param string       $text
	 * @param MenuOption[] $options
	 */
	public function __construct(string $title, string $text, array $options){
		assert(Utils::validateObjectArray($options, MenuOption::class));

		parent::__construct($title);
		$this->content = $text;
		$this->options = array_values($options);
	}

	public function getType() : string{
		return Form::TYPE_MENU;
	}

	/**
	 * @return MenuOption[]
	 */
	public function getOptions() : array{
		return $this->options;
	}

	public function getOption(int $position) : ?MenuOption{
		return $this->options[$position] ?? null;
	}

	/**
	 * Returns the index of the option selected by the user.
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
	 * @return MenuOption
	 * @throws \InvalidStateException if no option is selected or if the selected option doesn't exist
	 */
	public function getSelectedOption() : MenuOption{
		$index = $this->getSelectedOptionIndex();
		if($index === null){
			throw new \InvalidStateException("No option selected (form closed or hasn't been submitted yet)");
		}

		$option = $this->getOption($index);

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
	public function onSubmit(Player $player) : ?Form{
		return null;
	}

	/**
	 * Called when a player clicks the close button on this form without selecting an option.
	 * @param Player $player
	 * @return Form|null a form which will be opened immediately (before queued forms) as a response to this form, or null if not applicable.
	 */
	public function onClose(Player $player) : ?Form{
		return null;
	}

	public function clearResponseData() : void{
		$this->selectedOption = null;
	}

	final public function handleResponse(Player $player, $data) : ?Form{
		if($data === null){
			return $this->onClose($player);
		}

		if(is_int($data)){
			if(!isset($this->options[$data])){
				throw new \RuntimeException($player->getName() . " selected an option that doesn't seem to exist ($data)");
			}
			$this->setSelectedOptionIndex($data);
			return $this->onSubmit($player);
		}

		throw new \UnexpectedValueException("Expected int or NULL, got " . gettype($data));
	}

	public function serializeFormData() : array{
		return [
			"content" => $this->content,
			"buttons" => $this->options //yes, this is intended (MCPE calls them buttons)
		];
	}
}