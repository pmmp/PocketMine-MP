<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\args\TargetArgument;
use pocketmine\command\args\RelativeFloatArgument;
use pocketmine\command\args\BooleanArgument;
use pocketmine\command\CommandoCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\entity\Location;
use pocketmine\lang\KnownTranslationFactory;

class TeleportCommand extends CommandoCommand
{

	public function __construct()
	{
		parent::__construct("tp", "Teleports entities", "/tp <destination> or /tp <victim> <destination>");
		$this->setPermission("beeltymine.command.tp");
	}

	protected function prepare(): void
	{
		// Simplified overloads - only the most common use cases
		
		// Overload 1: /tp <destination: target> - teleport to a player
		$this->registerArgument(0, new TargetArgument("destination", true));
		
		// Overload 2: /tp <x> <y> <z> - teleport to coordinates
		$this->registerArgument(0, new RelativeFloatArgument("x", true));
		$this->registerArgument(1, new RelativeFloatArgument("y", true));
		$this->registerArgument(2, new RelativeFloatArgument("z", true));
		
		// Overload 3: /tp <victim: target> <destination: target> - teleport someone to a player
		$this->registerArgument(0, new TargetArgument("victim", true));
		$this->registerArgument(1, new TargetArgument("destination2", true));
		
		// Overload 4: /tp <victim: target> <x> <y> <z> - teleport someone to coordinates
		// victim already registered at position 0
		$this->registerArgument(1, new RelativeFloatArgument("x2", true));
		$this->registerArgument(2, new RelativeFloatArgument("y2", true));
		$this->registerArgument(3, new RelativeFloatArgument("z2", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if (!$this->testPermission($sender)) {
			$sender->sendMessage(KnownTranslationFactory::commands_tp_noPermission());
			return;
		}

		// Normalize args - handle arrays from overlapping argument names
		foreach ($args as $key => $value) {
			if (is_array($value)) {
				$args[$key] = $value[0] ?? "";
			}
		}

		// Detect which overload based on provided arguments
		if (isset($args["victim"])) {
			// Has victim - teleporting someone else
			$victim = $this->resolveTarget($sender, $args["victim"]);
			if (!$victim) {
				$sender->sendMessage(KnownTranslationFactory::commands_tp_victimNotFound());
				return;
			}
			
			if (isset($args["destination2"])) {
				// /tp <victim> <destination>
				$dest = $this->resolveTarget($sender, $args["destination2"]);
				if (!$dest) {
					$sender->sendMessage(KnownTranslationFactory::commands_tp_destinationNotFound());
					return;
				}
				$victim->teleport($dest->getPosition());
				$sender->sendMessage(KnownTranslationFactory::commands_tp_teleportedPlayer($victim->getName(), $dest->getName()));
			} elseif (isset($args["x2"]) && isset($args["y2"]) && isset($args["z2"])) {
				// /tp <victim> <x> <y> <z>
				$x = $this->parseRelativeCoord($args["x2"], $victim->getPosition()->getX());
				$y = $this->parseRelativeCoord($args["y2"], $victim->getPosition()->getY());
				$z = $this->parseRelativeCoord($args["z2"], $victim->getPosition()->getZ());
				
				$pos = new Position($x, $y, $z, $victim->getWorld());
				$victim->teleport($pos);
				$sender->sendMessage(KnownTranslationFactory::commands_tp_teleportedPlayerCoords(
					$victim->getName(),
					(string) round($x, 2),
					(string) round($y, 2),
					(string) round($z, 2)
				));
			} else {
				$sender->sendMessage(KnownTranslationFactory::commands_tp_invalidArgs());
			}
		} elseif (isset($args["destination"])) {
			// /tp <destination> - sender teleports to target
			if (!($sender instanceof Player)) {
				$sender->sendMessage(KnownTranslationFactory::commands_tp_consoleCannotTeleport());
				return;
			}
			
			$dest = $this->resolveTarget($sender, $args["destination"]);
			if (!$dest) {
				$sender->sendMessage(KnownTranslationFactory::commands_tp_destinationNotFound());
				return;
			}
			
			$sender->teleport($dest->getPosition());
			$sender->sendMessage(KnownTranslationFactory::commands_tp_teleportedTo($dest->getName()));
		} elseif (isset($args["x"]) && isset($args["y"]) && isset($args["z"])) {
			// /tp <x> <y> <z> - sender teleports to coordinates
			if (!($sender instanceof Player)) {
				$sender->sendMessage(KnownTranslationFactory::commands_tp_consoleCannotTeleport());
				return;
			}
			
			$x = $this->parseRelativeCoord($args["x"], $sender->getPosition()->getX());
			$y = $this->parseRelativeCoord($args["y"], $sender->getPosition()->getY());
			$z = $this->parseRelativeCoord($args["z"], $sender->getPosition()->getZ());
			
			$pos = new Position($x, $y, $z, $sender->getWorld());
			$sender->teleport($pos);
			$sender->sendMessage(KnownTranslationFactory::commands_tp_teleportedCoords(
				(string) round($x, 2),
				(string) round($y, 2),
				(string) round($z, 2)
			));
		} else {
			$sender->sendMessage(KnownTranslationFactory::commands_tp_usage());
		}
	}
	
	private function resolveTarget(CommandSender $sender, string $target): ?Player {
		// Handle target selectors
		if (strlen($target) > 0 && $target[0] === '@') {
			switch ($target) {
				case '@s':
					return $sender instanceof Player ? $sender : null;
				case '@p':
					// Nearest player
					if ($sender instanceof Player) {
						return $this->getNearestPlayer($sender);
					}
					return null;
				case '@r':
					// Random player
					$players = $sender->getServer()->getOnlinePlayers();
					return count($players) > 0 ? $players[array_rand($players)] : null;
				case '@a':
					// All players - for simplicity, return first (vanilla would handle multiple)
					$players = $sender->getServer()->getOnlinePlayers();
					return count($players) > 0 ? reset($players) : null;
				case '@e':
					// All entities - for simplicity, treat as @a
					$players = $sender->getServer()->getOnlinePlayers();
					return count($players) > 0 ? reset($players) : null;
			}
		}
		
		// Regular player name or prefix
		return $sender->getServer()->getPlayerByPrefix($target);
	}
	
	private function getNearestPlayer(Player $from): ?Player {
		$nearest = null;
		$minDist = PHP_FLOAT_MAX;
		
		foreach ($from->getServer()->getOnlinePlayers() as $player) {
			if ($player === $from) continue;
			if ($player->getWorld() !== $from->getWorld()) continue;
			
			$dist = $player->getPosition()->distance($from->getPosition());
			if ($dist < $minDist) {
				$minDist = $dist;
				$nearest = $player;
			}
		}
		
		return $nearest;
	}
	
	private function parseRelativeCoord(string $coord, float $current): float {
		if ($coord === '~') {
			return $current;
		}
		if (strlen($coord) > 1 && $coord[0] === '~') {
			return $current + (float)substr($coord, 1);
		}
		return (float)$coord;
	}
}
