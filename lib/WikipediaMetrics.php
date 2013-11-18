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
	}

	public function parse()
	{
		$output = $this->getOutputFile();
		fputcsv($output, array('id', 'mentions', 'pages'));

		foreach ($this->files() as $file) {
			$json = file_get_contents($file);
			$item = json_decode($json, true);

			$data = array(
				'id' => basename($file, '.' . $this->suffix),
				'mentions' => $item['query']['searchinfo']['totalhits'],
				'pages' => implode(',', array_map(function($page) {
					return $page['title'];
				}, $item['query']['search'])),
			);

			fputcsv($output, $data);
		}

		fclose($output);
	}
}