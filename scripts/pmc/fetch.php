<?php

require __DIR__ . '/../include.php';

define('OUTPUT_DIR', datadir('/pmc/original'));
clean_files(OUTPUT_DIR . '/*.xml');

$client = new CurlClient;

$date = new \DateTime('-1 MONTH'); // last month
$earliest = new \DateTime($config['pmcstats']['earliest']);

$i = 0;

do {
	$file = OUTPUT_DIR . sprintf('/%d.xml', $i++);
	$output = fopen($file, 'w');

	$params = array(
	    'user' => $config['pmcstats']['user'],
	    'password' => $config['pmcstats']['pass'],
	    'jrid' => $config['pmcstats']['jrid'],
	    'year' => $date->format('Y'),
	    'month' => $date->format('n'), // single-digit month
	);

	$client->get('http://www.pubmedcentral.nih.gov/utils/publisher/pmcstat/pmcstat.cgi', $params, $output);
	fclose($output);

	$date->modify('-1 MONTH');
} while ($date > $earliest);