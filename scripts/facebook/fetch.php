<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/facebook/original'));
clean_files(OUTPUT_DIR . '/*.json');

$client = new CurlClient;

$urls = array_map(function($article) {
	return sprintf('"%s"', $article['url']);
}, articles());

foreach (array_chunk($urls, 50) as $i => $someURLs) {
	$file = OUTPUT_DIR . sprintf('/%d.json', $i);
	$output = fopen($file, 'w');

	/*
	$params = array(
		'query' => sprintf('SELECT like_count,share_count,click_count,comment_count FROM link_stat WHERE url IN (%s)',
			implode(',', $urls)),
		'format' => 'json',
	);

	$client->get('https://api.facebook.com/method/fql.query', $params, $output);
	*/

	$params = array(
		'v' => '1.0',
		'method' => 'links.getStats',
		'format' => 'json',
		'urls' => implode(',', $someURLs),
	);

	$client->get('http://api.ak.facebook.com/restserver.php', $params, $output);

	fclose($output);
}

