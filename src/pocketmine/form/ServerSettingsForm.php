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

/**
 * Represents a custom form which can be shown in the Settings menu on the client. This is exactly the same as a regular
 * CustomForm, except that this type can also have an icon which can be shown on the settings section button.
 *
 * Passing this form to {@link Player::sendForm()} will not show a form with an icon nor set this form as the server
 * settings.
 */
abstract class ServerSettingsForm extends CustomForm{
	/**
	 * @var FormIcon|null
	 */
	private $icon;

	public function __construct(string $title, array $elements, ?FormIcon $icon = null){
		parent::__construct($title, $elements);
		$this->icon = $icon;
	}

	public function hasIcon() : bool{
		return $this->icon !== null;
	}

	public function getIcon() : ?FormIcon{
		return $this->icon;
	}

	public function serializeFormData() : array{
		$data = parent::serializeFormData();

		if($this->hasIcon()){
			$data["icon"] = $this->icon;
		}

		return $data;
	}

}