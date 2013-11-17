<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/scopus/original'));
define('OUTPUT_DIR', datadir('/scopus'));

$output = fopen(OUTPUT_DIR . '/scopus.csv', 'w');
fputcsv($output, array('url', 'count'));

$files = glob(INPUT_DIR . '/*.json');
sort($files, SORT_NATURAL);

$doc = new DOMDocument;

foreach ($files as $file) {
	$json = file_get_contents($file);
	$data = json_decode($json, true);

	if (!$data['search-results']) {
		exit("No results in file $file\n");
	}

	if ($data['search-results']['opensearch:totalResults'] != 1) {
		exit("Too many results in file $file\n");
	}

	$entry = $data['search-results']['entry'][0];

	$data = array(
		// TODO: doi?
        'scopus-url' => $entry['prism:url'],
        'scopus-citations' => (int) $entry['citedby-count'],
    );

	fputcsv($output, $data);
}

fclose($output);
