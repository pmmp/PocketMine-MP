<?php

$iterator = \Symfony\Component\Finder\Finder::create()->files()->name("*.php")->exclude(["tests", "composer", "stubs"])->in([dirname(__DIR__) . '/src/', dirname(__DIR__) . '/vendor/']);
$sami = new Sami\Sami($iterator, [
	'title' => 'PocketMine-MP',
	'default_opened_level' => 1,
	'remote_repository' => new \Sami\RemoteRepository\GitHubRemoteRepository('pmmp/PocketMine-MP', __DIR__),
	'build_dir' => __DIR__ . '/docs',
	'cache_dir' => __DIR__ . '/docs-cache',
	'theme' => 'pocketmine',
	'include_parent_data' => false
]);

$templates = $sami['template_dirs'];
$templates[] = __DIR__ . '/theme';
$sami['template_dirs'] = $templates;

return $sami;
