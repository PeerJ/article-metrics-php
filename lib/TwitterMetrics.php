<?php

namespace PeerJ\ArticleMetrics;

class TwitterMetrics extends Metrics
{
	protected $name = 'twitter';

	public function fetch($article)
	{
		$file = $this->getDataFile($article);

		$params = array(
			'url' => $article['url'],
		);

		$this->get('http://urls.api.twitter.com/1/urls/count.json', $params, $file);
	}

	public function parse()
	{
		$output = $this->getOutputFile();
		fputcsv($output, array('id', 'count'));

		foreach ($this->files() as $file) {
			$json = file_get_contents($file);
			$item = json_decode($json, true);

			$data = array(
				'id' => basename($file, '.' . $this->suffix),
				'count' => $item['count']
			);

			fputcsv($output, $data);
		}

		fclose($output);
	}
}