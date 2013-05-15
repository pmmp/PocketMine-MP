<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

class LevelAPI{
	private $server, $levels, $default;
	public function __construct(){
		$this->server = ServerAPI::request();
		$this->levels = array();
	}
	
	public function get($name){
		if(isset($this->levels[$name])){
			return $this->levels[$name];
		}
		return false;
	}
	
	public function getDefault(){
		return $this->levels[$this->default];
	}

	public function init(){
		$this->server->api->console->register("seed", "[world]", array($this, "commandHandler"));
		$this->default = $this->server->api->getProperty("level-name");
		if($this->loadLevel($this->default) === false){
			$this->generateLevel($this->default);
			$this->loadLevel($this->default);
		}
		$this->server->spawn = $this->getDefault()->getSpawn();
	}
	
	public function commandHandler($cmd, $params, $issuer, $alias){
		$output = "";
		switch($cmd){
			case "seed":
				if(!isset($params[0]) and ($issuer instanceof Player)){
					$output .= "Seed: ".$issuer->level->getSeed()."\n";
				}elseif(isset($params[0])){
					if(($lv = $this->server->api->level->get(trim(implode(" ", $params)))) !== false){
						$output .= "Seed: ".$lv->getSeed()."\n";
					}
				}else{
					$output .= "Seed: ".$this->server->api->level->getDefault()->getSeed()."\n";
				}
		}
		return $output;			
	}
	
	public function generateLevel($name, $seed = false){
		$path = DATA_PATH."worlds/".$name."/";
		$generator = "SuperflatGenerator";
		if($this->server->api->getProperty("generator") !== false and class_exists($this->server->api->getProperty("generator"))){
			$generator = $this->server->api->getProperty("generator");
		}
		$gen = new WorldGenerator($generator, ($seed === false ? Utils::readInt(Utils::getRandomBytes(4, false)):(int) $seed));
		if($this->server->api->getProperty("generator-settings") !== false and trim($this->server->api->getProperty("generator-settings")) != ""){
			$gen->set("preset", $this->server->api->getProperty("generator-settings"));
		}
		$gen->init();
		$gen->generate();
		$gen->save($path, $name);
	}
	
	public function loadLevel($name){
		if($this->get($name) !== false){
			return false;
		}
		$path = DATA_PATH."worlds/".$name."/";
		if(!file_exists($path."level.pmf")){
			$level = new LevelImport($path);
			if($level->import() === false){
				return false;
			}
		}
		console("[INFO] Preparing level \"".$name."\"");
		$level = new PMFLevel($path."level.pmf");
		$entities = new Config($path."entities.yml", CONFIG_YAML);
		$tileEntities = new Config($path."tileEntities.yml", CONFIG_YAML);
		$this->levels[$name] = new Level($level, $entities, $tileEntities, $name);
		foreach($entities->getAll() as $entity){
			if(!isset($entity["id"])){
				break;
			}
			if($entity["id"] === 64){ //Item Drop
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_ITEM, $entity["Item"]["id"], array(
					"meta" => $entity["Item"]["Damage"],
					"stack" => $entity["Item"]["Count"],
					"x" => $entity["Pos"][0],
					"y" => $entity["Pos"][1],
					"z" => $entity["Pos"][2],
					"yaw" => $entity["Rotation"][0],
					"pitch" => $entity["Rotation"][1],
				));
			}elseif($entity["id"] === OBJECT_PAINTING){ //Painting
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_OBJECT, $entity["id"], $entity);
				$e->setPosition(new Vector3($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2]), $entity["Rotation"][0], $entity["Rotation"][1]);
				$e->setHealth($entity["Health"]);
			}else{
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_MOB, $entity["id"], $entity);
				$e->setPosition(new Vector3($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2]), $entity["Rotation"][0], $entity["Rotation"][1]);
				$e->setHealth($entity["Health"]);
			}
		}
			
		foreach($tileEntities->getAll() as $tile){
			if(!isset($tile["id"])){
				break;
			}
			$t = $this->server->api->tileentity->add($this->levels[$name], $tile["id"], $tile["x"], $tile["y"], $tile["z"], $tile);
		}
	}

	public function handle($data, $event){
		switch($event){
		}
	}

	public function getSpawn(){
		return $this->server->spawn;
	}
	
	public function loadMap(){
		if($this->mapName !== false and trim($this->mapName) !== ""){
			if(!file_exists($this->mapDir."level.pmf")){
				$level = new LevelImport($this->mapDir);
				$level->import();
			}
			$this->level = new PMFLevel($this->mapDir."level.pmf");
			console("[INFO] Preparing level \"".$this->level->getData("name")."\"");
			$this->time = (int) $this->level->getData("time");
			$this->seed = (int) $this->level->getData("seed");
			$this->spawn = $this->level->getSpawn();
		}
	}

	public function save($final = false){
		if($this->mapName !== false){
			$this->levelData["Time"] = $this->time;
			file_put_contents($this->mapDir."level.dat", serialize($this->levelData));
			$this->map->saveMap($final);
			$this->trigger("server.save", $final);
			if(count($this->entities) > 0){
				$entities = array();
				foreach($this->entities as $entity){
					if($entity->class === ENTITY_MOB){
						$entities[] = array(
							"id" => $entity->type,
							"Color" => @$entity->data["Color"],
							"Sheared" => @$entity->data["Sheared"],
							"Health" => $entity->health,
							"Pos" => array(
								0 => $entity->x,
								1 => $entity->y,
								2 => $entity->z,
							),
							"Rotation" => array(
								0 => $entity->yaw,
								1 => $entity->pitch,
							),
						);
					}elseif($entity->class === ENTITY_OBJECT){
						$entities[] = array(
							"id" => $entity->type,
							"TileX" => $entity->x,
							"TileX" => $entity->y,
							"TileX" => $entity->z,
							"Health" => $entity->health,
							"Motive" => $entity->data["Motive"],
							"Pos" => array(
								0 => $entity->x,
								1 => $entity->y,
								2 => $entity->z,
							),
							"Rotation" => array(
								0 => $entity->yaw,
								1 => $entity->pitch,
							),
						);
					}elseif($entity->class === ENTITY_ITEM){
						$entities[] = array(
							"id" => 64,
							"Item" => array(
								"id" => $entity->type,
								"Damage" => $entity->meta,
								"Count" => $entity->stack,
							),
							"Health" => $entity->health,
							"Pos" => array(
								0 => $entity->x,
								1 => $entity->y,
								2 => $entity->z,
							),
							"Rotation" => array(
								0 => 0,
								1 => 0,
							),
						);
					}
				}
				file_put_contents($this->mapDir."entities.dat", serialize($entities));
			}
			if(count($this->tileEntities) > 0){
				$tiles = array();
				foreach($this->tileEntities as $tile){
					$tiles[] = $tile->data;
				}
				file_put_contents($this->mapDir."tileEntities.dat", serialize($tiles));
			}
		}
	}
	
}