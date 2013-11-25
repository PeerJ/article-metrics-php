<?php

namespace PeerJ\ArticleMetrics;

/**
 * Fetch cited-by counts for an article, from Scopus
 */
class ScopusMetrics extends Metrics
{
    /** @{inheritdoc} */
    protected $name = 'scopus';

    /** @{inheritdoc} */
    public function __construct(array $config)
    {
        parent::__construct($config);

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
            'X-ELS-APIKEY: ' . $this->config['api_key'],
            'X-ELS-INSTTOKEN: ' . $this->config['inst_token'],
        ));
    }

    /** @{inheritdoc} */
    public function fetch($article)
    {
        $file = $this->getDataFile($article);

        $params = array(
            'field' => 'citedby-count',
            'query' => sprintf('DOI(%s)', $article['doi']), // could join multiple queries with OR?
        );

        $this->get('https://api.elsevier.com/content/search/index:SCOPUS', $params, $file);
    }

    /** @{inheritdoc} */
    public function parse()
    {
        $output = $this->getOutputFile();
        fputcsv($output, array('id', 'link', 'count'));

        foreach ($this->files() as $file) {
            $json = file_get_contents($file);
            $data = json_decode($json, true);

            if (!$data['search-results']) {
                print "Error in file $file\n";
                continue;
            }

            if ($data['search-results']['opensearch:totalResults'] == 0) {
                print "No results in file $file\n";
                continue;
            }

            if ($data['search-results']['opensearch:totalResults'] != 1) {
                print "Too many results in file $file\n";
                continue;
            }

            $item = $data['search-results']['entry'][0];

            $data = array(
                'id' => $this->idFromFile($file),
                'link' => $item['prism:url'],
                'count' => $item['citedby-count'],
            );

            fputcsv($output, $data);
        }

        fclose($output);
    }
}
