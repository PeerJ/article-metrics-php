<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/dryad/original'));
clean_files(OUTPUT_DIR . '/*.json');

$client = new CurlClient;

foreach (articles() as $i => $article) {
	$file = OUTPUT_DIR . sprintf('/%d.json', $i);
	$output = fopen($file, 'w');

	$params = array(
		'wt' => 'json',
		'q' => 'dc.relation.isreferencedby:' . $article['doi'],
		'rows' => 0, // no metadata for each item
	);

	$client->get('http://datadryad.org/solr/search/select/', $params, $output);
	fclose($output);
}

