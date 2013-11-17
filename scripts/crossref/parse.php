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
	$xpath->registerNamespace('q', 'http://www.crossref.org/qrschema/2.0');

	$body = $xpath->query('q:query_result/q:body')->item(0);

	$data = array(
		'doi' => $xpath->evaluate('string(q:forward_link[1]/@doi)', $body),
		'count' => $xpath->evaluate('count(q:forward_link/q:journal_cite)', $body),
	);

	fputcsv($output, $data);
}

fclose($output);
