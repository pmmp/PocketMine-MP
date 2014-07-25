.. _config:

Configuration
=============
.. contents::
	:depth: 2

Basic settings
--------------
When you start the server for the first time you will get a set-up wizard.

Set-up wizard
-------------
.. code-block:: text

	PocketMine-MP/ $ ./start.sh
	[*] PocketMine-MP set-up wizard
	[*] Please select a language:
	 English => en
	 Español => es
	 中文 => zh
	 Pyccĸий => ru
	 日本語 => ja
	 Deutsch => de
	 한국어 => ko
	 Français => fr
	 Italiano => it
	 Nederlands => nl
	 Svenska => sv
	 Suomi => fi
	 Türkçe => tr
	[?] Language (en):

Choose the language you want and press enter

.. code-block:: text

	[?] Language (en):  en

.. code-block:: text

	[*] English has been correctly selected.
	Welcome to PocketMine-MP!
	Before starting setting up your new server you have to accept the license.
	PocketMine-MP is licensed under the LGPL License,
	that you can read opening the LICENSE file on this folder.

	  This program is free software: you can redistribute it and/or modify
	  it under the terms of the GNU Lesser General Public License as published by
	  the Free Software Foundation, either version 3 of the License, or
	  (at your option) any later version.

.. code-block:: text

	[?] Do you accept the License? (y/N): y
	[?] Do you want to skip the set-up wizard? (y/N):

If this is not the first time or you already have a custom properties file you can skip the wizard.

.. code-block:: text

	[?] Do you want to skip the set-up wizard? (y/N): n

.. code-block:: text

	[*] You are going to set up your server now.
	[*] If you don't want to change the default value, just press Enter.
	[*] You can edit them later on the server.properties file.
	[?] Give a name to your server (Minecraft: PE Server):
	[*] Do not change the default port value if this is your first server.
	[?] Server port (19132): 
	[*] The RAM is the maximum amount of memory PocketMine-MP will use. A value of 128-256 MB is recommended
	[?] Server RAM in MB (256): 
	[*] Choose between Creative (1) or Survival (0)
	[?] Default Game mode: (0): 
	[?] Max. online players (20): 
	[*] The spawn protection disallows placing/breaking blocks in the spawn zone except for OPs
	[?] Enable spawn protection? (Y/n): 
	[*] An OP is the player admin of the server. OPs can run more commands than normal players
	[?] OP player name (example, your game name): 
	[!] You will be able to add an OP user later using /op <player>
	[*] The white-list only allows players in it to join.
	[?] Do you want to enable the white-list? (y/N): 
	[!] Query is a protocol used by diferent tools to get information of your server and players logged in.
	[!] If you disable it, you won't be able to use server lists.
	[?] Do you want to disable Query? (y/N): 
	[*] RCON is a protocol to remote connect with the server console using a password.
	[?] Do you want to enable RCON? (y/N):
	[*] Getting your external IP and internal IP
	[!] Your external IP is *.*.*.*. You may have to port-forward to your internal *.*.*.*
	[!] Be sure to check it, if you have to forward and you skip that, no external players will be able to join. [Press Enter][*] You have finished the set-up wizard correctly
	[*] Check the Plugin Repository to add new features, minigames, or advanced protection to your server
	[*] PocketMine-MP will now start. Type /help to view the list of available commands.

Everything is now configurated.  PocketMine will now start.

.. code-block:: text

	19:01:52 [INFO] Starting Minecraft: PE server version v0.9.1 alpha
	19:01:52 [INFO] Loading pocketmine.yml...
	19:01:52 [INFO] Loading server properties...
	19:01:52 [INFO] Starting Minecraft PE server on *:19132
	19:01:52 [INFO] This server is running PocketMine-MP version Alpha_1.4dev "絶好(Zekkou)ケーキ(Cake)" (API 1.1.0)
	19:01:52 [INFO] PocketMine-MP is distributed under the LGPL License
	19:01:52 [NOTICE] Level "world" not found
	19:01:52 [INFO] Preparing level "world"
	19:01:52 [NOTICE] Spawn terrain for level "world" is being generated in the background
	19:01:52 [INFO] Starting GS4 status listener
	19:01:52 [INFO] Setting query port to 19132
	19:01:52 [INFO] Query running on 0.0.0.0:19132
	19:01:52 [INFO] Default game type: SURVIVAL
	19:01:52 [INFO] Done (4941.533s)! For help, type "help" or "?"

When there are no errors and you see the same message then the server is started. Now you should be able to join the server!

Server properties
-----------------

.. contents::
	:local:


allow-flight
++++++++++++

=======  =========
Type     Default
=======  =========
boolean  false
=======  =========

Allows users to use flight on your server while in Survival mode, if they have a mod that provides flight installed. If enabled, they will be kicked after flying for 5 seconds. Disabling this will remove the player speed limit.

difficulty
++++++++++

=============  =========
Type             Default
=============  =========
integer (0-3)          1
=============  =========

Level of difficulty of the game, Survival/Adventure mode only. 

0. Peaceful
1. Easy
2. Normal
3. Hard

enable-query
++++++++++++

=======  =========
Type     Default
=======  =========
boolean  true
=======  =========

Enables the GameSpy4 UT3 Query Protocol server listener. Used to get information about the server. It'll listen on the same port as the server (using the same UDP interface).

enable-rcon
+++++++++++

=======  =========
Type     Default
=======  =========
boolean  false
=======  =========

RCON is a protocol to allow remote access to the server console. It'll listen y default on the same port as the server, but using TCP. You can also set the `rcon.port`, `rcon.threads` and `rcon.clients-per-thread` properties, but you'll have to manually add them.

rcon.password
+++++++++++++

======  ============
Type    Default
======  ============
string  random value
======  ============

The password that RCON will check. And empty string will cause all the requests to be refused.

gamemode
++++++++

=============  =========
Type             Default
=============  =========
integer (0-3)          0
=============  =========

Defines the mode of gameplay. 

0. Survival
1. Creative
2. Adventure
3. Spectator`

generator-settings
++++++++++++++++++

======  =========
Type    Default
======  =========
string  blank
======  =========

The settings used to customize Superflat world generation. See `Superflat on the MC Wiki <http://www.minecraftwiki.net/wiki/Superflat>`_ for possible settings and examples.

hardcore
++++++++

=======  =========
Type     Default
=======  =========
boolean  false
=======  =========

If enabled, players will be permanently banned if they die.

level-name
++++++++++

======  =========
Type    Default
======  =========
string  world
======  =========

Default world name. If it doesn't exist, the server will create a new one using the Default generator.

level-seed
++++++++++

======  =========
Type    Default
======  =========
string  blank
======  =========

A seed for your world.

level-type
++++++++++

======  =========
Type    Default
======  =========
string  DEFAULT
======  =========

Determines the type of map that is generated. `DEFAULT => Standard world, FLAT => A flat world`

max-players
+++++++++++

=======  =========
Type       Default
=======  =========
integer         20
=======  =========

The maximum number of players that can play on the server at the same time.

server-type
+++++++++++

======  =========
Type    Default
======  =========
string  normal
======  =========

Defines server type shown in server list. `normal, minecon`

server-name
+++++++++++

======  ====================
Type    Default
======  ====================
string  Minecraft: PE Server
======  ====================

Server name in the Client server list.

description
+++++++++++

======  ===============================
Type    Default
======  ===============================
string  Server made using PocketMine-MP
======  ===============================

Marquee shown in the Client server list.

motd
++++

======  ===============================
Type    Default
======  ===============================
string  Welcome @player to this server!
======  ===============================

Message that is sent to welcome a player.

pvp
+++

=======  =========
Type     Default
=======  =========
boolean  true
=======  =========

Enable PvP on the server, allowing players to damage each other directly.

server-port
+++++++++++

=================  =========
Type                 Default
=================  =========
integer (1-65534)      19132
=================  =========

Port that the server will listen on. Note that the client will only show servers on the range 19132-19135. To be accesible over the internet, this port must be `forwarded <http://en.wikipedia.org/wiki/Portforwarding>`_ if the server is hosted in a network using `NAT <http://en.wikipedia.org/wiki/Networkaddresstranslation>`_ (If you have a home router/firewall).

server-usage
++++++++++++

=======  =========
Type     Default
=======  =========
boolean  true
=======  =========

Sends anonymous usage data to PocketMine.net, including the release version, online users and OS (Win, Linux, Mac). These are shown `here <http://stats.pocketmine.net/>`_.

spawn-animals
+++++++++++++

=======  =========
Type     Default
=======  =========
boolean  true
=======  =========

Determines if animals will be able to spawn. Random spawns will be implemented in the future.

spawn-monsters
++++++++++++++

=======  =========
Type     Default
=======  =========
boolean  true
=======  =========

Determines if monsters will be able to spawn. Random spawns will be implemented in the future.

spawn-protection
++++++++++++++++

=======  =========
Type       Default
=======  =========
integer         16
=======  =========

Determines the radius of the spawn protection. Only OPs will be able to place/break blocks inside. Note: Setting this to 0 will not disable spawn protection. 0 will protect the single block at the spawn point. You can disable this using -1 as the value.

view-distance
+++++++++++++

=======  =========
Type       Default
=======  =========
integer         10
=======  =========

Sets the amount of world data the server sends the client, measured in chunks in each direction of the player.

white-list
++++++++++

=======  =========
Type     Default
=======  =========
boolean  false
=======  =========

Enables or disables whitelisting.

upnp-forwarding
+++++++++++++++

=======  =========
Type     Default
=======  =========
boolean  false
=======  =========

Only available on Windows. Tries UPnP automatic port forwarding.

memory-limit
++++++++++++

==========================  =========
Type                        Default
==========================  =========
integer (plus unit suffix)  128M
==========================  =========

Maximum memory that the server will allocate. The server won't work correctly with less than 128M

debug.level
+++++++++++

=======  =========
Type       Default
=======  =========
integer          1
=======  =========

Changes the log output. Max output level is 4
