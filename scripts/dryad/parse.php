<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/dryad/original'));
define('OUTPUT_DIR', datadir('/dryad'));

$output = fopen(OUTPUT_DIR . '/dryad.csv', 'w');
fputcsv($output, array('doi', 'count'));

$files = glob(INPUT_DIR . '/*.json');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
	$json = file_get_contents($file);
	$item = json_decode($json, true);

	$doi = preg_replace('#^dc.relation.isreferencedby:#', '', $item['responseHeader']['params']['q']);

	$data = array(
		$doi,
		$item['response']['numFound'],
	);

	fputcsv($output, $data);
}

fclose($output);
