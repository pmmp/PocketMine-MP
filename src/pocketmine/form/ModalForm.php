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
use pocketmine\utils\Utils;

use function is_bool;

/**
 * This form type presents a simple "yes/no" dialog with two buttons.
 */
abstract class ModalForm extends BaseForm{

	/** @var string */
	private $content;
	/** @var \Closure */
	private $onSubmit;
	/** @var string */
	private $button1;
	/** @var string */
	private $button2;

	/**
	 * @param string   $title Text to put on the title of the dialog.
	 * @param string   $text Text to put in the body.
	 * @param \Closure $onSubmit signature `function(Player $player, bool $choice)`
	 * @param string   $yesButtonText Text to show on the "Yes" button. Defaults to client-translated "Yes" string.
	 * @param string   $noButtonText Text to show on the "No" button. Defaults to client-translated "No" string.
	 */
	public function __construct(string $title, string $text, \Closure $onSubmit, string $yesButtonText = "gui.yes", string $noButtonText = "gui.no"){
		parent::__construct($title);
		$this->content = $text;
		Utils::validateCallableSignature(function(Player $player, bool $choice) : void{}, $onSubmit);
		$this->onSubmit = $onSubmit;
		$this->button1 = $yesButtonText;
		$this->button2 = $noButtonText;
	}

	public function getYesButtonText() : string{
		return $this->button1;
	}

	public function getNoButtonText() : string{
		return $this->button2;
	}

	final public function handleResponse(Player $player, $data) : void{
		if(!is_bool($data)){
			throw new FormValidationException("Expected bool, got " . gettype($data));
		}

		($this->onSubmit)($player, $data);
	}

	protected function getType() : string{
		return "modal";
	}

	protected function serializeFormData() : array{
		return [
			"content" => $this->content,
			"button1" => $this->button1,
			"button2" => $this->button2
		];
	}
}
