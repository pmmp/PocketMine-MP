<?php

$sami = new Sami\Sami(dirname(__DIR__) . '/src/', [
	'title' => 'PocketMine-MP',
	'default_opened_level' => 1,
	'remote_repository' => new \Sami\RemoteRepository\GitHubRemoteRepository('pmmp/PocketMine-MP', __DIR__),
	'build_dir' => __DIR__ . '/docs',
	'cache_dir' => __DIR__ . '/docs-cache',
	'theme' => 'pocketmine'
]);

$templates = $sami['template_dirs'];
$templates[] = __DIR__ . '/theme';
$sami['template_dirs'] = $templates;

return $sami;
