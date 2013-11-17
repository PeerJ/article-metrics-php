<?php

class WikipediaMetrics extends Metrics
{
	protected $name = 'wikipedia';

	public function fetch($article)
	{
		$file = $this->getDataFile($article);

		$params = array(
			'action' => 'query',
			'list' => 'search',
			'srprop' => 'timestamp',
			'format' => 'json',
			'srsearch' => sprintf('"%s"', $article['doi']),
		);

		$this->get('https://en.wikipedia.org/w/api.php', $params, $file);

		// hack to add the article ID to the search response
		$data = json_decode(file_get_contents($file));
		$data->query->searchinfo->id = $article['id'];
		file_put_contents($file, json_encode($data));
	}

	public function parse()
	{
		$output = $this->getOutputFile();
		fputcsv($output, array('doi', 'mentions', 'pages'));

		foreach ($this->files() as $file) {
			$json = file_get_contents($file);
			$item = json_decode($json, true);

			$pages = array_map(function($page) {
				return $page['title'];
			}, $item['query']['search']);

			$info = $item['query']['searchinfo'];

			$data = array(
				$info['id'], // added when saving
				$info['totalhits'],
				implode(',', $pages),
			);

			fputcsv($output, $data);
		}

		fclose($output);
	}
}