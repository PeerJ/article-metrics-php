<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/twitter/original'));
define('OUTPUT_DIR', datadir('/twitter'));

$output = fopen(OUTPUT_DIR . '/twitter.csv', 'w');
fputcsv($output, array('url', 'count'));

$files = glob(INPUT_DIR . '/*.json');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
	$json = file_get_contents($file);
	$item = json_decode($json, true);

	$data = array(
		$item['url'],
		$item['count']
	);

	fputcsv($output, $data);
}

fclose($output);
