.. plugins:

Plugins
=======
**PocketMine is extendable!**

Plugins are available on the `PocketMine website <http://forums.pocketmine.net/plugins/>`_ or you can make your own plugin.

Below is an skeleton with the minimal needed directories, files and content.

Basic plugin structure
----------------------

.. contents::
	:local:
	:depth: 2

Directories
+++++++++++
Make sure your base structure looks like this

.. code-block:: sh

	Example
	├── plugin.yml
	└── src
	    └── Example
		└── Main.php

	2 directories, 2 files

plugin.yml
++++++++++
This file is required in a plugin. It contains the information used by PocketMine-MP to load this plugin. It's in YAML format (you will use this format for plugin configurations). It has four required fields: name, version, api and main. Each one of these fields (and all the optional ones) are described on the plugin.yml page. Be sure that it is named exactly plugin.yml.

======= ====================================================================================
field   data
======= ====================================================================================
name    The name for your plugin
main    The namespace and classname pointing to your main plugin class. It is case sensitive
version The version string of your plugin
api     Minimal PocketMine-MP API version required for your plugin
======= ====================================================================================

.. code-block:: yaml

	name: Example
	main: Example\Main
	version: 1.0.0
	api: [1.0.0]

Main.php
++++++++
Now, create the main class file, that will include the PluginBase Class that starts the plugin. You can name it whatever you want, but a common way to name it is like the plugin name or Main.

.. code-block:: php

	<?php

	namespace Example;

	use pocketmine\plugin\PluginBase;

	class Main extends PluginBase{

		public function onLoad(){
			$this->getLogger()->info("onLoad() has been called!");
		}

		public function onEnable(){
			$this->getLogger()->info("onEnable() has been called!");
		}

		public function onDisable(){
			$this->getLogger()->info("onDisable() has been called!");
		}
	}

