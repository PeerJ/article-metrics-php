<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/crossref/original'));

clean_files(OUTPUT_DIR . '/*.xml');

$client = new CurlClient;

foreach (articles() as $i => $article) {
	$file = OUTPUT_DIR . sprintf('/%d.xml', $i);
	print "$file\n";

	$params = array(
		'usr' => $config['crossref']['user'],
		'pwd' => $config['crossref']['pass'],
		'doi' => $article['doi'],
	);

	$output = fopen($file, 'w');
	$client->get('http://doi.crossref.org/servlet/getForwardLinks', $params, $output);
	fclose($output);

	// TODO: rate limit/sleep?
}
