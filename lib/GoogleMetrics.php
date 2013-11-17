<?php

// https://cloud.google.com/console
// enable Analytics API
// https://developers.google.com/apis-explorer/#s/analytics/v3/analytics.data.ga.get
// authorise
// enter field values + execute
// copy bearer header
// TODO: proper authorisation

class GoogleMetrics extends Metrics
{
	protected $name = 'google';

	public function __construct()
	{
		parent::__construct();

		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $this->config['access_token'],
			'x-origin: https://developers.google.com' // TODO: proper authorisation
		));
	}

	public function fetch($article)
	{
		$file = $this->getDataFile($article);

		$parts = parse_url($article['url']);

		$params = array(
			'key' => $this->config['key'],
			'ids' => $this->config['id'],
			'start-date' => '2005-01-01',
			'end-date' => 'today',
			'metrics' => 'ga:visitors,ga:uniquepageviews,ga:pageviews',
			'filters' => sprintf('ga:hostname==%s;ga:pagePath==%s', $parts['host'], $parts['path']),
			'max-results' => 1,
		);

		$this->get('https://www.googleapis.com/analytics/v3/data/ga', $params, $file);
	}

	public function parse()
	{
		$output = $this->getOutputFile();
		fputcsv($output, array('url', 'visitors', 'unique pageviews', 'pageviews'));

		foreach ($this->files() as $file) {
			$json = file_get_contents($file);
			$item = json_decode($json, true);

			preg_match('/^ga:hostname==(.+);ga:pagePath==(.+)$/', $item['query']['filters'], $matches);

			$data = array(
				'url' => 'https://' . $matches[1] . $matches[2],
				'visitors' => $item['totalsForAllResults']['ga:visitors'],
				'unique pageviews' => $item['totalsForAllResults']['ga:uniquepageviews'],
				'pageviews' => $item['totalsForAllResults']['ga:pageviews'],
			);

			fputcsv($output, $data);
		}

		fclose($output);
	}
}