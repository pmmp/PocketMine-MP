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
 * @link http://www.pocketmine.net/
 *
 *
 */

/**
 * Class PermissionAPI
 *
 * An API that provides an interface for plugins to extend, or statically call.
 *
 * <p>The whole point of this permissions API is to provide developers with an expendable API
 * that allows fine tuning of each and every specific part of PocketMine.
 *
 * Thus, it should be as abstract as possible.
 *
 * Roles and levels - Administrator / Moderator etc will be handled by plugins.
 * Permissions loading and file formats etc will be handled by separate plugins.
 *
 * Permission checking on events will be handled by this API
 * Add enable-op argument to allow disabling OP systems. OP system should override this permission system, and this API
 * Will determine whether that option is set or not.</p>
 */
class PermissionsAPI{
    /**
     *
     */
    public function __construct(){

	}

	public function __destruct(){

	}

	public function init()
	{
		ServerAPI::request()->api->addHandler("player.connect", function ($player)//Use a better event than player.connect. Player.create maybe?
		{
            /** @var Player $player */
            $player->permissions = new PlayerPermissions();

            /**
             * @param string $event
             * @param array $data
             */
            $player->emitEvent = function ($event, $data) use ($player) {
                $player->permissions->emitEvent($event, $data);
            };

            //Now instead of doing api->handle, doing emitEvent will handle permissions automatically.

            /** @var array $newPermission */
			$newPermissions = ServerAPI::request()->api->dhandle("permissions.request", array('player' => $player));
			if($newPermissions){
				//array_push($player->permissions, $newPermissions);
                array_walk($newPermissions, function ($value, $key) use ($player) {
                        $player->permissions[] = $value;
                    });
			}else{
				//TODO: Give out default permission. Fall back to OP system maybe? Checking for a permissions receiver would be nice.
			}
		}, 1);//Experimental. Use Closure / Anonymous Functions to assign new functions from each API rather than hard-coding them to Player.php.
	}
}

class PlayerPermissions implements ArrayAccess
{
    private $Permissions = array();

    public function __construct()
    {

    }

    public function __destruct()
    {

    }

    public function __invoke()
    {

    }

    private function testRestrictions()
    {
        //TODO: Test Restrictions Here
    }

    public function emitEvent($event, $data)
    {
        //TODO: Emit event here
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->Permissions[] = $value;
        } else {
            $this->Permissions[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->Permissions[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->Permissions[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->Permissions[$offset]) ? $this->Permissions[$offset] : null;
    }
}

/**
 * Class Permission
 *
 * Each Permission object will be given a level in integer, and it will be this permission object that will be assigned to players.
 * Not just an integer variable. Plugins can extend this to have their own Permissions assigned to players.
 */
//interface RoleInterface{
//	/**
//	 * @return integer
//	 */
//	public function getPermissionLevel();
//
//	/**
//	 * @param RoleInterface $permission Permission to check the user for. Must be a an object implementing Permission class.
//	 *
//	 * @return boolean Whether the person has permissions or not.
//	 */
//	public function isGranted($permission);
//}

//Thinking of doing $player->permissions[] = new EventRestriction("player.move");
//$player->permissions->apply(new EventRestriction("player.move"));

interface Restriction
{
    public function __construct($restriction);

    public function __toString();
}

/**
 * Class EventRestriction
 */
class EventRestriction implements Restriction
{
    /**
     * @var string $event
     */
    private $event;

    /**
     * @param string $event
     */
    public function __construct($event)
    {
        $this->event = trim($event);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->event;
    }
}

/**
 * Class CommandRestriction
 */
class CommandRestriction implements Restriction
{
    /**
     * @var string $command
     */
    private $command;

    /**
     * @param string $command
     */
    public function __construct($command)
    {
        $this->command = strtolower(trim($command));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->command;
    }
}