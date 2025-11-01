<?php

declare(strict_types=1);

namespace pocketmine\command;

use pocketmine\Server;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\permission\DefaultPermissions;
use pocketmine\command\defaults\VanillaCommand;

use function count;
use function strtolower;
use function trim;

class BaseCommandMap extends SimpleCommandMap
{
    public function __construct(Server $server)
    {
        // No special-case pre-registration required anymore: Command::setPermissions will
        // auto-create any missing permissions when commands are constructed.
        parent::__construct($server);
    }

    public function register(string $fallbackPrefix, Command $command, ?string $label = null): bool
    {
        if (count($command->getPermissions()) === 0) {
            $lbl = $label ?? $command->getLabel();
            $lbl = trim($lbl);
            $perm = strtolower(trim($fallbackPrefix)) . ".command." . strtolower($lbl);

            $pm = PermissionManager::getInstance();
            if ($pm->getPermission($perm) === null) {
                $pm->addPermission(new Permission($perm, $command->getDescription()));
            }
            $command->setPermission($perm);
        }

        return parent::register($fallbackPrefix, $command, $label);
    }
}
