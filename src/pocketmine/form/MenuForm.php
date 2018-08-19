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

/**
 * This form type presents a menu to the user with a list of options on it. The user may select an option or close the
 * form by clicking the X in the top left corner.
 */
abstract class MenuForm extends BaseForm{

	/** @var string */
	protected $content;
	/** @var MenuOption[] */
	private $options;

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

	public function getOption(int $position) : ?MenuOption{
		return $this->options[$position] ?? null;
	}

	/**
	 * @param Player $player Player submitting this form.
	 * @param int    $selectedOption Selected option, can be used with getOption().
	 */
	public function onSubmit(Player $player, int $selectedOption) : void{

	}

	/**
	 * Called when a player clicks the close button on this form without selecting an option.
	 *
	 * @param Player $player
	 */
	public function onClose(Player $player) : void{

	}

	final public function handleResponse(Player $player, $data) : void{
		if($data === null){
			$this->onClose($player);
		}elseif(is_int($data)){
			if(!isset($this->options[$data])){
				throw new FormValidationException("Option $data does not exist");
			}
			$this->onSubmit($player, $data);
		}else{
			throw new FormValidationException("Expected int or null, got " . gettype($data));
		}
	}

	protected function getType() : string{
		return "form";
	}

	protected function serializeFormData() : array{
		return [
			"content" => $this->content,
			"buttons" => $this->options //yes, this is intended (MCPE calls them buttons)
		];
	}
}
