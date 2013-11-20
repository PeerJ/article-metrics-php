<?php

namespace PeerJ\ArticleMetrics;

class FacebookMetrics extends Metrics
{
    protected $name = 'facebook';

    public function fetch($article)
    {
        $file = $this->getDataFile($article);

        $params = array(
            'v' => '1.0',
            'method' => 'links.getStats',
            'format' => 'json',
            'urls' => sprintf('"%s"', $article['url']),
        );

        $this->get('http://api.ak.facebook.com/restserver.php', $params, $file);
    }

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
