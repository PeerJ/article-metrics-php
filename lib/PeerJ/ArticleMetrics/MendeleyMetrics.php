<?php

namespace PeerJ\ArticleMetrics;

/**
 * Fetch counts of readers of an article, from Mendeley
 */
class MendeleyMetrics extends Metrics
{
    /** @{inheritdoc} */
    protected $name = 'mendeley';

    /** @{inheritdoc} */
    public function fetch($article)
    {
        $file = $this->getDataFile($article);

        try {
            $params = array(
                'type' => 'doi',
                'consumer_key' => $this->config['consumer_key'],
            );

            // bug in the API, so url-encode the DOI twice
            $url = sprintf(
                'http://api.mendeley.com/oapi/documents/details/%s/',
                rawurlencode(rawurlencode($article['doi']))
            );

            $this->get($url, $params, $file);
        } catch (\Exception $e) { // ignore 404 errors
            print $e->getMessage() . "\n";
            unlink($file);
        }
    }

    /** @{inheritdoc} */
    public function parse()
    {
        $output = $this->getOutputFile();
        fputcsv($output, array('id', 'readers'));

        foreach ($this->files() as $file) {
            $json = file_get_contents($file);
            $item = json_decode($json, true);

            $data = array(
                'id' => $this->idFromFile($file),
                'readers' => $item['stats']['readers'],
            );

            fputcsv($output, $data);
        }

        fclose($output);
    }
}
