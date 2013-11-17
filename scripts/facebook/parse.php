<?php

require __DIR__ . '/../include.php';

define('INPUT_DIR', datadir('/facebook/original'));
define('OUTPUT_DIR', datadir('/facebook'));

$output = fopen(OUTPUT_DIR . '/facebook.csv', 'w');
fputcsv($output, array('url', 'likes', 'shares', 'comments'));

$files = glob(INPUT_DIR . '/*.json');
sort($files, SORT_NATURAL);

foreach ($files as $file) {
	$json = file_get_contents($file);
	$items = json_decode($json, true);

	foreach ($items as $item) {
		$data = array(
			$item['url'],
			$item['like_count'],
			$item['share_count'],
			$item['comment_count'],
		);

		fputcsv($output, $data);
	}
}

fclose($output);
