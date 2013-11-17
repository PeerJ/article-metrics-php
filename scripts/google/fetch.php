<?php

// https://cloud.google.com/console
// enable Analytics API
// https://developers.google.com/apis-explorer/#s/analytics/v3/analytics.data.ga.get
// authorise
// enter field values + execute
// copy bearer header
// TODO: proper authorisation

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/google/original'));
clean_files(OUTPUT_DIR . '/*.json');

$client = new CurlClient;
curl_setopt($client->curl, CURLOPT_HTTPHEADER, array(
	'Authorization: Bearer ' . $config['google-analytics']['access_token'],
	'x-origin: https://developers.google.com' // TODO: proper authorisation
));

$paths = array_map(function($article) {
	return preg_replace('#^https://peerj.com#', '', $article['url']);
}, articles());

foreach ($paths as $i => $path) {
	$file = OUTPUT_DIR . sprintf('/%d.json', $i);
	$output = fopen($file, 'w');

	$params = array(
		'key' => $config['google-analytics']['key'],
		'ids' => $config['google-analytics']['id'],
		'start-date' => '2005-01-01',
		'end-date' => 'today',
		'metrics' => 'ga:visitors,ga:uniquepageviews,ga:pageviews',
		'filters' => 'ga:pagePath==' . $path,
		'max-results' => 1,
	);

	$client->get('https://www.googleapis.com/analytics/v3/data/ga', $params, $output);
	fclose($output);
}

