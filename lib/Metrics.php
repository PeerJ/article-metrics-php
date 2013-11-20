<?php

namespace PeerJ\ArticleMetrics;

abstract class Metrics {
	/** @var cURL */
	protected $curl;

	/** @var array */
	protected $headers;

	/** @var string */
	protected $name;

	/** @var string */
	protected $suffix = 'json';

	/** @var string */
	protected $dir;

	/** @var array */
	protected $config = array();

	abstract public function fetch($articles);

	abstract public function parse();

	/**
	 * read configuration and create a cURL instance
	 */
	public function __construct($config = array()) {
		$this->curl = curl_init();

		curl_setopt_array($this->curl, array(
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => false,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_VERBOSE => true,
			CURLOPT_HEADERFUNCTION => array($this, 'header'),
			CURLOPT_ENCODING => 'gzip,deflate',
		));

		if (isset($config[$this->name])) {
			$this->config = $config[$this->name];
		}
	}

	public function setDataDir($dir) {
		// TODO: verify $this->name is set?
		$this->dir = rtrim($dir, '/') . '/' . $this->name;

		$sourcedir = $this->dir . '/original';

		if (!file_exists($sourcedir)) {
			mkdir($sourcedir, 0777, true);
		}
	}

	protected function getDataFile($article)
	{
		return $this->dir . sprintf('/original/%d.%s', $article['id'], $this->suffix);
	}

	protected function getOutputFile()
	{
		return fopen($this->dir . '/' . $this->name . '.csv', 'w');
	}

	protected function id_from_doi($doi) {
		preg_match('/(\d+)$/', $doi, $matches);

		if (!$matches) {
			exit("No ID in DOI: $doi\n");
		}

		return $matches[1];
	}

	protected function id_from_url($url) {
		preg_match('/(\d+)\/?$/', $url, $matches);

		if (!$matches) {
			exit("No ID in URL: $url\n");
		}

		return $matches[1];
	}

	public function clean() {
		foreach (glob($this->dir . '/original/*.' . $this->suffix) as $file) {
			unlink($file);
		}
	}

	protected function files() {
		$files = glob($this->dir . '/original/*.' . $this->suffix);
		sort($files, SORT_NATURAL);

		return $files;
	}

	public function get($url, array $params, $file, $tries = 0)
	{
		if (!empty($params)) {
			$url .= '?' . http_build_query($params);
		}

		print "$url\n";

		$this->headers = array();

		$output = fopen($file, 'w');

		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_FILE, $output);

		$result = curl_exec($this->curl);
		$code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

		fclose($output);

		switch ($code) {
			case 200:
				return true;

			case 429: // rate limit
				if ($tries == 5) {
					throw new Exception('Rate limited too many times');
				}

				$this->delay();

				return $this->get($url, array(), $file, ++$tries);

			default:
				// TODO: unlink file?
				$message = sprintf('Response not OK: %d %s', $code, $result);

				throw new Exception($message);
		}
	}

	/**
	 * store response headers in an array
	 *
	 * @return int header length
	 */
	protected function header($curl, $header) {
		$parts = preg_split('/:\s+/', $header, 2);

		if (isset($parts[1])) {
			list($name, $value) = $parts;
			$this->headers[strtolower($name)] = $value;
		}

		return strlen($header);
	}

	/**
	 * delay if rate limit is reached
	 */
	protected function delay() {
		if (!isset($this->headers['x-rate-limit-reset'])) {
			exit('Rate limited, but no rate limit header found');
		}

		$delay = $this->headers['x-rate-limit-reset'] - time();

		if ($delay < 10) {
			$delay = 60 * 15; // 15 minute delay if the given delay seems unreasonably small (can be due to server time differences)
		}

		printf('Sleeping for %d seconds', $delay);
		sleep($delay);
	}
}