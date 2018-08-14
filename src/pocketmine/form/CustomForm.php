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

use pocketmine\form\element\CustomFormElement;
use pocketmine\Player;
use pocketmine\utils\Utils;

abstract class CustomForm extends BaseForm{

	/** @var CustomFormElement[] */
	private $elements;
	/** @var CustomFormElement[] */
	private $elementMap = [];

	/**
	 * @param string              $title
	 * @param CustomFormElement[] $elements
	 */
	public function __construct(string $title, array $elements){
		assert(Utils::validateObjectArray($elements, CustomFormElement::class));

		parent::__construct($title);
		$this->elements = array_values($elements);
		foreach($this->elements as $element){
			if(isset($this->elements[$element->getName()])){
				throw new \InvalidArgumentException("Multiple elements cannot have the same name, found \"" . $element->getName() . "\" more than once");
			}
			$this->elementMap[$element->getName()] = $element;
		}
	}

	/**
	 * @param int $index
	 *
	 * @return CustomFormElement|null
	 */
	public function getElement(int $index) : ?CustomFormElement{
		return $this->elements[$index] ?? null;
	}

	/**
	 * @param string $name
	 *
	 * @return null|CustomFormElement
	 */
	public function getElementByName(string $name) : ?CustomFormElement{
		return $this->elementMap[$name] ?? null;
	}

	/**
	 * @return CustomFormElement[]
	 */
	public function getAllElements() : array{
		return $this->elements;
	}

	/**
	 * @param Player             $player
	 * @param CustomFormResponse $data
	 *
	 * @return null|Form
	 */
	public function onSubmit(Player $player, CustomFormResponse $data) : ?Form{
		return null;
	}

	/**
	 * Called when a player closes the form without submitting it.
	 *
	 * @param Player $player
	 *
	 * @return Form|null a form which will be opened immediately (before queued forms) as a response to this form, or null if not applicable.
	 */
	public function onClose(Player $player) : ?Form{
		return null;
	}

	public function handleResponse(Player $player, $data) : ?Form{
		if($data === null){
			return $this->onClose($player);
		}

		if(is_array($data)){
			if(($actual = count($data)) !== ($expected = count($this->elements))){
				throw new FormValidationException("Expected $expected result data, got $actual");
			}

			$values = [];

			/** @var array $data */
			foreach($data as $index => $value){
				if(!isset($this->elements[$index])){
					throw new FormValidationException("Element at offset $index does not exist");
				}
				$element = $this->elements[$index];
				try{
					$element->validateValue($value);
				}catch(FormValidationException $e){
					throw new FormValidationException("Validation failed for element \"" . $element->getName() . "\": " . $e->getMessage(), 0, $e);
				}
				$values[$element->getName()] = $value;
			}

			return $this->onSubmit($player, new CustomFormResponse($values));
		}

		throw new FormValidationException("Expected array or null, got " . gettype($data));
	}

	/**
	 * @return string
	 */
	protected function getType() : string{
		return "custom_form";
	}

	protected function serializeFormData() : array{
		return [
			"content" => $this->elements
		];
	}
}
