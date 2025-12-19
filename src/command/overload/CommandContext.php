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

namespace pocketmine\command\overload;

use pocketmine\command\CommandSender;

final class CommandContext{

	private int $mostSuccessfulFailedParseOffset = 0;

	/**
	 * @var ExecutorOverload[]
	 * @phpstan-var list<ExecutorOverload>
	 */
	private array $mostSuccessfulFailedOverloads = [];
	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	private array $mostSuccessfulFailedOverloadReasons = [];
	/**
	 * @var ExecutorOverload[]
	 * @phpstan-var list<ExecutorOverload>
	 */
	private array $permissionDeniedOverloads = [];

	public function __construct(
		private CommandSender $sender,
		private string $aliasUsed,
		private string $commandLine
	){}

	public function getSender() : CommandSender{ return $this->sender; }

	public function getAliasUsed() : string{ return $this->aliasUsed; }

	public function getCommandLine() : string{ return $this->commandLine; }

	public function permissionDenied(ExecutorOverload $overload) : void{
		$this->permissionDeniedOverloads[] = $overload;
	}

	public function invalidSyntax(Overload $overload, int $offsetReached, string $reason) : void{
		if($offsetReached === $this->mostSuccessfulFailedParseOffset){
			foreach($overload->collectExecutors() as $executor){
				$this->mostSuccessfulFailedOverloads[] = $executor;
				$this->mostSuccessfulFailedOverloadReasons[] = $reason;
			}
		}elseif($offsetReached > $this->mostSuccessfulFailedParseOffset){
			$this->mostSuccessfulFailedOverloads = [];
			$this->mostSuccessfulFailedOverloadReasons = [];
			$this->mostSuccessfulFailedParseOffset = $offsetReached;
			foreach($overload->collectExecutors() as $executor){
				$this->mostSuccessfulFailedOverloads[] = $executor;
				$this->mostSuccessfulFailedOverloadReasons[] = $reason;
			}
		}
	}

	/**
	 * @return ExecutorOverload[]
	 * @phpstan-return list<ExecutorOverload>
	 */
	public function getMostSuccessfulFailedOverloads() : array{ return $this->mostSuccessfulFailedOverloads; }

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getMostSuccessfulFailedOverloadReasons() : array{ return $this->mostSuccessfulFailedOverloadReasons; }

	/**
	 * @return ExecutorOverload[]
	 * @phpstan-return list<ExecutorOverload>
	 */
	public function getPermissionDeniedOverloads() : array{ return $this->permissionDeniedOverloads; }
}
