<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/scopus/original'));

clean_files(OUTPUT_DIR . '/*.json');

$client = new CurlClient;

foreach (articles() as $i => $article) {
	$file = OUTPUT_DIR . sprintf('/%d.json', $i);
	print "$file\n";

	$params = array(
		'apiKey' => $config['scopus']['api_key'],
		'search' => sprintf('DOI(%s)', $article['doi']),
	);

	$output = fopen($file, 'w');
	$client->get('http://searchapi.scopus.com/documentSearch.url', $params, $output);
	fclose($output);
}
