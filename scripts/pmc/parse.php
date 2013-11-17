<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/pmc/original'));
define('OUTPUT_DIR', datadir('/pmc'));

$output = fopen(OUTPUT_DIR . '/pmc.csv', 'w');
fputcsv($output, array('doi', 'count'));

$files = glob(INPUT_DIR . '/*.xml');
sort($files, SORT_NATURAL);

$doc = new DOMDocument;
$items = array();

// sum counts for each month
foreach ($files as $file) {
	$doc->load($file);
	$xpath = new DOMXPath($doc);
	$nodes = $xpath->query('articles/article');

	foreach ($nodes as $node) {
		$doi = $xpath->evaluate('string(meta-data/@doi)', $node);
		$count = $xpath->evaluate('number(usage/@full-text)', $node);

		if (!isset($items[$doi])) {
			$items[$doi] = 0;
		}

		$items[$doi] += $count;
	}
}

// output total counts per article
foreach ($items as $doi => $count) {
	$data = array($doi, $count);
	fputcsv($output, $data);
}

fclose($output);
