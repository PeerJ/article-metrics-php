<?php

namespace PeerJ\ArticleMetrics;

/**
 * Fetch count of tweets about an article, from Twitter
 *
 * Twitter resolves alternate URLs to the destination URL and combines counts
 */
class TwitterMetrics extends Metrics
{
    /** @{inheritdoc} */
    protected $name = 'twitter';

    /** @{inheritdoc} */
    public function fetch($article)
    {
        $file = $this->getDataFile($article);

        $params = array(
            'url' => $article['url'],
        );

        $this->get('http://urls.api.twitter.com/1/urls/count.json', $params, $file);
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
                'count' => $item['count']
            );

            fputcsv($output, $data);
        }

        fclose($output);
    }
}
