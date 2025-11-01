<?php

declare(strict_types=1);

namespace pocketmine\command;


use pocketmine\command\constraint\BaseConstraint;
use pocketmine\command\exception\InvalidErrorCode;
use pocketmine\command\traits\ArgumentableTrait;
use pocketmine\command\traits\IArgumentable;
use InvalidArgumentException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use function array_shift;
use function array_unique;
use function array_unshift;
use function count;
use function dechex;
use function implode;
use function str_replace;

abstract class BaseCommand extends Command implements IArgumentable, IRunnable, PluginOwned {
	use ArgumentableTrait;

	public const ERR_INVALID_ARG_VALUE = 0x01;
	public const ERR_TOO_MANY_ARGUMENTS = 0x02;
	public const ERR_INSUFFICIENT_ARGUMENTS = 0x03;
	public const ERR_NO_ARGUMENTS = 0x04;

	/** @var string[] */
	protected array $errorMessages = [
		self::ERR_INVALID_ARG_VALUE => TextFormat::RED . "Invalid value '{value}' for argument #{position}",
		self::ERR_TOO_MANY_ARGUMENTS => TextFormat::RED . "Too many arguments given",
		self::ERR_INSUFFICIENT_ARGUMENTS => TextFormat::RED . "Insufficient number of arguments given",
		self::ERR_NO_ARGUMENTS => TextFormat::RED . "No arguments are required for this command",
	];

	/** @var CommandSender */
	protected CommandSender $currentSender;

	/** @var BaseSubCommand[] */
	private array $subCommands = [];

	/** @var BaseConstraint[] */
	private array $constraints = [];

	/** @var Plugin */
	private Plugin $plugin;

	public function __construct(
		Plugin $plugin,
		string $name,
		Translatable|string $description = "",
		array $aliases = []
	) {
		$this->plugin = $plugin;
		parent::__construct($name, $description, null, $aliases);

		$this->prepare();

		$usages = ["/" . $this->generateUsageMessage()];
		foreach($this->subCommands as $subCommand) {
			$usages[] = $subCommand->getUsageMessage();
		}
		$usages = array_unique($usages);
		$this->usageMessage = implode("\n - /" . $this->getName() . " ", $usages);
	}

	public function getOwningPlugin(): Plugin {
		return $this->plugin;
	}

	final public function execute(CommandSender $sender, string $commandLabel, array $args){
		$this->currentSender = $sender;
		if(!$this->testPermission($sender)){
			return;
		}
		/** @var BaseCommand|BaseSubCommand $cmd */
		$cmd = $this;
		$passArgs = [];
		if(count($args) > 0){
			if(isset($this->subCommands[($label = $args[0])])){
				array_shift($args);
				$cmd = $this->subCommands[$label];
				$cmd->setCurrentSender($sender);
				if(!$cmd->testPermissionSilent($sender)) {
					$msg = $this->getPermissionMessage();
					if($msg === null) {
						$sender->sendMessage(
							$sender->getServer()->getLanguage()->translateString(
								TextFormat::RED . "%commands.generic.permission"
							)
						);
					} elseif(empty($msg)) {
						$sender->sendMessage(str_replace("<permission>", $cmd->getPermission(), $msg));
					}

					return;
				}
			}

			$passArgs = $this->attemptArgumentParsing($cmd, $args);
		} elseif($this->hasRequiredArguments()){
			$this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);
			return;
		}
		if($passArgs !== null) {
			foreach ($cmd->getConstraints() as $constraint){
				if(!$constraint->test($sender, $commandLabel, $passArgs)){
					$constraint->onFailure($sender, $commandLabel, $passArgs);
					return;
				}
			}
			$cmd->onRun($sender, $commandLabel, $passArgs);
		}
	}

	/**
	 * @param ArgumentableTrait $ctx
	 * @param array             $args
	 *
	 * @return array|null
	 */
	private function attemptArgumentParsing($ctx, array $args): ?array {
		$dat = $ctx->parseArguments($args, $this->currentSender);
		if(!empty(($errors = $dat["errors"]))) {
			foreach($errors as $error) {
				$this->sendError($error["code"], $error["data"]);
			}

			return null;
		}

		return $dat["arguments"];
	}

	/**
	 * @param CommandSender  $sender
	 * @param string         $aliasUsed
	 * @param array|array<string,mixed|array<mixed>> $args
	 */
	abstract public function onRun(CommandSender $sender, string $aliasUsed, array $args): void;

	protected function sendUsage(): void {
		$this->currentSender->sendMessage("Usage: " . $this->getUsage());
	}

	public function sendError(int $errorCode, array $args = []): void {
		$str = $this->errorMessages[$errorCode];
		foreach($args as $item => $value) {
			$str = str_replace('{' . $item . '}', (string) $value, $str);
		}
		$this->currentSender->sendMessage($str);
	}

	public function setErrorFormat(int $errorCode, string $format): void {
		if(!isset($this->errorMessages[$errorCode])) {
			throw new InvalidErrorCode("Invalid error code 0x" . dechex($errorCode));
		}
		$this->errorMessages[$errorCode] = $format;
	}

	public function setErrorFormats(array $errorFormats): void {
		foreach($errorFormats as $errorCode => $format) {
			$this->setErrorFormat($errorCode, $format);
		}
	}

	public function registerSubCommand(BaseSubCommand $subCommand): void {
		$keys = $subCommand->getAliases();
		array_unshift($keys, $subCommand->getName());
		$keys = array_unique($keys);
		foreach($keys as $key) {
			if(!isset($this->subCommands[$key])) {
				$subCommand->setParent($this);
				$this->subCommands[$key] = $subCommand;
			} else {
				throw new InvalidArgumentException("SubCommand with same name / alias for '$key' already exists");
			}
		}
	}

	/**
	 * @return BaseSubCommand[]
	 */
	public function getSubCommands(): array {
		return $this->subCommands;
	}

	public function addConstraint(BaseConstraint $constraint) : void {
		$this->constraints[] = $constraint;
	}

	/**
	 * @return BaseConstraint[]
	 */
	public function getConstraints(): array {
		return $this->constraints;
	}

	public function getUsageMessage(): string {
		return $this->getUsage();
	}
}
