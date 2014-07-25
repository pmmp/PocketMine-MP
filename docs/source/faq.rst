.. _faq:

FAQ - Frequently Asked Questions
================================

.. contents::
	:local:
	:depth: 2

Installation
------------

How do I install PHP? / How do I install this Server?
+++++++++++++++++++++++++++++++++++++++++++++++++++++
Check the installation instructions on the :ref:`installation <setup>` page.

Failed loading opcache.so
+++++++++++++++++++++++++
This will fail when you did not use the installer. This can be fixed with a single command.

.. code-block:: sh

	sed "s/^zend_extension=.*opcache.so/zend_extension=$(find $(pwd) -name opcache.so | sed 's/\//\\\//g')/g" \
	bin/php5/bin/php.ini | tee bin/php5/bin/php.ini

Playing
-------

Can PC Minecraft clients connect to this server
+++++++++++++++++++++++++++++++++++++++++++++++
No

Plugins
-------

How do I install Plugins
++++++++++++++++++++++++
Download the ``.phar`` file and move it to the ``plugins`` folder

Can i use .php files
++++++++++++++++++++
Yes, but only when the `DevTools <http://forums.pocketmine.net/plugins/devtools.515/>`_ plugin is installed and the plugin/PocketMine API versions are both the same

Connecting
----------

How do I connect to the server?
+++++++++++++++++++++++++++++++
* Tap Play -> Edit -> External, then fill in the server details
* If it is in your local network, you will find it highlighted on the play menu, without needing to add it

Can other users connect to my server
++++++++++++++++++++++++++++++++++++
Users on the same network are able to join the server. If you want other people from outside your own network to be able to join then you need to port-forward

Do I have to open ports?
++++++++++++++++++++++++
If you have a firewall setup then you need to allow access to ``UDP port 19132``

.. note::

	Do you want to use **RCON** then ``TCP port 19132`` also needs access.

Do I have to configure port forwarding?
+++++++++++++++++++++++++++++++++++++++++++
This is only needed when you want people from outside your network to connect. Check `portforward.com <http://portforward.com/english/routers/port_forwarding/routerindex.htm>`_ or us `Google <http://www.google.com>`_ to find the instructions

.. note::

	* UDP port: 19132 for PocketMine and Query
	* TCP port: 19132 for RCON
