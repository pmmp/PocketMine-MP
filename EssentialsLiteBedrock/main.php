<?php

declare(strict_types=1);

namespace EssentialsLiteBedrock;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\Position;
use pocketmine\utils\Config;

class Main extends PluginBase {

    private Config $homes;
    private Config $spawn;
    private array $tpaRequests = [];

    public function onEnable(): void {
        @mkdir($this->getDataFolder() . "data/");
        $this->homes = new Config($this->getDataFolder() . "data/homes.json", Config::JSON);
        $this->spawn = new Config($this->getDataFolder() . "data/spawn.json", Config::JSON);
    }

    public function onDisable(): void {
        $this->homes->save();
        $this->spawn->save();
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
        if (!$sender instanceof Player) return true;
        $name = strtolower($sender->getName());

        switch ($cmd->getName()) {

            case "setspawn":
                $pos = $sender->getPosition();
                $this->spawn->setAll([
                    "x" => $pos->getX(),
                    "y" => $pos->getY(),
                    "z" => $pos->getZ(),
                    "world" => $pos->getWorld()->getFolderName()
                ]);
                $this->spawn->save();
                return true;

            case "spawn":
                $data = $this->spawn->getAll();
                if (empty($data)) return true;
                $world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
                if ($world === null) return true;
                $sender->teleport(new Position($data["x"], $data["y"], $data["z"], $world));
                return true;

            case "sethome":
                if (count($args) < 1) return true;
                $homeName = strtolower($args[0]);
                $playerHomes = $this->homes->get($name, []);
                if (count($playerHomes) >= 3 && !isset($playerHomes[$homeName])) return true;
                $pos = $sender->getPosition();
                $playerHomes[$homeName] = [
                    "x" => $pos->getX(),
                    "y" => $pos->getY(),
                    "z" => $pos->getZ(),
                    "world" => $pos->getWorld()->getFolderName()
                ];
                $this->homes->set($name, $playerHomes);
                $this->homes->save();
                return true;

            case "home":
                if (count($args) < 1) return true;
                $homeName = strtolower($args[0]);
                $playerHomes = $this->homes->get($name, []);
                if (!isset($playerHomes[$homeName])) return true;
                $data = $playerHomes[$homeName];
                $world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
                if ($world === null) return true;
                $sender->teleport(new Position($data["x"], $data["y"], $data["z"], $world));
                return true;

            case "delhome":
                if (count($args) < 1) return true;
                $homeName = strtolower($args[0]);
                $playerHomes = $this->homes->get($name, []);
                unset($playerHomes[$homeName]);
                $this->homes->set($name, $playerHomes);
                $this->homes->save();
                return true;

            case "tp":
                if (count($args) < 1) return true;
                $target = Server::getInstance()->getPlayerExact($args[0]);
                if ($target === null) return true;
                $sender->teleport($target->getPosition());
                return true;

            case "tpa":
                if (count($args) < 1) return true;
                $target = Server::getInstance()->getPlayerExact($args[0]);
                if ($target === null || $target === $sender) return true;
                $this->tpaRequests[strtolower($target->getName())] = $sender->getName();
                return true;

            case "tpaccept":
                $targetName = $this->tpaRequests[$name] ?? null;
                if ($targetName === null) return true;
                $target = Server::getInstance()->getPlayerExact($targetName);
                if ($target === null) return true;
                $target->teleport($sender->getPosition());
                unset($this->tpaRequests[$name]);
                return true;

            case "fly":
                if (!$sender->hasPermission("ess.fly")) return true;
                $sender->setAllowFlight(!$sender->getAllowFlight());
                return true;

            case "gm":
                if (!$sender->hasPermission("ess.gm") || count($args) < 1) return true;
                $mode = (int)$args[0];
                $sender->setGamemode($mode);
                return true;
        }
        return true;
    }
}
