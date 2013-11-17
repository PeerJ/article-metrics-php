<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/twitter/original'));

clean_files(OUTPUT_DIR . '/*.json');

$client = new CurlClient;

foreach (articles() as $i => $article) {
	$file = OUTPUT_DIR . sprintf('/%d.json', $i);
	print "$file\n";

	$params = array(
		'url' => $article['url'],
	);

	$output = fopen($file, 'w');
	$client->get('http://urls.api.twitter.com/1/urls/count.json', $params, $output);
	fclose($output);

	// TODO: rate limit/sleep?
}
