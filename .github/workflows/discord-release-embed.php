<?php

declare(strict_types=1);

namespace pocketmine;

use pocketmine\utils\Internet;
use function dirname;
use function fwrite;
use function is_array;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use const STDERR;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

/**
 * @phpstan-return array<string, mixed>
 */
function generateDiscordEmbed(string $version, string $channel, string $description, string $detailsUrl, string $sourceUrl, string $pharDownloadUrl, string $buildLogUrl) : array{
	return [
		"embeds" => [
			[
				"title" => "New PocketMine-MP release: $version ($channel)",
				"description" => <<<DESCRIPTION
$description

[Details]($detailsUrl) | [Source Code]($sourceUrl) | [Build Log]($buildLogUrl) | [Download]($pharDownloadUrl)
DESCRIPTION,
				"url" => $detailsUrl,
				"color" => $channel === "stable" ? 0x57ab5a : 0xc69026
			]
		]
	];
}

if(count($argv) !== 5){
	fwrite(STDERR, "Required arguments: github repo, version, API token\n");
	exit(1);
}
[, $repo, $tagName, $token, $hookURL] = $argv;

$result = Internet::getURL('https://api.github.com/repos/' . $repo . '/releases/tags/' . $tagName, extraHeaders: [
	'Authorization: token ' . $token
]);
if($result === null){
	fwrite(STDERR, "failed to access GitHub API\n");
	return;
}
if($result->getCode() !== 200){
	fwrite(STDERR, "Error accessing GitHub API: " . $result->getCode() . "\n");
	fwrite(STDERR, $result->getBody() . "\n");
	exit(1);
}

$releaseInfoJson = json_decode($result->getBody(), true, JSON_THROW_ON_ERROR);
if(!is_array($releaseInfoJson)){
	fwrite(STDERR, "Invalid release JSON returned from GitHub API\n");
	exit(1);
}
$buildInfoPath = 'https://github.com/' . $repo . '/releases/download/' . $tagName . '/build_info.json';

$buildInfoResult = Internet::getURL($buildInfoPath, extraHeaders: [
	'Authorization: token ' . $token
]);
if($buildInfoResult === null){
	fwrite(STDERR, "missing build_info.json\n");
	exit(1);
}
if($buildInfoResult->getCode() !== 200){
	fwrite(STDERR, "error accessing build_info.json: " . $buildInfoResult->getCode() . "\n");
	fwrite(STDERR, $buildInfoResult->getBody() . "\n");
	exit(1);
}

$buildInfoJson = json_decode($buildInfoResult->getBody(), true, JSON_THROW_ON_ERROR);
if(!is_array($buildInfoJson)){
	fwrite(STDERR, "invalid build_info.json\n");
	exit(1);
}
$detailsUrl = $buildInfoJson["details_url"];
$sourceUrl = $buildInfoJson["source_url"];
$pharDownloadUrl = $buildInfoJson["download_url"];
$buildLogUrl = $buildInfoJson["build_log_url"];

$description = $releaseInfoJson["body"];

$discordPayload = generateDiscordEmbed($buildInfoJson["base_version"], $buildInfoJson["channel"], $description, $detailsUrl, $sourceUrl, $pharDownloadUrl, $buildLogUrl);

$response = Internet::postURL(
	$hookURL,
	json_encode($discordPayload, JSON_THROW_ON_ERROR),
	extraHeaders: ['Content-Type: application/json']
);
if($response?->getCode() !== 204){
	fwrite(STDERR, "failed to send Discord webhook\n");
	fwrite(STDERR, $response?->getBody() ?? "no response body\n");
	exit(1);
}
