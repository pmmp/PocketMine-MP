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
 * This form type presents a simple "yes/no" dialog with two buttons.
 */
abstract class ModalForm extends Form{

	/** @var string */
	private $content;
	/** @var string */
	private $button1;
	/** @var string */
	private $button2;

	/** @var bool|null */
	private $choice;

	/**
	 * @param string $title Text to put on the title of the dialog.
	 * @param string $text Text to put in the body.
	 * @param string $yesButtonText Text to show on the "Yes" button. Defaults to client-translated "Yes" string.
	 * @param string $noButtonText Text to show on the "No" button. Defaults to client-translated "No" string.
	 */
	public function __construct(string $title, string $text, string $yesButtonText = "gui.yes", string $noButtonText = "gui.no"){
		parent::__construct($title);
		$this->content = $text;
		$this->button1 = $yesButtonText;
		$this->button2 = $noButtonText;
	}

	public function getType() : string{
		return self::TYPE_MODAL;
	}

	public function getYesButtonText() : string{
		return $this->button1;
	}

	public function getNoButtonText() : string{
		return $this->button2;
	}

	/**
	 * If called from {@link onSubmit} this will return true if the user chose Yes, or false if they chose No.
	 *
	 * Will return null if called before the form is submitted.
	 *
	 * @return bool|null
	 */
	public function getChoice() : ?bool{
		return $this->choice;
	}

	/**
	 * Sets the option selected by the player. true = Yes, false = No
	 *
	 * @param bool $choice
	 */
	public function setChoice(bool $choice) : void{
		$this->choice = $choice;
	}

	/**
	 * {@inheritdoc}
	 *
	 * {@link getChoice} can be used in here to find out which option the player selected.
	 */
	public function onSubmit(Player $player) : ?Form{
		return null;
	}

	public function clearResponseData() : void{
		$this->choice = null;
	}


	final public function handleResponse(Player $player, $data) : ?Form{
		if(!is_bool($data)){
			throw new \UnexpectedValueException("Expected bool, got " . gettype($data));
		}

		$this->setChoice($data);
		return $this->onSubmit($player);
	}

	public function serializeFormData() : array{
		return [
			"content" => $this->content,
			"button1" => $this->button1,
			"button2" => $this->button2
		];
	}

}
