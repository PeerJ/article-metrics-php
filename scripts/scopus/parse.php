<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/scopus/original'));
define('OUTPUT_DIR', datadir('/scopus'));

$output = fopen(OUTPUT_DIR . '/scopus.csv', 'w');
fputcsv($output, array('doi', 'link', 'count'));

$files = glob(INPUT_DIR . '/*.json');
sort($files, SORT_NATURAL);

$doc = new DOMDocument;

foreach ($files as $file) {
	$jsonp = file_get_contents($file);
	$json = preg_replace('/^null\(/', '', preg_replace('/\)$/', '', $jsonp));
	$data = json_decode($json, true);

	print_r($data);

	if (!$data['OK']) {
		print "Error in file $file\n";
		continue;
	}

	if ($data['OK']['returnedResults'] === 0) {
		print "No results in file $file\n";
		continue;
	}

	if ($data['OK']['returnedResults'] != 1) {
		print "Too many results in file $file\n";
		continue;
	}

	$item = $data['OK']['results'][0];

	$data = array(
		'doi' => $item['doi'],
        'link' => $item['inwardurl'],
        'cited' => (int) $item['citedbycount'],
    );

	fputcsv($output, $data);
}

fclose($output);
