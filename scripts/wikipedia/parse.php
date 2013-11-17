<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/wikipedia/original'));
define('OUTPUT_DIR', datadir('/wikipedia'));

$output = fopen(OUTPUT_DIR . '/wikipedia.csv', 'w');
fputcsv($output, array('doi', 'mentions', 'pages'));

$files = glob(INPUT_DIR . '/*.json');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
	$json = file_get_contents($file);
	$item = json_decode($json, true);

	$pages = array_map(function($page) {
		return $page['title'];
	}, $item['query']['search']);

	$info = $item['query']['searchinfo'];

	$data = array(
		$info['doi'],
		$info['totalhits'],
		implode(',', $pages),
	);

	fputcsv($output, $data);
}

fclose($output);
