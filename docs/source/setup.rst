.. _setup:

Installation
============

Installing on Windows
---------------------
Download the latest version from `sourceforge <http://sourceforge.net/projects/pocketmine/files/windows/dev/>`_

.. warning::
	If the provided x64 binary does not work then try the x86 binary!

Installing on Linux
-------------------
Open the terminal

Navigate where you want to install/update PocketMine-MP. You can move around using ``cd [directory]``, and create directories using ``mkdir [name]``

.. code-block:: sh

	~/ $ mkdir PocketMine-MP			# make new directory
	~/ $ cd PocketMine-MP				# change to directory
	PocketMine-MP/ $ 

Run the following code. It will download PocketMine-MP, download the PHP binaries or compile it if binaries are not available.

.. code-block:: sh

	PocketMine-MP/ $ curl -sL \
	https://raw.githubusercontent.com/PocketMine/php-build-scripts/master/installer.sh \
	 | bash -s - -v development

	[INFO] Found PocketMine-MP Alpha_1.4dev (build 289) using API 1.1.0
	[INFO] This development build was released on Thu Jul 17 21:31:35 CEST 2014
	[INFO] Installing/updating PocketMine-MP on directory ./
	[1/3] Cleaning...
	[2/3] Downloading PocketMine-MP Alpha_1.4dev-289 phar... done!
	[3/3] Obtaining PHP: detecting if build is available...
	[3/3] Linux 64-bit PHP build available, downloading PHP_5.5.14_x86-64_Linux.tar.gz... checking... regenerating php.ini... done
	[INFO] Everything done! Run ./start.sh to start PocketMine-MP

Installing on OS X
------------------
Open the Terminal.app. (Applications -> Utilities -> Terminal)

Navigate where you want to install/update PocketMine-MP. You can move around using ``cd [directory]``, and create directories using ``mkdir [name]``

.. code-block:: sh

	~/ $ mkdir PocketMine-MP			# make new directory
	~/ $ cd PocketMine-MP				# change to directory
	PocketMine-MP/ $ 

Run the following code. It will download PocketMine-MP, download the PHP binaries or compile it if binaries are not available.

.. code-block:: sh

	PocketMine-MP/ $ curl -sL \
	https://raw.githubusercontent.com/PocketMine/php-build-scripts/master/installer.sh \
	 | bash -s - -v development

	[INFO] Found PocketMine-MP Alpha_1.4dev (build 289) using API 1.1.0
	[INFO] This development build was released on Thu Jul 17 21:31:35 CEST 2014
	[INFO] Installing/updating PocketMine-MP on directory ./
	[1/3] Cleaning...
	[2/3] Downloading PocketMine-MP Alpha_1.4dev-289 phar... done!
	[3/3] Obtaining PHP: detecting if build is available...
	[3/3] MacOS 64-bit PHP build available, downloading PHP_5.5.14_x86-64_MacOS.tar.gz... checking... regenerating php.ini... done
	[INFO] Everything done! Run ./start.sh to start PocketMine-MP


Installing on Android
---------------------
Install `PocketMine-MP for Android <https://play.google.com/store/apps/details?id=net.pocketmine.server>`_
