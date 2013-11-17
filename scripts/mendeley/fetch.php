<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/mendeley/original'));
clean_files(OUTPUT_DIR . '/*.json');

$client = new CurlClient;

foreach (articles() as $i => $article) {
	$file = OUTPUT_DIR . sprintf('/%d.json', $i);
	$output = fopen($file, 'w');

	$params = array(
		'type' => 'doi',
		'consumer_key' => $config['mendeley']['consumer_key'],
	);

	$doi = rawurlencode(rawurlencode($article['doi'])); // bug in the API, so url-encode twice
	$base = sprintf('http://api.mendeley.com/oapi/documents/details/%s/', $doi);

	try {
		$client->get($base, $params, $output);
	} catch (Exception $e) { // ignore 404 errors
		print $e->getMessage() . "\n";
		fclose($output);
		unlink($file);
		continue;
	}

	fclose($output);
}

