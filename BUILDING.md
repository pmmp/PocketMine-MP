# Building
## Pre-requisites
- A bash shell (git bash is sufficient for Windows)
- [`git`](https://git-scm.com) available in your shell
- PHP 7.3 or newer available in your shell
- [`composer`](https://getcomposer.org) available in your shell

## Custom PHP binaries
Because PocketMine-MP requires several non-standard PHP extensions and configuration, PMMP provides scripts to build custom binaries for running PocketMine-MP, as well as prebuilt binaries.

- [Prebuilt binaries](https://jenkins.pmmp.io/job/PHP-7.3-Aggregate)
- [Compile scripts](https://github.com/pmmp/php-build-scripts) are provided as a submodule in the path `build/php`

If you use a custom binary, you'll need to replace `composer` usages in this guide with `path/to/your/php path/to/your/composer.phar`.

## Setting up environment
1. `git clone --recursive https://github.com/pmmp/PocketMine-MP.git`
2. `composer install`

## Checking out a different branch to build
1. `git checkout <branch to checkout>`
2. `git submodule update --init`
3. Re-run `composer install` to synchronize dependencies.

## Optimizing for release builds
1. Add the flags `--no-dev --classmap-authoritative` to your `composer install` command. This will reduce build size and improve autoloading speed.
2. Preprocess the source code by running `build/preprocessor/PreProcessor.php`. Usage instructions are provided in `build/preprocessor/README.md`.

### Note
Preprocessor requires that the `cpp` (c preprocessor) is available in your PATH.

## Building `PocketMine-MP.phar`
Run `composer make-server` using your preferred PHP binary. It'll drop a `PocketMine-MP.phar` into the current working directory.

You can also use the `--out` option to change the output filename.

## Running PocketMine-MP from source code
Run `src/PocketMine.php` using your preferred PHP binary.
