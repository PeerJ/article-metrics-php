<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/articles/original'));

clean_files(OUTPUT_DIR . '/*.json');

$client = new CurlClient;
$url = 'https://peerj.com/articles/index.json';
$i = 1;

do {
	print "$url\n";
	$json = $client->get($url);
	$feed = json_decode($json, true);

	$file = OUTPUT_DIR . sprintf('/%d.json', $i++);
	file_put_contents($file, json_encode($feed, JSON_PRETTY_PRINT));

	if (!isset($feed['_links']['next'])) {
		break;
	}

	$url = $feed['_links']['next']['href'];
} while ($url);

