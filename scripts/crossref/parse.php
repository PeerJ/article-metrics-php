<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/crossref/original'));
define('OUTPUT_DIR', datadir('/crossref'));

$output = fopen(OUTPUT_DIR . '/crossref.csv', 'w');
fputcsv($output, array('doi', 'count'));

$files = glob(INPUT_DIR . '/*.xml');
sort($files, SORT_NATURAL);

$doc = new DOMDocument;

foreach ($files as $file) {
	$doc->load($file, LIBXML_NOENT | LIBXML_NONET);

	$xpath = new DOMXPath($doc);
	$xpath->registerNamespace('q', 'http://www.crossref.org/qrschema/3.0');

	$query = $xpath->query('q:query_result/q:body/q:query')->item(0);

	$data = array(
		$xpath->evaluate('string(q:doi)', $query),
		$xpath->evaluate('number(q:crm-item[@name="citedby-count"])', $query),
	);

	fputcsv($output, $data);
}

fclose($output);
