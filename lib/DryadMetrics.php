<?php

class DryadMetrics extends Metrics
{
	protected $name = 'dryad';

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

	public function parse()
	{
		$output = $this->getOutputFile();
		fputcsv($output, array('id', 'count'));

		foreach ($this->files() as $file) {
			$json = file_get_contents($file);
			$item = json_decode($json, true);

			$doi = preg_replace('#^dc.relation.isreferencedby:#', '', $item['responseHeader']['params']['q']);

			$data = array(
				'id' => basename($file, '.' . $this->suffix),
				'count' => $item['response']['numFound'],
			);

			fputcsv($output, $data);
		}

		fclose($output);
	}
}