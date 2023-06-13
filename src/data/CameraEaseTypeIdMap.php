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

namespace pocketmine\data\bedrock;

use pocketmine\player\camera\CameraEaseType;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;

final class CameraEaseTypeIdMap{
	use SingletonTrait;

	/**
	 * @var CameraEaseType[]
	 * @phpstan-var array<string, CameraEaseType>
	 */
	private array $idToEnum = [];
	/**
	 * @var string[]
	 * @phpstan-var array<int, string>
	 */
	private array $enumToId = [];

	public function __construct(){
		$this->register("linear", CameraEaseType::LINEAR());
		$this->register("spring", CameraEaseType::SPRING());
		$this->register("in_quad", CameraEaseType::IN_QUAD());
		$this->register("out_quad", CameraEaseType::OUT_QUAD());
		$this->register("in_out_quad", CameraEaseType::IN_OUT_QUAD());
		$this->register("in_cubic", CameraEaseType::IN_CUBIC());
		$this->register("out_cubic", CameraEaseType::OUT_CUBIC());
		$this->register("in_out_cubic", CameraEaseType::IN_OUT_CUBIC());
		$this->register("in_quart", CameraEaseType::IN_QUART());
		$this->register("out_quart", CameraEaseType::OUT_QUART());
		$this->register("in_out_quart", CameraEaseType::IN_OUT_QUART());
		$this->register("in_quint", CameraEaseType::IN_QUINT());
		$this->register("out_quint", CameraEaseType::OUT_QUINT());
		$this->register("in_out_quint", CameraEaseType::IN_OUT_QUINT());
		$this->register("in_sine", CameraEaseType::IN_SINE());
		$this->register("out_sine", CameraEaseType::OUT_SINE());
		$this->register("in_out_sine", CameraEaseType::IN_OUT_SINE());
		$this->register("in_expo", CameraEaseType::IN_EXPO());
		$this->register("out_expo", CameraEaseType::OUT_EXPO());
		$this->register("in_out_expo", CameraEaseType::IN_OUT_EXPO());
		$this->register("in_circ", CameraEaseType::IN_CIRC());
		$this->register("out_circ", CameraEaseType::OUT_CIRC());
		$this->register("in_out_circ", CameraEaseType::IN_OUT_CIRC());
		$this->register("in_bounce", CameraEaseType::IN_BOUNCE());
		$this->register("out_bounce", CameraEaseType::OUT_BOUNCE());
		$this->register("in_out_bounce", CameraEaseType::IN_OUT_BOUNCE());
		$this->register("in_back", CameraEaseType::IN_BACK());
		$this->register("out_back", CameraEaseType::OUT_BACK());
		$this->register("in_out_back", CameraEaseType::IN_OUT_BACK());
		$this->register("in_elastic", CameraEaseType::IN_ELASTIC());
		$this->register("out_elastic", CameraEaseType::OUT_ELASTIC());
		$this->register("in_out_elastic", CameraEaseType::IN_OUT_ELASTIC());
	}

	public function register(string $stringId, CameraEaseType $type) : void{
		$this->idToEnum[$stringId] = $type;
		$this->enumToId[$type->id()] = $stringId;
	}

	public function fromId(string $id) : ?CameraEaseType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(CameraEaseType $type) : string{
		if(!array_key_exists($type->id(), $this->enumToId)){
			throw new \InvalidArgumentException("Missing mapping for camere ease type " . $type->name());
		}
		return $this->enumToId[$type->id()];
	}
}
