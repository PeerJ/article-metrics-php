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
    public function fetch($article)
    {
        $file = $this->getDataFile($article);

        $params = array(
            'apiKey' => $this->config['api_key'], // could set this in header instead
            'search' => sprintf('DOI(%s)', $article['doi']), // could join multiple queries with OR?
        );

        $this->get('http://searchapi.scopus.com/documentSearch.url', $params, $file);
    }

    /** @{inheritdoc} */
    public function parse()
    {
        $output = $this->getOutputFile();
        fputcsv($output, array('id', 'link', 'count'));

        foreach ($this->files() as $file) {
            $jsonp = file_get_contents($file);
            $json = preg_replace('/^null\(/', '', preg_replace('/\)$/', '', $jsonp)); // hack for JSONP response
            $data = json_decode($json, true);

            if (!$data['OK']) {
                print "Error in file $file\n";
                continue;
            }

            if ((int) $data['OK']['returnedResults'] === 0) {
                print "No results in file $file\n";
                continue;
            }

            if ((int) $data['OK']['returnedResults'] !== 1) {
                print "Too many results in file $file\n";
                continue;
            }

            $item = $data['OK']['results'][0];

            $data = array(
                'id' => $this->idFromFile($file),
                'link' => $item['inwardurl'],
                'count' => (int) $item['citedbycount'],
            );

            fputcsv($output, $data);
        }

        fclose($output);
    }
}
