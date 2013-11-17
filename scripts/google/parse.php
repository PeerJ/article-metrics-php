<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/google/original'));
define('OUTPUT_DIR', datadir('/google'));

$output = fopen(OUTPUT_DIR . '/google.csv', 'w');
fputcsv($output, array('url', 'visitors', 'unique pageviews', 'pageviews'));

$files = glob(INPUT_DIR . '/*.json');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
	$json = file_get_contents($file);
	$item = json_decode($json, true);

	$url = preg_replace('/ga:pagePath==/', 'https://peerj.com', $item['query']['filters']);

	$data = array(
		$url,
		$item['totalsForAllResults']['ga:visitors'],
		$item['totalsForAllResults']['ga:uniquepageviews'],
		$item['totalsForAllResults']['ga:pageviews'],
	);

	fputcsv($output, $data);
}

fclose($output);
