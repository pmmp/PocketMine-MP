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

use DaveRandom\CallbackValidator\ParameterInfo;
use DaveRandom\CallbackValidator\Prototype;
use DaveRandom\CallbackValidator\ReturnInfo;
use DaveRandom\CallbackValidator\Type\BuiltInType;
use DaveRandom\CallbackValidator\Type\NamedType;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandStringHelper;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\Translatable;
use pocketmine\permission\PermissionManager;
use function array_key_last;
use function array_slice;
use function count;
use function implode;
use function is_string;
use function strlen;
use function trigger_error;
use const E_USER_DEPRECATED;

/**
 * @internal Do not construct this class directly. Use {@link OverloadBuilder instead}.
 */
final class ExecutorOverload implements Overload{

	private int $requiredInputCount;
	private int $requiredCallbackArgumentCount;

	/**
	 * @var string[]
	 * @phpstan-var list<string>
	 */
	private array $permissions;

	/**
	 * @param Parameter[]|string[] $parameters
	 * @param string|string[]      $permission
	 * @phpstan-param list<Parameter<*>|string> $parameters
	 * @phpstan-param string|list<string>       $permission
	 * @phpstan-param anyClosure                $handler
	 */
	public function __construct(
		private array $parameters,
		string|array $permission,
		private \Closure $handler,
		private bool $acceptsAliasUsed = false,
		private Translatable|string|null $customUsageMessage = null
	){
		if($this->customUsageMessage !== null){
			trigger_error("Overriding overload usage message is discouraged. A usage message can usually be generated from the parameter infos.", E_USER_DEPRECATED);
		}
		foreach($this->parameters as $k => $parameter){
			if(!is_string($parameter) && $parameter->consumesAllRemainingInputs() && $k !== array_key_last($this->parameters)){
				throw new \InvalidArgumentException($parameter::class . " can only be used as the final argument, because it consumes all remaining inputs");
			}
		}
		$permissions = is_string($permission) ? [$permission] : $permission;
		if(count($permissions) === 0){
			throw new \InvalidArgumentException("At least one permission must be provided");
		}
		$permissionManager = PermissionManager::getInstance();
		foreach($permissions as $perm){
			if($permissionManager->getPermission($perm) === null){
				throw new \InvalidArgumentException("Cannot use non-existing permission \"$perm\"");
			}
		}
		$this->permissions = $permissions;

		//TODO: auto infer parameter infos if they aren't provided?
		//TODO: allow the type of CommandSender to be constrained - this can be useful for player-only commands etc
		$nonInputParameters = self::alwaysPresentArgs($this->acceptsAliasUsed);

		$literalCount = 0;
		$inputParameters = [];
		foreach($this->parameters as $parameter){
			if(is_string($parameter)){
				$literalCount++;
			}else{
				$inputParameters[] = new ParameterInfo(
					$parameter->getCodeName(),
					$parameter->getCodeType(),
					byReference: false,
					isOptional: false,
					isVariadic: false,
				);
			}
		}

		$expectedPrototype = new Prototype(
			new ReturnInfo(new NamedType(BuiltInType::VOID), byReference: false),
			...$nonInputParameters,
			...$inputParameters
		);
		$actualPrototype = Prototype::fromClosure($this->handler);
		if(!$expectedPrototype->isSatisfiedBy($actualPrototype)){
			//validateCallableSignature() not used because we want a custom error message
			throw new \InvalidArgumentException("Expected handler signature $expectedPrototype from provided parameter info, but handler has signature $actualPrototype");
		}

		//optionals are inferred from the prototype of the callable, not the parameter infos themselves
		//contravariance allows them to be optional even if they're required in the prototype
		//literals must always be provided
		$this->requiredInputCount = $actualPrototype->getRequiredParameterCount() + $literalCount - count($nonInputParameters);
		$this->requiredCallbackArgumentCount = $actualPrototype->getRequiredParameterCount() - count($nonInputParameters);
	}

	/**
	 * @return ParameterInfo[]
	 */
	private static function alwaysPresentArgs(bool $acceptsAliasUsed) : array{
		$result = [new ParameterInfo("sender", new NamedType(CommandSender::class), byReference: false, isOptional: false, isVariadic: false)];
		if($acceptsAliasUsed){
			$result[] = new ParameterInfo("aliasUsed", new NamedType(BuiltInType::STRING), byReference: false, isOptional: false, isVariadic: false);
		}
		return $result;
	}

	/**
	 * @return Parameter[]|string[]
	 * @phpstan-return list<Parameter<*>|string>
	 */
	public function getParameters() : array{ return $this->parameters; }

	public function getRequiredParameterCount() : int{
		return $this->requiredInputCount;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getPermissions() : array{ return $this->permissions; }

	public function senderHasAnyPermissions(CommandSender $sender) : bool{
		foreach($this->permissions as $permission){
			if($sender->hasPermission($permission)){
				return true;
			}
		}

		return false;
	}

	public function getUsage(string $aliasUsed) : Translatable{
		if($this->customUsageMessage !== null){
			return new Translatable("{%0}", [$this->customUsageMessage]);
		}
		$templates = [];
		$args = [];
		$pos = 0;
		$processed = 0;
		foreach($this->parameters as $parameter){
			$processed++;
			if(is_string($parameter)){
				//literal token
				$templates[] = $parameter;
				continue;
			}
			//TODO: printable type info would be nice
			if($processed <= $this->requiredInputCount){
				$template = "<{%$pos}>";
			}else{
				$template = "[{%$pos}]";
			}
			$suffix = $parameter->getSuffix();
			$template .= $suffix;
			$templates[] = $template;

			$args[] = $parameter->getPrintableName();
			$pos++;
		}

		return new Translatable("/$aliasUsed " . implode(" ", $templates), $args);
	}

	/**
	 * @param mixed[] $parentArgs
	 * @phpstan-param list<mixed> $parentArgs
	 */
	public function invoke(CommandContext $context, int $offset, array $parentArgs, int $parentParametersParsed) : bool{
		$parameters = $parentParametersParsed > 0 ? array_slice($this->parameters, $parentParametersParsed) : $this->parameters;

		$commandLine = $context->getCommandLine();
		try{
			$myArgs = CommandStringHelper::parseArguments($parameters, $commandLine, $offset);
		}catch(ParameterParseException $e){
			$context->invalidSyntax($this, $offset, $e->getMessage());
			return false;
		}
		if($offset !== strlen($commandLine)){
			$context->invalidSyntax($this, $offset, "Too many inputs provided for overload");
			return false;
		}
		if(count($parentArgs) + count($myArgs) < $this->requiredCallbackArgumentCount){
			$context->invalidSyntax($this, $offset, "Not enough arguments resolved for overload");
			return false;
		}
		//Reflection magic here :)
		//TODO: maybe we don't want to invoke this directly, but hand the args back to the caller?
		//this would allow resolving by more than just overload order
		$sender = $context->getSender();
		if(!$this->senderHasAnyPermissions($sender)){
			$context->permissionDenied($this);
			return false;
		}
		try{
			if($this->acceptsAliasUsed){
				// @phpstan-ignore-next-line
				($this->handler)($sender, $context->getAliasUsed(), ...$parentArgs, ...$myArgs);
			}else{
				// @phpstan-ignore-next-line
				($this->handler)($sender, ...$parentArgs, ...$myArgs);
			}
		}catch(InvalidCommandSyntaxException $e){
			$context->invalidSyntax($this, $offset, $e->getMessage());
			return false;
		}

		return true;
	}

	public function collectExecutors() : array{
		return [$this];
	}

}
