<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/scopus/original'));

clean_files(OUTPUT_DIR . '/*.json');

$client = new CurlClient;
curl_setopt($client->curl, CURLOPT_HTTPHEADER, array('X-ELS-APIKey: ' . $config['scopus']['api_key']));

foreach (articles() as $i => $article) {
	$file = OUTPUT_DIR . sprintf('/%d.json', $i);
	print "$file\n";

	$params = array(
	   'field' => 'citedby-count',
	   'query' => sprintf('DOI(%s)', $article['doi']),
	);

	$output = fopen($file, 'w');
	$client->get('http://api.elsevier.com/content/search/index:SCOPUS', $params, $output);
	fclose($output);
}
