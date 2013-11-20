<?php

namespace PeerJ\ArticleMetrics;

/**
 * Fetch counts of likes, shares and comments for an article, from Facebook
 *
 * Facebook resolves alternate URLs to the destination URL and combines counts
 */
class FacebookMetrics extends Metrics
{
    /** @{inheritdoc} */
    protected $name = 'facebook';

    /** @{inheritdoc} */
    public function fetch($article)
    {
        $file = $this->getDataFile($article);

        $params = array(
            'v' => '1.0',
            'method' => 'links.getStats',
            'format' => 'json',
            'urls' => sprintf('"%s"', $article['url']), // could join multiple URLs with a comma
        );

        $this->get('http://api.ak.facebook.com/restserver.php', $params, $file);
    }

    /** @{inheritdoc} */
    public function parse()
    {
        $output = $this->getOutputFile();
        fputcsv($output, array('id', 'likes', 'shares', 'comments'));

        foreach ($this->files() as $file) {
            $json = file_get_contents($file);
            $items = json_decode($json, true);

            foreach ($items as $item) {
                $data = array(
                    'id' => basename($file, '.' . $this->suffix),
                    'likes' => $item['like_count'],
                    'shares' => $item['share_count'],
                    'comments' => $item['comment_count'],
                );

                fputcsv($output, $data);
            }
        }

        fclose($output);
    }
}
