<?php

date_default_timezone_set('UTC');

if (file_exists(__DIR__ . '/config.json')) {
	$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);

	if ($error = json_last_error()) {
		exit("Error parsing config.json: $error\n");
	}
}

function __autoload($class) {
    include __DIR__ . '/../lib/' . $class . '.php';
}

function datadir($suffix) {
	$dir = __DIR__ . '/../data' . $suffix;

	if (!file_exists($dir)) {
		mkdir($dir, 0777, true);
	}

	return $dir;
}

function articles() {
	 $input = fopen(datadir('/articles') . '/articles.csv', 'r');

	 $fields = fgetcsv($input);

	 $items = array();

	 while (($line = fgetcsv($input)) !== false) {
	 	$items[] = array_combine($fields, $line);
	 }

	 return $items;
}

function clean_files($pattern) {
	foreach (glob($pattern) as $file) {
		unlink($file);
	}
}