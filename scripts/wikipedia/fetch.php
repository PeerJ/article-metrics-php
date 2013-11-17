<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/wikipedia/original'));
clean_files(OUTPUT_DIR . '/*.json');

$client = new CurlClient;

foreach (articles() as $i => $article) {
	$file = OUTPUT_DIR . sprintf('/%d.json', $i);
	$output = fopen($file, 'w');

	$params = array(
		'action' => 'query',
		'list' => 'search',
		'srprop' => 'timestamp',
		'format' => 'json',
		'srsearch' => sprintf('"%s"', $article['doi']),
	);

	$client->get('https://en.wikipedia.org/w/api.php', $params, $output);
	fclose($output);

	// hack to get the DOI in the search response
	$data = json_decode(file_get_contents($file));
	$data->query->searchinfo->doi = $article['doi'];
	file_put_contents($file, json_encode($data));
}

