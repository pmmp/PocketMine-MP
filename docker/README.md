# PocketMine-MP Docker image
This folder contains the files used to build and test the `pmmp/pocketmine-mp` Docker image.

Docker is an easy, safe way to run software in a container where it can't affect anything else on your machine.
You don't need to build any dependencies, and updating is as simple as changing the version number of the image you're using.

## Pre-requisites
To install Docker, refer to the [official Docker docs](https://docs.docker.com/engine/install/).

## Running PocketMine-MP from Docker (using Docker Hub)
This is really easy once you have `docker` installed.

```
mkdir wherever-you-want
cd wherever-you-want
mkdir data plugins
sudo chown -R 1000:1000 data plugins
docker run -it -p 19132:19132/udp -v $PWD/data:/data -v $PWD/plugins:/plugins ghcr.io/pmmp/pocketmine-mp
```

To run a specific version, just add it to the end of the command, like this:
```
docker run -it -p 19132:19132/udp -v $PWD/data:/data -v $PWD/plugins:/plugins ghcr.io/pmmp/pocketmine-mp:4.0.0
```

## Changing the server port
Docker allows you to map ports, so you don't need to edit `server.properties`.

In the run command shown above, change `19132:19132/udp` to `<port number you want>:19132/udp`. **Note: Do not change the second number.**

> [!WARNING]
> Do not change the port in `server.properties`. This is unnecessary when using Docker and will make things more complicated.

## Editing the server data
The server data (e.g. worlds, `server.properties`, etc.) will be stored in the `data` folder you created above.

**Note: If you add new files (e.g. a world), don't forget to change the ownership of the file/folder to `1000:1000`**:
```
sudo chown -R 1000:1000 <file/folder you added>
```
This is needed to make the server able to access the file/folder.

## Adding plugins
Plugins can be added by putting them in `plugins` folder you created earlier.


**Note: If you add new files, don't forget to change the ownership of the file/folder to `1000:1000`:
```
sudo chown -R 1000:1000 <file/folder you added>
```
This is needed to make the server able to access the file/folder.

## Run the server in the background
To run the server in the background, simply change `-it` to `-itd` in the last command above.
This will run the server in the background even if you closed console. (No need to `screen`/`tmux` anymore!)

### Opening the console of the server
Use `docker ps` to see a list of running containers. It will look like this:
```
user@DYLANS-PC:~/pm-docker-test$ docker ps
CONTAINER ID   IMAGE                      COMMAND                  CREATED         STATUS         PORTS                                                                              NAMES
dc20edd3dd62   pmmp/pocketmine-mp:4.0.0   "start-pocketmine"       7 seconds ago   Up 6 seconds   19132/tcp, 0.0.0.0:19132-19133->19132-19133/udp, :::19132-19133->19132-19133/udp   brave_dijkstra
```
In this case, the container name is `brave_dijkstra`, but it might be something else in your case.

To open the console, run the following command:

```
docker attach <container name you saw in docker ps>
```

To leave the console, just press `Ctrl p` `Ctrl q`.

### Viewing the logs
To see the logs, run the following command:
```
docker logs --tail=100 <container name you saw in docker ps>
```
Change `--tail=100` to the number of recent lines in the log you want to see.

## Adding plugins from Poggit
If the `$POCKETMINE_PLUGINS` is set, the container will auto-download the plugins specified from https://poggit.pmmp.io
before starting PocketMine-MP.

The list of plugins should be given in the format `PluginOne:1.2.3 PluginTwo:4.5.6`. The version part (`:4.5.6`) is optional.

> [!CAUTION]
> Plugins won't be redownloaded if they're already in the `plugins` volume, even if the version is different.
> If you need to update a plugin, you'll need to delete the old plugin `.phar` first.

## Volumes
- `/data` is a read-write data directory where PocketMine stores all data in.
	This includes PocketMine config files, player data, worlds and plugin config files/data.
- `/plugins` is a read-only data directory where PocketMine loads plugins from.

## Advanced usage: Passing args to PocketMine-MP.phar inside the container
The `POCKETMINE_ARGS` environment variable will be passed to `PocketMine-MP.phar` when run.

## Building this image
The Dockerfile requires a build-arg `GIT_HASH` to fill the git hash metadata when building `PocketMine-MP.phar`.
This ensures that `/version`, crash reports, logs etc. report the correct server version.
