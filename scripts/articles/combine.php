<?php

require __DIR__ . '/../include.php';

$items = array();

foreach (articles() as $article) {
	$id = id_from_doi($article['doi']);

	$items[$id] = array(
		'id' => $id,
		'date' => $article['date'],
		'doi' => $article['doi'],
		'url' => $article['url'],
		'crossref-cited' => null,
		'scopus-cited' => null,
		'tweets' => null,
		'facebook-likes' => null,
		'facebook-shares' => null,
		'facebook-comments' => null,
		'google-unique-views' => null,
		'mendeley-readers' => null,
		'pmc-fulltext-views' => null,
		'dryad-cited' => null,
		'wikipedia-mentions' => null,
		'title' => $article['title'],
		'scopus' => null,
		'twitter' => sprintf('https://twitter.com/search?q="%s"', preg_replace('#^https?://#', '', $article['url'])),
		'wikipedia' => sprintf('https://en.wikipedia.org/w/api.php?action=query&list=search&srprop=timestamp&format=jsonfm&srsearch="%s"', $article['doi']),
		'altmetric' => 'http://www.altmetric.com/details.php?doi=' . $article['doi'],
		'impactstory' => 'http://impactstory.org/item/doi/' . $article['doi'],
	);
}

$input = fopen(datadir('/crossref') . '/crossref.csv', 'r');
$fields = fgetcsv($input);
while (($line = fgetcsv($input)) !== false) {
	$data = array_combine($fields, $line);
	$id = id_from_doi($data['doi']);
	$items[$id]['crossref-cited'] = $data['count'];
}
fclose($input);

$input = fopen(datadir('/scopus') . '/scopus.csv', 'r');
$fields = fgetcsv($input);
while (($line = fgetcsv($input)) !== false) {
	$data = array_combine($fields, $line);
	$id = id_from_doi($data['doi']);
	$items[$id]['scopus-cited'] = $data['count'];
	$items[$id]['scopus'] = $data['link'];
}
fclose($input);

$input = fopen(datadir('/twitter') . '/twitter.csv', 'r');
$fields = fgetcsv($input);
while (($line = fgetcsv($input)) !== false) {
	$data = array_combine($fields, $line);
	$id = id_from_url($data['url']);
	$items[$id]['tweets'] = $data['count'];
}
fclose($input);

$input = fopen(datadir('/facebook') . '/facebook.csv', 'r');
$fields = fgetcsv($input);
while (($line = fgetcsv($input)) !== false) {
	$data = array_combine($fields, $line);
	$id = id_from_url($data['url']);
	$items[$id]['facebook-likes'] = $data['likes'];
	$items[$id]['facebook-shares'] = $data['shares'];
	$items[$id]['facebook-comments'] = $data['comments'];
}
fclose($input);

$input = fopen(datadir('/google') . '/google.csv', 'r');
$fields = fgetcsv($input);
while (($line = fgetcsv($input)) !== false) {
	$data = array_combine($fields, $line);
	$id = id_from_url($data['url']);
	$items[$id]['google-unique-views'] = $data['unique pageviews'];
}
fclose($input);

$input = fopen(datadir('/mendeley') . '/mendeley.csv', 'r');
$fields = fgetcsv($input);
while (($line = fgetcsv($input)) !== false) {
	$data = array_combine($fields, $line);
	$id = id_from_doi($data['doi']);
	$items[$id]['mendeley-readers'] = $data['readers'];
}
fclose($input);

$input = fopen(datadir('/pmc') . '/pmc.csv', 'r');
$fields = fgetcsv($input);
while (($line = fgetcsv($input)) !== false) {
	$data = array_combine($fields, $line);
	$id = id_from_doi($data['doi']);
	$items[$id]['pmc-fulltext-views'] = $data['count'];
}
fclose($input);

$input = fopen(datadir('/dryad') . '/dryad.csv', 'r');
$fields = fgetcsv($input);
while (($line = fgetcsv($input)) !== false) {
	$data = array_combine($fields, $line);
	$id = id_from_doi($data['doi']);
	$items[$id]['dryad-cited'] = $data['count'];
}
fclose($input);

$input = fopen(datadir('/wikipedia') . '/wikipedia.csv', 'r');
$fields = fgetcsv($input);
while (($line = fgetcsv($input)) !== false) {
	$data = array_combine($fields, $line);
	$id = id_from_doi($data['doi']);
	$items[$id]['wikipedia-mentions'] = $data['mentions'];
}
fclose($input);

$output = fopen(datadir('/articles') . '/combined.csv', 'w');

$fields = array_keys($items[1]);
fputcsv($output, $fields);
$field_count = count($fields);

foreach ($items as $id => $item) {
	if (count($item) !== $field_count) {
		exit("Not enough fields in item $id\n");
	}

	fputcsv($output, $item);
}
fclose($output);
