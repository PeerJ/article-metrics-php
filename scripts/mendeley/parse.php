<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/mendeley/original'));
define('OUTPUT_DIR', datadir('/mendeley'));

$output = fopen(OUTPUT_DIR . '/mendeley.csv', 'w');
fputcsv($output, array('doi', 'readers'));

$files = glob(INPUT_DIR . '/*.json');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
	$json = file_get_contents($file);
	$item = json_decode($json, true);

	$data = array(
		$item['identifiers']['doi'],
		$item['stats']['readers'],
	);

	fputcsv($output, $data);
}

fclose($output);
