<?php

declare(strict_types=1);

namespace pocketmine\command;


use pocketmine\command\constraint\BaseConstraint;
use pocketmine\command\traits\ArgumentableTrait;
use pocketmine\command\traits\IArgumentable;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use function explode;

abstract class BaseSubCommand implements IArgumentable, IRunnable {
	use ArgumentableTrait;
	/** @var string */
	private string $name;
	/** @var string[] */
	private array $aliases;
	/** @var string */
	private string $description;
	/** @var string */
	protected string $usageMessage;
	/** @var string|null */
	private ?string $permission = null;
	/** @var CommandSender */
	protected CommandSender $currentSender;
	/** @var BaseCommand */
	protected BaseCommand $parent;
	/** @var BaseConstraint[] */
	private array $constraints = [];

	public function __construct(string $name, string $description = "", array $aliases = []) {
		$this->name = $name;
		$this->description = $description;
		$this->aliases = $aliases;

		$this->prepare();

		$this->usageMessage = $this->generateUsageMessage();
	}

	abstract public function onRun(CommandSender $sender, string $aliasUsed, array $args): void;

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string[]
	 */
	public function getAliases(): array {
		return $this->aliases;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getUsageMessage(): string {
		return $this->usageMessage;
	}

	/**
	 * @return string|null
	 */
	public function getPermission(): ?string {
		return $this->permission;
	}

	/**
	 * @param string $permission
	 */
	public function setPermission(string $permission): void {
		$this->permission = $permission;
	}

	public function testPermissionSilent(CommandSender $sender): bool {
		if(empty($this->permission)) {
			return true;
		}
		foreach(explode(";", $this->permission) as $permission) {
			if($sender->hasPermission($permission)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param CommandSender $currentSender
	 *
	 * @internal Used to pass the current sender from the parent command
	 */
	public function setCurrentSender(CommandSender $currentSender): void {
		$this->currentSender = $currentSender;
	}

	/**
	 * @param BaseCommand $parent
	 *
	 * @internal Used to pass the parent context from the parent command
	 */
	public function setParent(BaseCommand $parent): void {
		$this->parent = $parent;
	}

	public function sendError(int $errorCode, array $args = []): void {
		$this->parent->sendError($errorCode, $args);
	}

	public function sendUsage():void {
		$this->currentSender->sendMessage("/{$this->parent->getName()} $this->usageMessage");
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

	/**
	 * @return Plugin
	 */
	public function getOwningPlugin(): Plugin {
		return $this->parent->getOwningPlugin();
	}
}