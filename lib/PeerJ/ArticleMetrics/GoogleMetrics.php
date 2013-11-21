<?php

namespace PeerJ\ArticleMetrics;

// https://cloud.google.com/console
// enable Analytics API
// https://developers.google.com/apis-explorer/#s/analytics/v3/analytics.data.ga.get
// authorise
// enter field values + execute
// copy bearer header
// TODO: proper authorisation

/**
 * Fetch counts of visitors, unique views and total pageviews for an article, from Google
 */
class GoogleMetrics extends Metrics
{
    /** @{inheritdoc} */
    protected $name = 'google';

    /** @{inheritdoc} */
    public function __construct(array $config)
    {
        parent::__construct($config);

		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->config['access_token'],
            'x-origin: https://developers.google.com' // TODO: proper authorisation
		));
    }

    /** @{inheritdoc} */
    public function fetch($article)
    {
        $file = $this->getDataFile($article);

        $parts = parse_url($article['url']);

        $params = array(
            'key' => $this->config['key'],
            'ids' => $this->config['id'],
            'start-date' => $this->config['earliest'], // e.g. 2005-01-01
            'end-date' => 'today',
            'metrics' => 'ga:visitors,ga:uniquepageviews,ga:pageviews',
            'filters' => sprintf('ga:hostname==%s;ga:pagePath==%s', $parts['host'], $parts['path']),
            'max-results' => 1,
        );

        $this->get('https://www.googleapis.com/analytics/v3/data/ga', $params, $file);
    }

    /** @{inheritdoc} */
    public function parse()
    {
        $output = $this->getOutputFile();
        fputcsv($output, array('id', 'visitors', 'unique pageviews', 'pageviews'));

        foreach ($this->files() as $file) {
            $json = file_get_contents($file);
            $item = json_decode($json, true);

            $data = array(
                'id' => $this->idFromFile($file),
                'visitors' => $item['totalsForAllResults']['ga:visitors'],
                'unique pageviews' => $item['totalsForAllResults']['ga:uniquepageviews'],
                'pageviews' => $item['totalsForAllResults']['ga:pageviews'],
            );

            fputcsv($output, $data);
        }

        fclose($output);
    }
}
