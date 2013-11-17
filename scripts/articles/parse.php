<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/articles/original'));
define('OUTPUT_DIR', datadir('/articles'));

$output = fopen(OUTPUT_DIR . '/articles.csv', 'w');
fputcsv($output, array('doi', 'date', 'title', 'url'));

$files = glob(INPUT_DIR . '/*.json');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
	$json = file_get_contents($file);
	$feed = json_decode($json, true);

	foreach ($feed['_items'] as $item) {
		$data = array(
			'doi' => $item['doi'],
			'date' => $item['date'],
			'title' => $item['title'],
			'url' => $item['fulltext_html_url'] . '/', // TODO: add link[rel=canonical]
		);

		fputcsv($output, $data);
	}
}

fclose($output);
