<?php

class CrossRefMetrics extends Metrics
{
	protected $name = 'crossref';

	protected $suffix = 'xml';

	public function fetch($article)
	{
		$file = $this->getDataFile($article);

		$params = array(
			'usr' => $this->config['user'],
			'pwd' => $this->config['pass'],
			'doi' => $article['doi'],
		);

		$this->get('http://doi.crossref.org/servlet/getForwardLinks', $params, $file);
	}

	public function parse()
	{
		$output = $this->getOutputFile();
		fputcsv($output, array('id', 'count'));

		foreach ($this->files() as $file) {
			$doc = new DOMDocument;
			$doc->load($file, LIBXML_NOENT | LIBXML_NONET);

			$xpath = new DOMXPath($doc);
			$xpath->registerNamespace('q', 'http://www.crossref.org/qrschema/2.0');

			$body = $xpath->query('q:query_result/q:body')->item(0);

			$data = array(
				'id' => basename($file, '.' . $this->suffix),
				'count' => $xpath->evaluate('count(q:forward_link/q:journal_cite)', $body),
			);

			fputcsv($output, $data);
		}

		fclose($output);
	}
}