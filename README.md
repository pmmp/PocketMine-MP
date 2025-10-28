<p align="center">
	<a href="https://github.com/ayrzDev/BeeltyMine">
		<picture>
			<source srcset=".github/readme/beetlymine-dark.gif" media="(prefers-color-scheme: dark)">
			<img src=".github/readme/beetlymine.gif" alt="BeeltyMine logo" loading="eager" />
		</picture>
	</a><br>
	<b>A highly customizable, open source server software for Minecraft: Bedrock Edition written in PHP</b>
</p>

<p align="center">
	<a href="https://github.com/ayrzDev/BeeltyMine/actions/workflows/main.yml"><img src="https://github.com/ayrzDev/BeeltyMine/actions/workflows/main.yml/badge.svg" alt="CI" /></a>
	<a href="https://github.com/ayrzDev/BeeltyMine/releases/latest"><img alt="GitHub release (latest SemVer)" src="https://img.shields.io/github/v/release/ayrzDev/BeeltyMine?label=release&sort=semver"></a>
	<a href="https://discord.gg/bmSAZBG"><img src="https://img.shields.io/discord/373199722573201408?label=discord&color=7289DA&logo=discord" alt="Discord" /></a>
	<br>
	<a href="https://github.com/ayrzDev/BeeltyMine/releases"><img alt="GitHub all releases" src="https://img.shields.io/github/downloads/ayrzDev/BeeltyMine/total?label=downloads%40total"></a>
	<a href="https://github.com/ayrzDev/BeeltyMine/releases/latest"><img alt="GitHub release (latest by SemVer)" src="https://img.shields.io/github/downloads/ayrzDev/BeeltyMine/latest/total?sort=semver"></a>
</p>

---

## 🚀 What is BeeltyMine?
**BeeltyMine** is a **fork of [PocketMine-MP](https://github.com/pmmp/PocketMine-MP)** — a high-performance, optimized version built for better stability, lower latency, and smoother gameplay.

This fork focuses on:
- ⚙️ **Improved optimizations** for high player counts  
- 🧠 **Reduced memory usage** and faster tick handling  
- 🔧 **Enhanced plugin compatibility**  
- 🚀 **Better performance under heavy load**

BeeltyMine maintains full compatibility with PocketMine-MP plugins and continues to receive updates alongside the upstream project.

---

## About PocketMine-MP
PocketMine-MP is a highly customisable server software for Minecraft: Bedrock Edition, built from scratch in PHP, with over 10 years of history.

If you're looking to create a Minecraft: Bedrock server with **custom functionality**, look no further.

- 🧩 **Powerful plugin API** - extend and customise gameplay as you see fit  
- 🗺️ **Rich ecosystem** and **large developer community** - find plugins easily and learn to develop your own  
- 🌐 **Multi-world support** - offer a more varied game experience to players without transferring them to other server nodes  
- 🏎️ **Performance** - get 100+ players onto one server (depending on hardware and plugins)  
- ⤴️ **Continuously updated** - new Minecraft versions are usually supported within days  

---

## ⚠️ Note
PocketMine-MP (and Tunaly) are **not** vanilla Minecraft server software.  
They do not include all features from the original game (e.g. redstone, mob AI, vanilla terrain generation, etc.).

For vanilla survival multiplayer, consider using the [official Minecraft: Bedrock server software](https://minecraft.net/download/server/bedrock).

---

## 📖 Getting Started
- [Documentation](http://pmmp.readthedocs.org/)  
- [Installation instructions](https://pmmp.readthedocs.io/en/rtfd/installation.html)  
- [Docker image](https://github.com/pmmp/PocketMine-MP/pkgs/container/pocketmine-mp)  
- [Plugin repository](https://poggit.pmmp.io/plugins)  

### Run locally (Windows)

If you're on Windows the included start scripts will run the server:

```powershell
# Run the bundled server (PowerShell)
.\start.ps1
# or
.\start.cmd
```

The server directory contains runtime data (worlds, players, logs). These files should not be pushed to GitHub — see `.gitignore` which already ignores `players/`, `worlds/`, `logs/` and other local artifacts.

---

## 🧑‍💻 Developing Plugins
If you want to write your own plugins, the following resources may be useful:

- [Developer documentation](https://devdoc.pmmp.io)  
- [API documentation (latest release)](https://apidoc.pmmp.io)  
- [DevTools](https://github.com/pmmp/DevTools/)  
- [ExamplePlugin](https://github.com/pmmp/ExamplePlugin/)  

---

## 💖 Support & Community
Join our Discord to chat with other developers and players:

[![Discord](https://img.shields.io/discord/373199722573201408?label=discord&color=7289DA&logo=discord)](https://discord.gg/bmSAZBG)

You can also ask questions on StackOverflow using the `pocketmine` tag.

---

## 🧩 Contributing
Tunaly welcomes community contributions and improvements!  
Check out:
- [Building from source](BUILDING.md)  
- [Contributing guidelines](CONTRIBUTING.md)  

---

## ⚖️ License
This project is licensed under **LGPL-3.0**.  
See the [LICENSE](/LICENSE) file for details.

**Note:** Tunaly/PocketMine are not affiliated with Mojang. All brands and trademarks belong to their respective owners.  
PocketMine-MP is not Mojang-approved software.
