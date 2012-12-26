	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.


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
		 

PocketMine-MP
=============
Github repo: https://github.com/shoghicp/PocketMine-MP

Server (and client) Minecraft Pocket Edition library written in PHP.
Currently a work in progress, and used to document http://www.wiki.vg/Pocket_Minecraft_Protocol

Check the wiki! https://github.com/shoghicp/PocketMine-MP/wiki

**Project Status: `PRE-ALPHA`**

**Tested in: `v4.0.0, v5.0.0`**


Current features of the server:
-------------------------------
* Players can connect and move around the world (and see each other)
* Support for reading/sending chunks!
* Map generator!
* Map saving! Place & remove blocks
* Multiple worlds and importing!
* PvP, life regeneration and death cause!
* Extensible API!
* Online list broadcast
* Configurable day/night cycle
* Mob spawning!
* Health and position saving
* server.properties configuration file
* Whitelist and IP Ban files
* Survival & Creative
* Awesome features in server list!
* Automatic new version checking
* Implemented packet loss recovery algorithm
* + more!


How to contact me
-----------------
* Email - <shoghicp@gmail.com>
* Twitter - [@shoghicp](https://twitter.com/shoghicp)
* Via IRC - #mcdevs or #mcpedevs on *irc.freenode.net* (or just /msg me there)
* [MinecraftForums profile](http://www.minecraftforum.net/user/1476633-shoghicp/)


Third-party Libraries Used
--------------------------
* __[PHP NBT](https://github.com/TheFrozenFire/PHP-NBT-Decoder-Encoder/blob/master/nbt.class.php)__ by [TheFrozenFire](https://github.com/TheFrozenFire): Class for reading in NBT-format files
* __[Math_BigInteger](http://phpseclib.sourceforge.net/math/intro.html)__ by _[phpseclib](http://phpseclib.sourceforge.net/)_: Pure-PHP arbitrary precission integer arithmetic library
* __[Spyc](https://github.com/mustangostang/spyc/blob/master/Spyc.php)__ by _[Vlad Andersen](https://github.com/mustangostang)_: A simple YAML loader/dumper class for PHP