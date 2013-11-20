<?php

namespace PeerJ\ArticleMetrics;

/**
 * Fetch counts of datasets mentioning an article, from Dryad
 */
class DryadMetrics extends Metrics
{
    /** @{inheritdoc} */
    protected $name = 'dryad';

    /** @{inheritdoc} */
    public function fetch($article)
    {
        $file = $this->getDataFile($article);

        $params = array(
            'wt' => 'json',
            'q' => 'dc.relation.isreferencedby:' . $article['doi'],
            'rows' => 0, // no metadata for each item
        );

        $this->get('http://datadryad.org/solr/search/select/', $params, $file);
    }

    /** @{inheritdoc} */
    public function parse()
    {
        $output = $this->getOutputFile();
        fputcsv($output, array('id', 'count'));

        foreach ($this->files() as $file) {
            $json = file_get_contents($file);
            $item = json_decode($json, true);

            $data = array(
                'id' => $this->idFromFile($file),
                'count' => $item['response']['numFound'],
            );

            fputcsv($output, $data);
        }

        fclose($output);
    }
}
