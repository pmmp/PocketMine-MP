<?php

/**
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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine;

use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3 as Vector3;

class PlayerAPI{
	private $server;

	function __construct(){
		$this->server = Server::getInstance();
	}

	public function init(){
		$this->server->schedule(20 * 15, array($this, "handle"), 1, true, "server.regeneration");
		$this->server->addHandler("player.death", array($this, "handle"), 1);
		$this->server->api->console->register("kill", "<player>", array($this, "commandHandler"));
		$this->server->api->console->register("tp", "[target player] <destination player | w:world> OR /tp [target player] <x> <y> <z>", array($this, "commandHandler"));
		$this->server->api->console->register("spawnpoint", "[player | w:world] [x] [y] [z]", array($this, "commandHandler"));

		$this->server->api->console->alias("suicide", "kill");
		$this->server->api->console->alias("tppos", "tp");
		$this->server->api->ban->cmdWhitelist("list");
		$this->server->api->ban->cmdWhitelist("ping");
		$this->server->api->ban->cmdWhitelist("spawn");
	}

	public function handle($data, $event){
		switch($event){
			case "server.regeneration":
				/*if($this->server->difficulty === 0){
					$result = $this->server->preparedSQL->selectPlayersToHeal->execute();
					if($result !== false){
						while(($player = $result->fetchArray()) !== false){
							if(($player = Entity::get($player["EID"])) !== false){
								if($player->getHealth() <= 0){
									continue;
								}
								$player->setHealth(min(20, $player->getHealth() + $data), "regeneration");
							}
						}
						return true;
					}
				}*/
				break;
			case "player.death":
				if(is_numeric($data["cause"])){
					$e = Entity::get($data["cause"]);
					if($e instanceof Entity){
						switch($e->class){
							case ENTITY_PLAYER:
								$message = " was killed by " . $e->name;
								break;
							default:
								$message = " was killed";
								break;
						}
					}
				}else{
					switch($data["cause"]){
						case "cactus":
							$message = " was pricked to death";
							break;
						case "lava":
							$message = " tried to swim in lava";
							break;
						case "fire":
							$message = " went up in flames";
							break;
						case "burning":
							$message = " burned to death";
							break;
						case "suffocation":
							$message = " suffocated in a wall";
							break;
						case "water":
							$message = " drowned";
							break;
						case "void":
							$message = " fell out of the world";
							break;
						case "fall":
							$message = " hit the ground too hard";
							break;
						case "explosion":
							$message = " blew up";
							break;
						default:
							$message = " died";
							break;
					}
				}
				Player::broadcastMessage($data["player"]->getName() . $message);

				return true;
		}
		return;
	}

	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "spawnpoint":
				if(count($params) === 0){
					$output .= "Usage: /$cmd [player | w:world] [x] [y] [z]\n";
					break;
				}
				if(!($issuer instanceof Player) and count($params) < 4){
					$output .= "Please run this command in-game.\n";
					break;
				}

				if(count($params) === 1 or count($params) === 4){
					$tg = array_shift($params);
					if(count($params) === 3 and substr($tg, 0, 2) === "w:"){
						$target = Level::get(substr($tg, 2));
					}else{
						$target = Player::get($tg);
					}
				}else{
					$target = $issuer;
				}

				if(!($target instanceof Player) and !($target instanceof Level)){
					$output .= "That player cannot be found.\n";
					break;
				}

				if(count($params) === 3){
					if($target instanceof Level){
						$spawn = new Vector3(floatval(array_shift($params)), floatval(array_shift($params)), floatval(array_shift($params)));
					}else{
						$spawn = new Position(floatval(array_shift($params)), floatval(array_shift($params)), floatval(array_shift($params)), $issuer->level);
					}
				}else{
					$spawn = new Position($issuer->entity->x, $issuer->entity->y, $issuer->entity->z, $issuer->entity->level);
				}

				$target->setSpawn($spawn);
				if($target instanceof Level){
					$output .= "Spawnpoint of world " . $target->getName() . " set correctly!\n";
				}elseif($target !== $issuer){
					$output .= "Spawnpoint of " . $target->getName() . " set correctly!\n";
				}else{
					$output .= "Spawnpoint set correctly!\n";
				}
				break;
			case "spawn":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				$issuer->teleport(Level::getDefault()->getSafeSpawn());
				break;
			case "ping":
				if(!($issuer instanceof Player)){
					$output .= "Please run this command in-game.\n";
					break;
				}
				$output .= "ping " . round($issuer->getLag(), 2) . "ms, packet loss " . round($issuer->getPacketLoss() * 100, 2) . "%, " . round($issuer->getBandwidth() / 1024, 2) . " KB/s\n";
				break;
			case "gamemode":
				$player = false;
				$setgm = false;
				$gms = array(
					"0" => 0,
					"survival" => 0,
					"s" => 0,
					"1" => 1,
					"creative" => 1,
					"c" => 1,
					"2" => 2,
					"adventure" => 2,
					"a" => 2,
					"3" => 3,
					"view" => 3,
					"viewer" => 3,
					"spectator" => 3,
					"v" => 3,
				);
				if(isset($params[1])){
					if(Player::get($params[1]) instanceof Player){
						$player = Player::get($params[1]);
						$setgm = $params[0];
					}elseif(Player::get($params[0]) instanceof Player){
						$player = Player::get($params[0]);
						$setgm = $params[1];
					}else{
						$output .= "Usage: /$cmd <mode> [player] or /$cmd [player] <mode>\n";
						break;
					}
				}elseif(isset($params[0])){
					if(!(Player::get($params[0]) instanceof Player)){
						if($issuer instanceof Player){
							$setgm = $params[0];
							$player = $issuer;
						}
					}
				}

				if(!($player instanceof Player) or !isset($gms[strtolower($setgm)])){
					$output .= "Usage: /$cmd <mode> [player] or /$cmd [player] <mode>\n";
					break;
				}
				if($player->setGamemode($gms[strtolower($setgm)])){
					$output .= "Gamemode of " . $player->getName() . " changed to " . $player->getGamemode() . "\n";
				}
				break;
			case "tp":
				if(count($params) <= 2 or substr($params[0], 0, 2) === "w:" or substr($params[1], 0, 2) === "w:"){
					if((!isset($params[1]) or substr($params[0], 0, 2) === "w:") and isset($params[0]) and ($issuer instanceof Player)){
						$name = $issuer->getName();
						$target = implode(" ", $params);
					}elseif(isset($params[1]) and isset($params[0])){
						$name = array_shift($params);
						$target = implode(" ", $params);
					}else{
						$output .= "Usage: /$cmd [target player] <destination player | w:world>\n";
						break;
					}
					if($this->teleport($name, $target) !== false){
						$output .= "\"$name\" teleported to \"$target\"\n";
					}else{
						$output .= "Couldn't teleport.\n";
					}
				}else{
					if(!isset($params[3]) and isset($params[2]) and isset($params[1]) and isset($params[0]) and ($issuer instanceof Player)){
						$name = $issuer->getName();
						$x = $params[0];
						$y = $params[1];
						$z = $params[2];
					}elseif(isset($params[3]) and isset($params[2]) and isset($params[1]) and isset($params[0])){
						$name = $params[0];
						$x = $params[1];
						$y = $params[2];
						$z = $params[3];
					}else{
						$output .= "Usage: /$cmd [player] <x> <y> <z>\n";
						break;
					}
					if($this->tppos($name, $x, $y, $z)){
						$output .= "\"$name\" teleported to ($x, $y, $z)\n";
					}else{
						$output .= "Couldn't teleport.\n";
					}
				}
				break;
			case "kill":
			case "suicide":
				if(!isset($params[0]) and ($issuer instanceof Player)){
					$player = $issuer;
				}else{
					$player = Player::get($params[0]);
				}
				if($player instanceof Player){
					$player->harm(1000, "console", true);
					$player->sendMessage("Ouch. That looks like it hurt.\n");
				}else{
					$output .= "Usage: /$cmd [player]\n";
				}
				break;
			case "list":
				$output .= "There are " . count(Player::$list) . "/" . $this->server->maxClients . " players online:\n";
				if(count(Player::$list) == 0){
					break;
				}
				foreach(Player::$list as $c){
					$output .= $c->getName() . ", ";
				}
				$output = substr($output, 0, -2) . "\n";
				break;
		}

		return $output;
	}

	public function teleport(&$name, &$target){
		if(substr($target, 0, 2) === "w:"){
			$lv = Level::get(substr($target, 2));
			if($lv instanceof Level){
				$origin = Player::get($name);
				if($origin instanceof Player){
					$name = $origin->getName();

					return $origin->teleport($lv->getSafeSpawn());
				}
			}else{
				return false;
			}
		}
		$player = Player::get($target);
		if($player instanceof Player and $player->spawned === true){
			$target = $player->getName();
			$origin = Player::get($name);
			if($origin instanceof Player){
				$name = $origin->getName();

				return $origin->teleport($player->entity);
			}
		}

		return false;
	}

	public function tppos(&$name, &$x, &$y, &$z){
		$player = Player::get($name);
		if($player instanceof Player and $player->spawned === true){
			$name = $player->getName();
			$x = $x{0} === "~" ? $player->x + floatval(substr($x, 1)) : floatval($x);
			$y = $y{0} === "~" ? $player->y + floatval(substr($y, 1)) : floatval($y);
			$z = $z{0} === "~" ? $player->z + floatval(substr($z, 1)) : floatval($z);
			$player->teleport(new Vector3($x, $y, $z));

			return true;
		}

		return false;
	}
}
